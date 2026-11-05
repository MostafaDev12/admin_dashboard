<?php

namespace Tests\Feature;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsTrashEntry;
use App\Services\CmsMenu;
use App\Services\CmsPlacements;
use App\Services\CmsUpgrade;
use App\Services\WebsiteImporter;

class ContentRelationshipTest extends CmsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->asOwner();
    }

    private function createService(array $extra = []): CmsPage
    {
        $this->post(route('cms.pages.store'), $extra + [
            'template_id' => $this->page('service-exam')->id,
            'title' => 'Test service',
            'slug' => 'test-service',
            'summary' => 'Test service summary',
            'add_to_menu' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        return CmsPage::where('slug', $extra['slug'] ?? 'test-service')->firstOrFail();
    }

    private function createArticle(array $extra = []): CmsPage
    {
        $this->post(route('cms.pages.store'), $extra + [
            'template_id' => $this->page('article-1')->id,
            'title' => 'Test article',
            'slug' => 'test-article',
            'summary' => 'Test article summary',
            'category' => 'Test category',
        ])->assertSessionHasNoErrors()->assertRedirect();

        return CmsPage::where('slug', $extra['slug'] ?? 'test-article')->firstOrFail();
    }

    private function savePage(CmsPage $page, array $changes): void
    {
        $this->put(route('cms.pages.update', $page), $changes + [
            'title' => $page->title,
            'slug' => $page->slug,
            'description' => $page->description,
            'is_visible' => (int) $page->is_visible,
        ])->assertSessionHasNoErrors();
    }

    private function allPublicHtml(): string
    {
        return CmsPage::where('is_visible', true)->get()->map(fn ($page) => $this->publicHtml('/'.$page->slug))->implode("\n");
    }

    public function test_upgrade_links_existing_cards_without_changing_what_they_display(): void
    {
        $seed = json_decode(file_get_contents(database_path('content/website.json')), true);
        foreach ($seed['sections'] as $definition) {
            if (! in_array($definition['key'], ['services-s2', 'articles-s2', 'index-s3'], true)) {
                continue;
            }
            foreach ($definition['items'] as $source) {
                $item = CmsItem::where('import_key', $definition['key'].':'.$source['import_id'])->firstOrFail();
                $this->assertSame($source['fields'], $item->content);
            }
        }
        foreach (CmsPage::where('type', 'service')->get() as $page) {
            $cards = $this->section('services-s2')->items()->where('group_key', 'g-1')->where('target_page_id', $page->id)->get();
            $this->assertCount(1, $cards, $page->key);
            $this->assertEmpty($cards[0]->overrides);
            $this->assertSame(trim($cards[0]->content['f5']), $page->listing['title']);
            $this->assertTrue($page->auto_listing);
        }
        foreach (CmsPage::where('type', 'article')->get() as $page) {
            $cards = $this->section('articles-s2')->items()->where('group_key', 'g-2')->where('target_page_id', $page->id)->get();
            $this->assertCount(1, $cards, $page->key);
            $this->assertSame(trim($cards[0]->content['f7']), $page->listing['category']);
        }
        // The home card intentionally words this service differently: it stays customised.
        $home = $this->section('index-s3')->items()->where('target_page_id', $this->page('service-vision-correction')->id)->firstOrFail();
        $this->assertSame('تصحيح الإبصار', trim($home->content['f4']));
        $this->assertContains('title', $home->overrides);
    }

    public function test_repeating_the_upgrade_changes_nothing_and_creates_no_duplicates(): void
    {
        $snapshot = fn () => [CmsPage::count(), CmsSection::count(), CmsItem::count(), CmsTrashEntry::count(), CmsItem::orderBy('id')->get(['id', 'content', 'target_page_id', 'overrides', 'position', 'is_visible'])->toArray(), CmsPage::orderBy('id')->get(['id', 'listing', 'auto_listing'])->toArray()];
        $before = $snapshot();
        app(CmsUpgrade::class)->run();
        app(CmsUpgrade::class)->run();
        app(WebsiteImporter::class)->import();
        $this->assertSame($before, $snapshot());
    }

    public function test_new_service_lifecycle_from_draft_to_published_to_hidden(): void
    {
        $page = $this->createService();
        $card = $this->cardFor($page, 'services-s2');
        $this->assertNotNull($card);
        $this->assertTrue($card->is_managed);
        $this->assertSame('/test-service', $card->content['f7']);
        $this->assertSame('Test service', $card->content['f5']);
        $this->assertSame('Test service summary', $card->content['f6']);
        $this->assertSame('service', $page->type);
        $this->assertFalse($page->is_visible);

        // A draft is invisible everywhere.
        $this->get('/test-service')->assertNotFound();
        $this->assertStringNotContainsString('Test service', $this->publicHtml('/services.html'));
        $this->assertStringNotContainsString('/test-service', $this->publicHtml('/'));

        $this->savePage($page, ['is_visible' => 1]);
        $detail = $this->publicHtml('/test-service');
        $this->assertStringContainsString('Test service', $detail);
        $html = $this->publicHtml('/services.html');
        $xpath = $this->xpath($html);
        $this->assertSame(1, $xpath->query('//article[contains(@class,"service-card")]//a[@href="/test-service"]')->length);
        $this->assertSame(1, $xpath->query('//div[contains(@class,"nav-dropdown__menu")]//a[@href="/test-service"]')->length);
        $this->assertSame(1, $xpath->query('//div[contains(@class,"mobile-services__menu")]//a[@href="/test-service"]')->length);
        $this->assertNoDuplicateIds($html, 'services page with a new card');

        // Title and content remain manageable from the page.
        $this->savePage($page->fresh(), ['listing' => ['title' => 'Renamed service', 'description' => 'Renamed summary', 'image' => '/website/assets/images/servies/service-eye2.jpg']]);
        $html = $this->publicHtml('/services.html');
        $this->assertStringContainsString('Renamed service', $html);
        $this->assertStringContainsString('Renamed summary', $html);
        $this->assertSame('/website/assets/images/servies/service-eye2.jpg', $card->fresh()->content['f3']);

        // Unpublishing removes every route to the page while keeping all of its content.
        $content = $card->fresh()->content;
        $this->savePage($page->fresh(), ['is_visible' => 0]);
        $this->get('/test-service')->assertNotFound();
        $this->assertStringNotContainsString('/test-service', $this->allPublicHtml());
        $this->assertSame($content, $card->fresh()->content);
        $this->assertTrue($card->fresh()->is_visible);

        $this->savePage($page->fresh(), ['is_visible' => 1]);
        $this->assertStringContainsString('/test-service', $this->publicHtml('/services.html'));
    }

    public function test_new_page_seeds_its_heading_and_image_and_does_not_reuse_another_pages_card_copy(): void
    {
        $image = '/website/assets/images/Blog/blog-3.jpg';
        $page = $this->createService(['image' => $image]);
        $this->savePage($page, ['is_visible' => 1]);
        $heading = $page->sections()->where('template', 'like', '%-s1')->firstOrFail();
        $this->assertSame('Test service', $heading->content['f2']);
        $this->assertSame('Test service summary', $heading->content['f3']);
        $intro = $page->sections()->where('template', 'like', '%-s2')->firstOrFail();
        $this->assertSame($image, $intro->content['f1']);
        $this->assertSame($image, $this->cardFor($page, 'services-s2')->content['f3']);

        $this->post(route('cms.pages.store'), ['template_id' => $this->page('service-exam')->id, 'title' => 'No summary', 'slug' => 'no-summary'])->assertSessionHasNoErrors();
        $blank = $this->cardFor(CmsPage::where('slug', 'no-summary')->firstOrFail(), 'services-s2');
        $this->assertSame('', $blank->content['f6']);
    }

    public function test_customised_placement_copy_survives_page_updates_and_can_follow_again(): void
    {
        $page = $this->createService();
        $card = $this->cardFor($page, 'services-s2');
        $content = $card->content;
        $content['f5'] = 'Hand written card title';
        $this->put(route('cms.items.update', $card), $this->payload($card, ['content' => $content]))->assertSessionHasNoErrors();
        $this->assertSame(['title'], $card->fresh()->overrides);

        $this->savePage($page, ['listing' => ['title' => 'New page title', 'description' => 'New page summary']]);
        $card->refresh();
        $this->assertSame('Hand written card title', $card->content['f5']);
        $this->assertSame('New page summary', $card->content['f6']);

        $this->post(route('cms.placements.reset', $card))->assertRedirect();
        $this->assertSame('New page title', $card->fresh()->content['f5']);
        $this->assertEmpty($card->fresh()->overrides);
    }

    public function test_changing_a_url_updates_every_linked_placement_and_keeps_fragments(): void
    {
        $page = $this->page('service-cataract');
        $placements = CmsItem::where('target_page_id', $page->id)->pluck('id');
        $this->assertGreaterThanOrEqual(2, $placements->count());

        $contact = $this->page('contact');
        $linked = $this->section('index-s3')->items()->firstOrFail();
        $content = $linked->content;
        $content['f2'] = 'contact.html#location';
        $this->put(route('cms.items.update', $linked), $this->payload($linked, ['content' => $content]))->assertSessionHasNoErrors();
        $this->assertSame($contact->id, $linked->fresh()->target_page_id);

        $this->savePage($page, ['slug' => 'cataract-surgery']);
        $this->savePage($contact, ['slug' => 'contact-us']);
        foreach (CmsItem::whereIn('id', $placements)->with('section')->get() as $item) {
            $field = app(CmsPlacements::class)->roles($item->section, $item)['link'];
            $this->assertSame('/cataract-surgery', $item->content[$field], $item->label);
        }
        $this->assertSame('/contact-us#location', $linked->fresh()->content['f2']);
        $this->get('/service-details.html?service=cataract')->assertRedirect('/cataract-surgery');
        $this->get('/cataract-surgery')->assertOk();
    }

    public function test_new_article_lifecycle_with_category_ordering_and_hiding(): void
    {
        $page = $this->createArticle();
        $card = $this->cardFor($page, 'articles-s2');
        $this->assertSame('/test-article', $card->content['f10']);
        $this->assertSame('Test category', $card->content['f7']);
        $this->assertSame('Test category', $card->content['f2']);
        $this->assertStringNotContainsString('Test article', $this->publicHtml('/articles.html'));

        $this->savePage($page, ['is_visible' => 1]);
        $xpath = $this->xpath($this->publicHtml('/articles.html'));
        $cards = $xpath->query('//article[contains(@class,"article-card")]');
        $this->assertSame('/test-article', $xpath->query('.//a', $cards->item($cards->length - 1))->item(0)->getAttribute('href'));
        $this->assertSame('Test category', $cards->item($cards->length - 1)->getAttribute('data-category'));
        $this->assertSame(1, $xpath->query('//select[@data-article-filter]/option[@value="Test category"]')->length);

        // Reorder.
        $this->post(route('cms.items.move', $card), ['direction' => 'up'])->assertRedirect();
        $xpath = $this->xpath($this->publicHtml('/articles.html'));
        $cards = $xpath->query('//article[contains(@class,"article-card")]');
        $this->assertSame('/test-article', $xpath->query('.//a', $cards->item($cards->length - 2))->item(0)->getAttribute('href'));

        // Hide from the listing while the article stays published.
        $this->patch(route('cms.items.visibility', $card), ['is_visible' => 0])->assertRedirect();
        $this->assertStringNotContainsString('/test-article', $this->publicHtml('/articles.html'));
        $this->get('/test-article')->assertOk();
        $this->patch(route('cms.items.visibility', $card), ['is_visible' => 1])->assertRedirect();

        // A second article in the same category does not add a second filter option.
        $second = $this->createArticle(['title' => 'Second', 'slug' => 'second-article', 'summary' => 'Second summary']);
        $this->savePage($second, ['is_visible' => 1]);
        $xpath = $this->xpath($this->publicHtml('/articles.html'));
        $this->assertSame(1, $xpath->query('//select[@data-article-filter]/option[@value="Test category"]')->length);
    }

    public function test_renaming_a_filter_option_keeps_its_cards_matched(): void
    {
        $page = $this->createArticle();
        $this->savePage($page, ['is_visible' => 1]);
        $option = $this->section('articles-s2')->items()->where('group_key', 'g-1')->get()->first(fn ($item) => ($item->content['attributes_1']['value'] ?? null) === 'Test category');
        $content = $option->content;
        $content['f1'] = 'Renamed category';
        $this->put(route('cms.items.update', $option), $this->payload($option, ['content' => $content]))->assertSessionHasNoErrors();
        $this->assertSame('Renamed category', $option->fresh()->content['attributes_1']['value']);
        $card = $this->cardFor($page, 'articles-s2');
        $this->assertSame('Renamed category', $card->content['f2']);
        $this->assertSame('Renamed category', $card->content['f7']);
        $this->assertSame('Renamed category', $page->fresh()->listing['category']);
        $this->savePage($page->fresh(), ['listing' => $page->fresh()->listing]);
        $this->assertSame(1, $this->xpath($this->publicHtml('/articles.html'))->query('//select[@data-article-filter]/option[@value="Renamed category"]')->length);
    }

    public function test_unpublishing_hides_every_placement_including_carousel_sidebar_and_menu(): void
    {
        $article = $this->page('article-2');
        $before = CmsItem::where('target_page_id', $article->id)->orderBy('id')->get(['id', 'content', 'is_visible'])->toArray();
        $this->assertNotEmpty($before);
        $this->assertStringContainsString('article-details.html?article=2', $this->publicHtml('/articles.html'));

        $this->savePage($article, ['is_visible' => 0]);
        $this->get('/article-details.html?article=2')->assertNotFound();
        $this->assertStringNotContainsString('article-details.html?article=2', $this->allPublicHtml());
        $this->assertStringNotContainsString('article-details.html?article=2', str_replace('\\/', '/', $this->publicHtml('/')));
        $this->assertSame($before, CmsItem::where('target_page_id', $article->id)->orderBy('id')->get(['id', 'content', 'is_visible'])->toArray());

        $service = $this->page('service-retina');
        $this->savePage($service, ['is_visible' => 0]);
        $html = $this->publicHtml('/');
        $this->assertStringNotContainsString('service-details.html?service=retina', $html);
        $this->savePage($service->fresh(), ['is_visible' => 1]);
        $this->assertStringContainsString('service-details.html?service=retina', $this->publicHtml('/'));
    }

    public function test_removing_a_page_preserves_authored_placements_and_can_be_restored(): void
    {
        $page = $this->page('service-cataract');
        $slug = $page->slug;
        $counts = [CmsSection::count(), CmsItem::count()];
        $placements = CmsItem::where('target_page_id', $page->id)->whereNotIn('section_id', $page->sections->pluck('id'))->get();
        $this->assertNotEmpty($placements);
        $contents = $placements->pluck('content', 'id')->all();
        $sectionIds = $page->sections->pluck('id');

        $this->delete(route('cms.pages.destroy', $page))->assertRedirect();
        $this->assertDatabaseMissing('cms_pages', ['id' => $page->id]);
        foreach ($sectionIds as $id) {
            $this->assertDatabaseMissing('cms_sections', ['id' => $id]);
        }
        foreach ($placements as $placement) {
            $placement->refresh();
            $this->assertNull($placement->target_page_id);
            $this->assertFalse($placement->is_visible);
            $this->assertSame($page->title, $placement->detached_from);
            $this->assertSame($contents[$placement->id], $placement->content);
        }
        // No internal link is left broken, and the old URL still leads somewhere useful.
        $this->assertStringNotContainsString($slug, $this->allPublicHtml());
        // Temporary while the page can still be restored.
        $this->get('/'.$slug)->assertRedirect('/services.html')->assertStatus(302);

        // Intentional removal is respected by a later import.
        app(WebsiteImporter::class)->import();
        $this->assertDatabaseMissing('cms_pages', ['key' => 'service-cataract']);

        $this->post(route('cms.trash.restore', CmsTrashEntry::where('type', 'page')->firstOrFail()))->assertSessionHasNoErrors();
        $restored = $this->page('service-cataract');
        $this->assertSame($slug, $restored->slug);
        $this->assertSame($counts, [CmsSection::count(), CmsItem::count()]);
        $this->assertSame($sectionIds->all(), $restored->sections->pluck('id')->all());
        foreach ($placements as $placement) {
            $placement->refresh();
            $this->assertSame($restored->id, $placement->target_page_id);
            $this->assertTrue($placement->is_visible);
            $this->assertNull($placement->detached_from);
        }
        $this->get('/'.$slug)->assertOk();
        $this->assertStringContainsString($slug, $this->publicHtml('/services.html'));
        $this->assertDatabaseCount('cms_trash', CmsTrashEntry::count());
    }

    public function test_redirect_of_a_removed_page_becomes_permanent_once_purged(): void
    {
        $page = $this->page('article-5');
        $slug = $page->slug;
        $this->delete(route('cms.pages.destroy', $page))->assertRedirect();
        $this->get('/'.$slug)->assertStatus(302);
        $this->delete(route('cms.trash.destroy', CmsTrashEntry::where('type', 'page')->firstOrFail()))->assertRedirect();
        $this->get('/'.$slug)->assertRedirect('/articles.html')->assertStatus(301);
    }

    public function test_renames_stay_permanent_redirects(): void
    {
        $page = $this->page('about');
        $this->savePage($page, ['slug' => 'doctor']);
        $this->get('/about.html')->assertRedirect('/doctor')->assertStatus(301);
    }

    public function test_fixed_buttons_never_lead_to_an_unpublished_page(): void
    {
        $section = $this->section('articles-s3');
        $article = $this->page('article-2');
        $this->assertSame($article->id, $section->page_links['f6']);
        $this->assertSame('article-details.html?article=2', $section->content['f6']);
        $this->savePage($article, ['is_visible' => 0]);

        // Falls back to the listing, the stored destination is kept, and the button stays in place.
        $xpath = $this->xpath($this->publicHtml('/articles.html'));
        $this->assertSame(0, $xpath->query('//a[@href="article-details.html?article=2"]')->length);
        $this->assertGreaterThan(0, $xpath->query('//section[contains(@class,"section")]//a[@href="/articles.html"]')->length);
        $this->assertSame('article-details.html?article=2', $section->fresh()->content['f6']);

        // Shared CTA: a plain page has no listing, so the next live page is home.
        $contact = $this->page('contact');
        $this->savePage($contact, ['is_visible' => 0]);
        $this->assertSame('/', $this->xpath($this->publicHtml('/about.html'))->query('//a[contains(@class,"header-cta")]')->item(0)->getAttribute('href'));

        $this->savePage($article->fresh(), ['is_visible' => 1]);
        $this->savePage($contact->fresh(), ['is_visible' => 1]);
        $this->assertSame('contact.html#appointment', $this->xpath($this->publicHtml('/about.html'))->query('//a[contains(@class,"header-cta")]')->item(0)->getAttribute('href'));
    }

    public function test_fixed_buttons_follow_a_url_change_and_a_new_destination(): void
    {
        $contact = $this->page('contact');
        $this->savePage($contact, ['slug' => 'contact-us']);
        $this->assertSame('/contact-us#appointment', $this->section('global-header')->content['f11']);
        $this->assertSame($contact->id, $this->section('global-header')->page_links['f11']);

        $header = $this->section('global-header');
        $content = $header->content;
        $content['f11'] = 'https://book.example.test';
        $this->put(route('cms.sections.update', $header), $this->payload($header, ['content' => $content]))->assertSessionHasNoErrors();
        $this->assertArrayNotHasKey('f11', $this->section('global-header')->page_links);
        $this->savePage($contact->fresh(), ['slug' => 'contact-again']);
        $this->assertSame('https://book.example.test', $this->section('global-header')->content['f11']);
    }

    public function test_a_placement_hidden_before_removal_stays_hidden_after_restore(): void
    {
        $page = $this->page('service-lenses');
        $card = $this->cardFor($page, 'services-s2');
        $card->update(['is_visible' => false]);
        $this->delete(route('cms.pages.destroy', $page))->assertRedirect();
        $this->post(route('cms.trash.restore', CmsTrashEntry::where('type', 'page')->firstOrFail()))->assertSessionHasNoErrors();
        $this->assertFalse($card->fresh()->is_visible);
        $this->assertSame($this->page('service-lenses')->id, $card->fresh()->target_page_id);
    }

    public function test_restoring_a_page_whose_url_was_reused_fails_safely(): void
    {
        $page = $this->createService();
        $this->delete(route('cms.pages.destroy', $page))->assertRedirect();
        $entry = CmsTrashEntry::where('type', 'page')->firstOrFail();
        $replacement = $this->createService(['title' => 'Replacement', 'slug' => 'test-service']);
        $this->savePage($replacement, ['is_visible' => 1]);
        // A new page may take the URL of a removed one.
        $this->get('/test-service')->assertOk()->assertSee('Replacement');
        $this->from(route('cms.trash'))->post(route('cms.trash.restore', $entry))->assertSessionHasErrors('trash');
        $this->assertDatabaseHas('cms_trash', ['id' => $entry->id]);
        $this->assertSame(1, CmsPage::where('slug', 'test-service')->count());
    }

    public function test_deleted_automatic_card_is_not_recreated_until_requested(): void
    {
        $page = $this->createService();
        $card = $this->cardFor($page, 'services-s2');
        $this->delete(route('cms.items.destroy', $card))->assertRedirect();
        $this->assertFalse($page->fresh()->auto_listing);
        $this->savePage($page->fresh(), ['is_visible' => 1]);
        $this->assertNull($this->cardFor($page, 'services-s2'));

        $this->post(route('cms.placements.listing', $page))->assertRedirect();
        $this->post(route('cms.placements.listing', $page))->assertRedirect();
        $this->assertSame(1, $this->section('services-s2')->items()->where('target_page_id', $page->id)->count());
    }

    public function test_listing_can_be_turned_off_without_losing_the_card(): void
    {
        $page = $this->createService();
        $this->savePage($page, ['is_visible' => 1]);
        $card = $this->cardFor($page, 'services-s2');
        $this->savePage($page->fresh(), ['auto_listing' => 0]);
        $this->assertFalse($card->fresh()->is_visible);
        $this->assertSame(0, $this->xpath($this->publicHtml('/services.html'))->query('//div[contains(@class,"services-grid")]//a[@href="/test-service"]')->length);
        $this->savePage($page->fresh(), ['auto_listing' => 1]);
        $this->assertTrue($card->fresh()->is_visible);
        $this->assertSame(1, $this->section('services-s2')->items()->where('target_page_id', $page->id)->count());
    }

    public function test_page_can_be_created_without_a_card_or_menu_entry(): void
    {
        $page = $this->createService(['show_in_listing' => 0, 'add_to_menu' => 0]);
        $this->assertFalse($page->auto_listing);
        $this->assertNull($this->cardFor($page, 'services-s2'));
        $this->assertSame(0, CmsItem::where('target_page_id', $page->id)->whereIn('group_key', CmsMenu::GROUPS)->count());
    }

    public function test_repeated_synchronization_never_duplicates_a_card(): void
    {
        $page = $this->createService();
        $placements = app(CmsPlacements::class);
        $count = CmsItem::count();
        foreach (range(1, 3) as $ignored) {
            $placements->ensureListing($page);
            $placements->syncPage($page);
            $placements->backfill();
        }
        $this->assertSame($count, CmsItem::count());
    }

    public function test_choosing_a_page_sets_the_destination_and_an_external_link_clears_the_relationship(): void
    {
        $card = $this->section('index-s3')->items()->firstOrFail();
        $about = $this->page('about');
        $this->put(route('cms.items.update', $card), $this->payload($card, ['target_page_id' => $about->id]))->assertSessionHasNoErrors();
        $card->refresh();
        $this->assertSame($about->id, $card->target_page_id);
        $this->assertSame('/about.html', $card->content['f2']);

        $content = $card->content;
        $content['f2'] = 'https://external.example.test/page';
        $this->put(route('cms.items.update', $card), $this->payload($card, ['content' => $content, 'target_page_id' => '']))->assertSessionHasNoErrors();
        $card->refresh();
        $this->assertNull($card->target_page_id);
        $this->assertNull($card->overrides);
        $this->savePage($about, ['is_visible' => 0]);
        $this->assertStringContainsString('external.example.test', $this->publicHtml('/'));
    }

    public function test_cards_added_by_hand_join_the_relationship_without_duplicating(): void
    {
        $page = $this->createService(['show_in_listing' => 0]);
        $section = $this->section('services-s2');
        $source = $section->items()->firstOrFail();
        $this->post(route('cms.items.store', $section), ['source_id' => $source->id])->assertSessionHasNoErrors();
        $copy = $this->newestItem($section);
        $this->assertFalse($copy->is_managed);
        $this->put(route('cms.items.update', $copy), $this->payload($copy, ['target_page_id' => $page->id]))->assertSessionHasNoErrors();
        $this->assertSame($page->id, $copy->fresh()->target_page_id);
        $this->assertSame($copy->id, app(CmsPlacements::class)->ensureListing($page->fresh())->id);
    }

    public function test_editors_can_manage_content_relationships_but_anonymous_visitors_cannot(): void
    {
        $this->post('/admin/logout');
        $this->post(route('cms.pages.store'), ['template_id' => 1, 'title' => 'x', 'slug' => 'x'])->assertRedirect('/admin/login');
        $this->actingAs($this->adminWithRole('cms'), 'admin');
        $page = $this->createService();
        $this->assertNotNull($this->cardFor($page, 'services-s2'));
        $this->get(route('cms.pages.edit', $page))->assertOk()->assertSee('Where this page appears');
        $this->get(route('cms.pages', ['type' => 'service']))->assertOk()->assertSee('Add a service');
        $this->get(route('cms.pages', ['type' => 'article']))->assertOk()->assertSee('Add an article');
    }
}
