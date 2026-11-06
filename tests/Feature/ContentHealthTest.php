<?php

namespace Tests\Feature;

use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsTrashEntry;
use App\Services\CmsContentHealth;
use App\Services\WebsiteImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ContentHealthTest extends CmsFeatureTestCase
{
    private const CLINIC_IMAGE = '/website/assets/images/about/clinic.jpg';

    protected function setUp(): void
    {
        parent::setUp();
        $this->asOwner();
    }

    private function report(): array
    {
        return app(CmsContentHealth::class)->report();
    }

    private function setField(string $sectionKey, string $field, string $value): void
    {
        $section = $this->section($sectionKey);
        $content = $section->content;
        $content[$field] = $value;
        $this->put(route('cms.sections.update', $section), $this->payload($section, ['content' => $content]))->assertSessionHasNoErrors();
    }

    public function test_the_missing_clinic_image_is_identified_with_each_location_and_is_not_invented(): void
    {
        $this->assertFileDoesNotExist(public_path(ltrim(self::CLINIC_IMAGE, '/')));
        $this->assertSame(self::CLINIC_IMAGE, $this->section('about-s2')->content['f1']);
        $this->assertSame(self::CLINIC_IMAGE, $this->section('contact-s3')->content['f1']);
        $rows = collect($this->report()['missing_media']);
        $this->assertSame([self::CLINIC_IMAGE], $rows->pluck('value')->unique()->values()->all());
        $this->assertCount(2, $rows);
        $this->assertEqualsCanonicalizing([route('cms.sections.edit', $this->section('about-s2')), route('cms.sections.edit', $this->section('contact-s3'))], $rows->pluck('url')->all());

        $screen = $this->get(route('cms.health'))->assertOk();
        $screen->assertSee(self::CLINIC_IMAGE, false)->assertSee('2 locations');
        $this->assertSame([self::CLINIC_IMAGE => 2], app(CmsContentHealth::class)->missingImages());
    }

    public function test_an_uploaded_image_can_replace_the_missing_file_everywhere(): void
    {
        Storage::fake('public');
        $this->post(route('cms.media.store'), ['file' => UploadedFile::fake()->image('approved-clinic.jpg'), 'alt' => 'Clinic'])->assertSessionHasNoErrors();
        $media = CmsMedia::where('name', 'approved-clinic.jpg')->firstOrFail();

        $this->post(route('cms.health.replace-image'), ['from' => self::CLINIC_IMAGE, 'to' => $media->path])->assertOk()->assertSee('Confirm image replacement');
        $this->assertSame(self::CLINIC_IMAGE, $this->section('about-s2')->content['f1']);
        $usage = app(\App\Services\CmsMediaUsage::class);
        $locations = collect($usage->locations(self::CLINIC_IMAGE))->filter(fn ($v) => $v['editable'])->all();
        $this->post(route('cms.media.replace-source'), ['source' => self::CLINIC_IMAGE, 'target' => $media->path, 'locations' => array_keys($locations), 'token' => $usage->token(self::CLINIC_IMAGE, $media->path, $locations), 'confirmed' => 1])->assertRedirect()->assertSessionHas('success', 'Replaced 2 selected content references. Original files were preserved.');
        $this->assertSame($media->path, $this->section('about-s2')->content['f1']);
        $this->assertSame($media->path, $this->section('contact-s3')->content['f1']);
        $this->assertStringContainsString($media->path, $this->publicHtml('/about.html'));
        $this->assertStringContainsString($media->path, $this->publicHtml('/contact.html'));
        $this->assertStringNotContainsString(self::CLINIC_IMAGE, $this->publicHtml('/contact.html'));
        $this->assertStringContainsString('alt="Clinic"', $this->publicHtml('/about.html'));
        // Now in use, so the media library protects it.
        $this->delete(route('cms.media.destroy', $media))->assertStatus(422);
    }

    public function test_image_replacement_only_accepts_images_from_the_media_library(): void
    {
        Storage::fake('public');
        $this->post(route('cms.health.replace-image'), ['from' => self::CLINIC_IMAGE, 'to' => '/not/in/the/library.jpg'])->assertSessionHasErrors('to');
        $this->post(route('cms.media.store'), ['file' => UploadedFile::fake()->createWithContent('brochure.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF")])->assertSessionHasNoErrors();
        $this->post(route('cms.health.replace-image'), ['from' => self::CLINIC_IMAGE, 'to' => CmsMedia::where('name', 'brochure.pdf')->value('path')])->assertStatus(422);
        $this->assertSame(self::CLINIC_IMAGE, $this->section('about-s2')->content['f1']);
    }

    public function test_placeholder_links_are_identified_and_clear_once_given_a_destination(): void
    {
        $rows = collect($this->report()['placeholder_links']);
        // Eight social links, the clinic video, and the directions button.
        $this->assertCount(10, $rows);
        $this->assertTrue($rows->contains(fn ($row) => $row['url'] === route('cms.sections.edit', $this->section('about-s2'))));
        $this->assertTrue($rows->contains(fn ($row) => $row['url'] === route('cms.sections.edit', $this->section('contact-s3'))));
        $this->setField('about-s2', 'f3', 'https://video.example.test/clinic');
        $this->setField('contact-s3', 'f9', 'https://maps.example.test/directions');
        $this->assertCount(8, $this->report()['placeholder_links']);
    }

    public function test_provisional_text_is_flagged_but_never_changed(): void
    {
        $seedContent = $this->section('index-s6')->items()->get()->pluck('content')->all();
        $rows = collect($this->report()['provisional_text']);
        $testimonials = $this->section('index-s6');
        $this->assertTrue($rows->contains('url', route('cms.sections.edit', $testimonials)));
        foreach ($testimonials->items as $review) {
            $this->assertTrue($rows->contains('url', route('cms.items.edit', $review)));
        }
        $this->assertTrue($rows->contains('url', route('cms.sections.edit', $this->section('contact-s3'))));
        $this->assertTrue($rows->contains('url', route('cms.sections.edit', $this->section('article-1-s1'))));
        $this->assertSame($seedContent, $this->section('index-s6')->items()->get()->pluck('content')->all());
    }

    public function test_every_owner_supplied_detail_can_be_edited_and_reaches_the_public_page(): void
    {
        // Social links.
        foreach (['global-header', 'global-footer'] as $key) {
            $item = $this->section($key)->items()->where('group_key', 'g-1')->firstOrFail();
            $content = $item->content;
            $content['f1'] = 'https://social.example.test/'.$key;
            $this->put(route('cms.items.update', $item), $this->payload($item, ['content' => $content]))->assertSessionHasNoErrors();
            $this->get('/')->assertSee('https://social.example.test/'.$key, false);
        }
        // Video, directions and home media destinations.
        $this->setField('about-s2', 'f3', 'https://video.example.test/clinic');
        $this->get('/about.html')->assertSee('https://video.example.test/clinic', false);
        $this->setField('contact-s3', 'f9', 'https://maps.example.test/directions');
        $this->get('/contact.html')->assertSee('https://maps.example.test/directions', false);
        $this->setField('index-s5', 'f10', 'https://video.example.test/media');
        $this->get('/')->assertSee('https://video.example.test/media', false);
        // Promotional calls to action, including the shared header button and floating contact.
        $this->setField('global-header', 'f11', '/contact.html#location');
        $this->setField('global-header', 'f12', 'EDITED_CTA');
        $this->get('/')->assertSee('EDITED_CTA')->assertSee('/contact.html#location', false);
        $this->setField('global-floating', 'f2', 'https://wa.me/200000000000');
        $this->get('/')->assertSee('https://wa.me/200000000000', false);
        // Clinic copy and map caption.
        $this->setField('about-s2', 'f8', 'EDITED_CLINIC_COPY');
        $this->get('/about.html')->assertSee('EDITED_CLINIC_COPY');
        $this->setField('contact-s3', 'f5', 'EDITED_MAP_CAPTION');
        $this->get('/contact.html')->assertSee('EDITED_MAP_CAPTION');
        // Testimonials.
        $review = $this->section('index-s6')->items()->firstOrFail();
        $content = $review->content;
        $content['f4'] = 'EDITED_TESTIMONIAL';
        $this->put(route('cms.items.update', $review), $this->payload($review, ['content' => $content]))->assertSessionHasNoErrors();
        $this->get('/')->assertSee('EDITED_TESTIMONIAL');
    }

    public function test_directory_points_to_a_working_editor_for_each_detail(): void
    {
        $rows = app(CmsContentHealth::class)->destinations();
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertNotNull($row['url'], $row['label']);
            $this->get($row['url'])->assertOk();
        }
    }

    public function test_map_block_is_unchanged_until_a_destination_is_set(): void
    {
        $section = $this->section('contact-s3');
        $this->assertSame('', $section->content['map_url']);
        $this->assertSame('link', $section->schema['map_url']['type']);
        $xpath = $this->xpath($this->publicHtml('/contact.html'));
        $this->assertSame(0, $xpath->query('//div[contains(@class,"fake-map")]//a')->length);

        $this->setField('contact-s3', 'map_url', 'https://maps.example.test/clinic');
        $xpath = $this->xpath($this->publicHtml('/contact.html'));
        $link = $xpath->query('//div[contains(@class,"fake-map")]//a')->item(0);
        $this->assertSame('https://maps.example.test/clinic', $link->getAttribute('href'));
        $this->assertSame('noopener', $link->getAttribute('rel'));

        $section = $this->section('contact-s3');
        $content = $section->content;
        $content['map_url'] = 'javascript:alert(1)';
        $this->put(route('cms.sections.update', $section), $this->payload($section, ['content' => $content]))->assertSessionHasErrors('content.map_url');
    }

    public function test_broken_and_unpublished_links_are_reported_without_affecting_placeholders(): void
    {
        $this->assertSame([], $this->report()['broken_links']);
        $this->assertSame([], $this->report()['unpublished_links']);

        $this->setField('index-s4', collect($this->section('index-s4')->schema)->search(fn ($f) => $f['type'] === 'link'), '/page-that-does-not-exist');
        $this->assertCount(1, $this->report()['broken_links']);

        // Section buttons cannot hide themselves, so they are reported instead.
        $contact = $this->page('contact');
        $this->put(route('cms.pages.update', $contact), ['title' => $contact->title, 'slug' => $contact->slug, 'is_visible' => 0])->assertSessionHasNoErrors()->assertSessionHas('success', fn ($message) => str_contains($message, 'Content health'));
        $this->assertNotEmpty($this->report()['unpublished_links']);
        // Linked items hide themselves and are listed for information instead.
        $hidden = collect($this->report()['hidden_by_page']);
        $this->assertNotEmpty($hidden);
        $this->assertTrue($hidden->pluck('value')->contains($contact->title));
    }

    public function test_removed_pages_are_reported_as_detached_with_preserved_copy(): void
    {
        $page = $this->page('service-cornea');
        $this->delete(route('cms.pages.destroy', $page))->assertRedirect();
        $this->assertNotEmpty($this->report()['detached']);
        $this->get(route('cms.health'))->assertOk()->assertSee('Hidden because their page was removed');
        $item = \App\Models\CmsItem::where('detached_from', $page->title)->firstOrFail();
        $this->get(route('cms.items.edit', $item))->assertOk()->assertSee('was removed');
    }

    public function test_media_used_only_by_trashed_content_stays_protected_until_purged(): void
    {
        Storage::fake('public');
        $this->post(route('cms.media.store'), ['file' => UploadedFile::fake()->image('in-trash.png')])->assertSessionHasNoErrors();
        $media = CmsMedia::where('name', 'in-trash.png')->firstOrFail();
        $this->setField('services-s3', collect($this->section('services-s3')->schema)->search(fn ($f) => $f['type'] === 'image'), $media->path);
        $section = $this->section('services-s3');

        $this->delete(route('cms.media.destroy', $media))->assertStatus(422);
        $this->delete(route('cms.sections.destroy', $section))->assertRedirect();
        $this->delete(route('cms.media.destroy', $media))->assertStatus(422);
        Storage::disk('public')->assertExists(substr($media->path, 9));

        $this->delete(route('cms.trash.destroy', CmsTrashEntry::where('type', 'section')->firstOrFail()))->assertRedirect();
        $this->delete(route('cms.media.destroy', $media))->assertRedirect();
        Storage::disk('public')->assertMissing(substr($media->path, 9));
    }

    public function test_the_upgrade_archive_does_not_block_media_deletion(): void
    {
        Storage::fake('public');
        $this->post(route('cms.media.store'), ['file' => UploadedFile::fake()->image('archived.png')])->assertSessionHasNoErrors();
        $media = CmsMedia::where('name', 'archived.png')->firstOrFail();
        $entry = CmsTrashEntry::where('type', 'archive')->firstOrFail();
        $payload = $entry->payload;
        $payload['section']['content'] = json_encode(['f4' => $media->path]);
        $entry->update(['payload' => $payload]);
        $this->delete(route('cms.media.destroy', $media))->assertRedirect();
    }

    public function test_media_used_as_a_listing_image_is_protected(): void
    {
        Storage::fake('public');
        $this->post(route('cms.media.store'), ['file' => UploadedFile::fake()->image('listing.png')])->assertSessionHasNoErrors();
        $media = CmsMedia::where('name', 'listing.png')->firstOrFail();
        $page = $this->page('service-exam');
        $this->put(route('cms.pages.update', $page), ['title' => $page->title, 'slug' => $page->slug, 'is_visible' => 1, 'listing' => ['title' => 'x', 'image' => $media->path]])->assertSessionHasNoErrors();
        $this->delete(route('cms.media.destroy', $media))->assertStatus(422);
        $page->update(['listing' => ['title' => 'x']]);
        $this->assertStringContainsString('/storage/', $this->cardFor($page, 'services-s2')->content['f3']);
    }

    public function test_unused_copied_assets_were_removed_and_nothing_referenced_them(): void
    {
        foreach (['service-details.js', 'article-details.js'] as $script) {
            $this->assertFileDoesNotExist(public_path('website/assets/js/'.$script));
            foreach (CmsPage::all() as $page) {
                $this->assertNotContains('/website/assets/js/'.$script, $page->assets['scripts']);
            }
        }
        $this->assertSame([], glob(public_path('website/assets/images/about/*.txt')));
        $this->assertSame(0, CmsMedia::where('path', 'like', '%.txt')->count());
        app(WebsiteImporter::class)->import();
        $this->assertSame(0, CmsMedia::where('path', 'like', '%.txt')->count());
    }

    public function test_only_content_visitors_can_reach_counts_as_a_live_problem(): void
    {
        $before = count($this->report()['placeholder_links']);
        $social = $this->section('global-header')->items()->where('group_key', 'g-1')->firstOrFail();
        $this->patch(route('cms.items.visibility', $social), ['is_visible' => 0])->assertRedirect();
        $this->assertCount($before - 1, $this->report()['placeholder_links']);

        $this->patch(route('cms.sections.visibility', $this->section('about-s2')), ['is_visible' => 0])->assertRedirect();
        $this->assertCount(1, $this->report()['missing_media']);

        // A removed page's placements are explained once, not also reported as broken links.
        $this->delete(route('cms.pages.destroy', $this->page('service-cornea')))->assertRedirect();
        $report = $this->report();
        $this->assertNotEmpty($report['detached']);
        $this->assertSame([], $report['broken_links']);
    }

    public function test_dashboard_surfaces_the_attention_count(): void
    {
        $count = collect($this->report())->only(['missing_media', 'broken_links', 'unpublished_links', 'placeholder_links', 'provisional_text'])->map(fn ($rows) => count($rows))->sum();
        $this->assertGreaterThan(0, $count);
        $this->get('/admin/cms')->assertOk()->assertSee($count.' content item(s) need attention');
    }
}
