<?php

namespace Tests\Feature;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsTrashEntry;
use App\Services\CmsMenu;

class MenuManagementTest extends CmsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->asOwner();
    }

    private function top(string $label): CmsItem
    {
        return $this->section('global-header')->items()->where('group_key', CmsMenu::TOP)->get()->firstOrFail(fn ($item) => $item->content['label'] === $label);
    }

    private function services(): CmsItem
    {
        return $this->section('global-header')->items()->where('group_key', CmsMenu::TOP)->where('target_page_id', $this->page('services')->id)->firstOrFail();
    }

    private function add(array $data): CmsItem
    {
        $this->post(route('cms.menu.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('cms.menu'));

        return CmsItem::whereIn('group_key', CmsMenu::GROUPS)->orderByDesc('id')->firstOrFail();
    }

    private function update(CmsItem $item, array $data): void
    {
        $this->put(route('cms.menu.update', $item), $data + [
            'label' => $item->content['label'],
            'mobile_label' => $item->content['mobile_label'] ?? '',
            'target_page_id' => $item->target_page_id,
            'url' => $item->target_page_id ? '' : ($item->content['link'] ?? ''),
            'parent_id' => $item->parent_id,
            'show_desktop' => $item->content['show_desktop'] ?? '1',
            'show_mobile' => $item->content['show_mobile'] ?? '1',
            'is_visible' => (int) $item->is_visible,
        ])->assertSessionHasNoErrors();
    }

    /** @return array{desktop: array, mobile: array} Visible link labels by device. */
    private function labels(string $url = '/'): array
    {
        $xpath = $this->xpath($this->publicHtml($url));
        $text = fn ($nodes) => array_map(fn ($node) => trim($node->textContent), iterator_to_array($nodes));

        return [
            'desktop' => $text($xpath->query('//nav[contains(@class,"main-nav")]//a')),
            'mobile' => $text($xpath->query('//div[contains(@class,"mobile-menu__inner")]//a | //div[contains(@class,"mobile-menu__inner")]//button/span')),
        ];
    }

    public function test_menu_screen_lists_the_consolidated_structure(): void
    {
        $screen = $this->get(route('cms.menu'))->assertOk();
        foreach ($this->section('global-header')->items()->whereIn('group_key', CmsMenu::GROUPS)->get() as $item) {
            $screen->assertSee($item->content['label']);
        }
        $this->get(route('cms.menu.edit', $this->services()))->assertOk();
        $this->get(route('cms.menu.edit', $this->section('index-s2')->items()->firstOrFail()))->assertNotFound();
    }

    public function test_add_edit_and_remove_a_top_level_item_linked_to_a_page(): void
    {
        $contact = $this->page('contact');
        $item = $this->add(['label' => 'Book now', 'target_page_id' => $contact->id, 'show_desktop' => 1, 'show_mobile' => 1, 'is_visible' => 1]);
        $this->assertSame($contact->id, $item->target_page_id);
        $this->assertSame('/contact.html', $item->content['link']);
        $labels = $this->labels();
        $this->assertContains('Book now', $labels['desktop']);
        $this->assertContains('Book now', $labels['mobile']);

        $this->update($item, ['label' => 'Reserve', 'mobile_label' => 'Reserve now']);
        $labels = $this->labels();
        $this->assertContains('Reserve', $labels['desktop']);
        $this->assertNotContains('Reserve now', $labels['desktop']);
        $this->assertContains('Reserve now', $labels['mobile']);

        $this->delete(route('cms.menu.destroy', $item))->assertRedirect(route('cms.menu'));
        $this->assertDatabaseMissing('cms_items', ['id' => $item->id]);
        $this->assertNotContains('Reserve', $this->labels()['desktop']);
        $this->post(route('cms.trash.restore', CmsTrashEntry::where('type', 'item')->firstOrFail()))->assertSessionHasNoErrors();
        $this->assertContains('Reserve', $this->labels()['desktop']);
    }

    public function test_custom_links_are_validated(): void
    {
        $item = $this->add(['label' => 'External', 'url' => 'https://example.test/path']);
        $this->assertSame('https://example.test/path', $item->content['link']);
        $this->assertNull($item->target_page_id);
        $this->get('/')->assertSee('https://example.test/path', false);

        foreach (['javascript:alert(1)', 'data:text/html,x', 'vbscript:x', 'not a link'] as $unsafe) {
            $this->post(route('cms.menu.store'), ['label' => 'Bad', 'url' => $unsafe])->assertSessionHasErrors('url');
        }
        $this->post(route('cms.menu.store'), ['label' => ''])->assertSessionHasErrors('label');
        $this->assertSame(0, CmsItem::whereIn('group_key', CmsMenu::GROUPS)->get()->where('label', 'Bad')->count());
    }

    public function test_nested_items_create_a_new_dropdown_that_works_on_both_devices(): void
    {
        $parent = $this->top('المقالات');
        $child = $this->add(['label' => 'Nested article', 'target_page_id' => $this->page('article-1')->id, 'parent_id' => $parent->id]);
        $this->assertSame(CmsMenu::CHILD, $child->group_key);
        $this->assertSame($parent->id, $child->parent_id);

        $xpath = $this->xpath($this->publicHtml('/'));
        $this->assertSame(2, $xpath->query('//nav[contains(@class,"main-nav")]/div[contains(@class,"nav-dropdown")]')->length);
        $this->assertSame(2, $xpath->query('//div[contains(@class,"mobile-menu__inner")]/div[@data-mobile-services]')->length);
        $this->assertStringContainsString("querySelectorAll('[data-mobile-services]')", file_get_contents(public_path('website/assets/js/layout.js')));
    }

    public function test_items_move_between_positions(): void
    {
        $about = $this->top('عن الدكتور');
        $before = $this->labels()['desktop'];
        $this->assertLessThan(array_search('عن الدكتور', $before, true), array_search('الرئيسية', $before, true));

        $this->post(route('cms.menu.move', $about), ['direction' => 'up'])->assertRedirect();
        $labels = $this->labels();
        foreach ($labels as $device => $list) {
            $this->assertLessThan(array_search('الرئيسية', $list, true), array_search('عن الدكتور', $list, true), $device);
        }

        // Into a dropdown, then back out to the end of the top level.
        $this->update($about->fresh(), ['parent_id' => $this->services()->id]);
        $about->refresh();
        $this->assertSame(CmsMenu::CHILD, $about->group_key);
        $this->assertSame($this->services()->id, $about->parent_id);
        $xpath = $this->xpath($this->publicHtml('/'));
        $this->assertSame(1, $xpath->query('//div[contains(@class,"nav-dropdown__menu")]/a[contains(.,"عن الدكتور")]')->length);
        $this->assertSame(1, $xpath->query('//div[contains(@class,"mobile-services__menu")]/a[contains(.,"عن الدكتور")]')->length);

        $this->update($about->fresh(), ['parent_id' => '']);
        $about->refresh();
        $this->assertSame(CmsMenu::TOP, $about->group_key);
        $this->assertNull($about->parent_id);
        $this->assertSame('عن الدكتور', collect($this->labels()['mobile'])->last());

        // Moving past the ends changes nothing and invalid directions are rejected.
        $first = $this->section('global-header')->items()->where('group_key', CmsMenu::TOP)->whereNull('parent_id')->orderBy('position')->firstOrFail();
        $this->post(route('cms.menu.move', $first), ['direction' => 'up'])->assertRedirect();
        $this->post(route('cms.menu.move', $first), ['direction' => 'left'])->assertSessionHasErrors('direction');
    }

    public function test_menu_depth_stays_within_the_supported_design(): void
    {
        $services = $this->services();
        $home = $this->top('الرئيسية');
        $child = $services->children()->firstOrFail();
        $this->post(route('cms.menu.store'), ['label' => 'Too deep', 'parent_id' => $child->id])->assertSessionHasErrors('parent_id');
        $this->put(route('cms.menu.update', $services), ['label' => 'x', 'parent_id' => $home->id])->assertSessionHasErrors('parent_id');
        $this->put(route('cms.menu.update', $home), ['label' => 'x', 'parent_id' => $home->id])->assertSessionHasErrors('parent_id');
        $this->post(route('cms.menu.store'), ['label' => 'Bad parent', 'parent_id' => $this->section('index-s2')->items()->firstOrFail()->id])->assertSessionHasErrors('parent_id');
    }

    public function test_visibility_and_device_controls(): void
    {
        $about = $this->top('عن الدكتور');
        $this->patch(route('cms.items.visibility', $about), ['is_visible' => 0])->assertRedirect();
        $labels = $this->labels();
        $this->assertNotContains('عن الدكتور', $labels['desktop']);
        $this->assertNotContains('عن الدكتور', $labels['mobile']);
        $this->patch(route('cms.items.visibility', $about), ['is_visible' => 1])->assertRedirect();

        $this->update($about->fresh(), ['show_desktop' => 0]);
        $labels = $this->labels();
        $this->assertNotContains('عن الدكتور', $labels['desktop']);
        $this->assertContains('عن الدكتور', $labels['mobile']);

        $this->update($about->fresh(), ['show_desktop' => 1, 'show_mobile' => 0]);
        $labels = $this->labels();
        $this->assertContains('عن الدكتور', $labels['desktop']);
        $this->assertNotContains('عن الدكتور', $labels['mobile']);
    }

    public function test_hiding_all_dropdown_items_on_one_device_turns_it_into_a_plain_link_there(): void
    {
        foreach ($this->services()->children as $child) {
            $this->update($child, ['show_mobile' => 0]);
        }
        $xpath = $this->xpath($this->publicHtml('/'));
        $this->assertSame(1, $xpath->query('//nav[contains(@class,"main-nav")]/div[contains(@class,"nav-dropdown")]')->length);
        $this->assertSame(0, $xpath->query('//div[contains(@class,"mobile-menu__inner")]/div[@data-mobile-services]')->length);
        $this->assertSame(1, $xpath->query('//div[contains(@class,"mobile-menu__inner")]/a[contains(.,"الخدمات")]')->length);
    }

    public function test_active_state_follows_the_destination(): void
    {
        $active = function (string $url): array {
            $nodes = $this->xpath($this->publicHtml($url))->query('//nav[contains(@class,"main-nav")]//a[contains(concat(" ",normalize-space(@class)," ")," active ")]');

            return array_map(fn ($node) => trim($node->textContent), iterator_to_array($nodes));
        };
        $this->assertSame(['الرئيسية'], $active('/'));
        $this->assertSame(['الرئيسية'], $active('/index.html'));
        $this->assertSame(['عن الدكتور'], $active('/about.html'));
        $this->assertSame(['الخدمات'], $active('/services.html'));
        $this->assertSame(['الخدمات'], $active('/service-details.html?service=cataract'));
        $this->assertSame(['المقالات'], $active('/articles.html'));
        $this->assertSame(['المقالات'], $active('/article-details.html?article=4'));
        $this->assertSame(['اتصل بنا'], $active('/contact.html'));

        // A page copied from About must not light up the About link.
        $this->post(route('cms.pages.store'), ['template_id' => $this->page('about')->id, 'title' => 'Custom', 'slug' => 'custom-page'])->assertSessionHasNoErrors();
        $custom = CmsPage::where('slug', 'custom-page')->firstOrFail();
        $custom->update(['is_visible' => true]);
        $this->assertSame([], $active('/custom-page'));

        // Giving it a menu item makes that item the active one, whatever its position.
        $this->add(['label' => 'Custom link', 'target_page_id' => $custom->id]);
        $this->assertSame(['Custom link'], $active('/custom-page'));

        // A clone of the Home link does not take over highlighting on Home.
        $this->assertContains('الرئيسية', $active('/'));
    }

    public function test_links_to_unpublished_pages_are_never_shown(): void
    {
        $about = $this->page('about');
        $about->update(['is_visible' => false]);
        $labels = $this->labels();
        $this->assertNotContains('عن الدكتور', $labels['desktop']);
        $this->assertNotContains('عن الدكتور', $labels['mobile']);
        $about->update(['is_visible' => true]);

        // A dropdown survives when only its parent's own page is unpublished.
        $services = $this->page('services');
        $services->update(['is_visible' => false]);
        $xpath = $this->xpath($this->publicHtml('/'));
        $trigger = $xpath->query('//a[contains(@class,"nav-dropdown__trigger")]')->item(0);
        $this->assertSame('#', $trigger->getAttribute('href'));
        $this->assertGreaterThan(0, $xpath->query('//div[contains(@class,"nav-dropdown__menu")]/a')->length);

        // With no child left either, the whole entry disappears.
        CmsPage::where('type', 'service')->update(['is_visible' => false]);
        $xpath = $this->xpath($this->publicHtml('/'));
        $this->assertSame(0, $xpath->query('//div[contains(@class,"nav-dropdown")]')->length);
        $this->assertSame(0, $xpath->query('//div[@data-mobile-services]')->length);
    }

    public function test_dropdown_with_a_placeholder_parent_disappears_when_it_has_no_visible_items(): void
    {
        $parent = $this->add(['label' => 'Placeholder parent', 'url' => '#']);
        $child = $this->add(['label' => 'Only child', 'target_page_id' => $this->page('about')->id, 'parent_id' => $parent->id]);
        $this->assertContains('Placeholder parent', $this->labels()['desktop']);
        $this->page('about')->update(['is_visible' => false]);
        $this->assertNotContains('Placeholder parent', $this->labels()['desktop']);
        $this->assertSame($parent->id, $child->fresh()->parent_id);
    }

    public function test_removing_a_page_hides_its_menu_links_and_restoring_brings_them_back(): void
    {
        $page = $this->page('service-eyelid');
        $child = $this->services()->children()->where('target_page_id', $page->id)->firstOrFail();
        $this->delete(route('cms.pages.destroy', $page))->assertRedirect();
        $this->assertFalse($child->fresh()->is_visible);
        $this->assertStringNotContainsString('service=eyelid', $this->publicHtml('/'));
        $this->post(route('cms.trash.restore', CmsTrashEntry::where('type', 'page')->firstOrFail()))->assertSessionHasNoErrors();
        $this->assertTrue($child->fresh()->is_visible);
        $this->assertStringContainsString('service=eyelid', $this->publicHtml('/'));
    }

    public function test_new_service_can_join_the_services_dropdown(): void
    {
        $this->post(route('cms.pages.store'), ['template_id' => $this->page('service-exam')->id, 'title' => 'Dropdown service', 'slug' => 'dropdown-service', 'summary' => 's', 'add_to_menu' => 1])->assertSessionHasNoErrors();
        $page = CmsPage::where('slug', 'dropdown-service')->firstOrFail();
        $child = $this->services()->children()->where('target_page_id', $page->id)->firstOrFail();
        $this->assertSame(CmsMenu::CHILD, $child->group_key);
        $this->assertStringNotContainsString('/dropdown-service', $this->publicHtml('/'));
        $page->update(['is_visible' => true]);
        $this->assertStringContainsString('/dropdown-service', $this->publicHtml('/'));
    }

    public function test_editors_manage_menus_but_anonymous_visitors_cannot(): void
    {
        $this->actingAs($this->adminWithRole('cms'), 'admin');
        $this->get(route('cms.menu'))->assertOk();
        $item = $this->add(['label' => 'From editor', 'url' => '#x']);
        $this->post('/admin/logout');
        $this->get(route('cms.menu'))->assertRedirect('/admin/login');
        $this->post(route('cms.menu.store'), ['label' => 'x'])->assertRedirect('/admin/login');
        $this->put(route('cms.menu.update', $item), ['label' => 'x'])->assertRedirect('/admin/login');
        $this->delete(route('cms.menu.destroy', $item))->assertRedirect('/admin/login');
        $this->post(route('cms.menu.move', $item), ['direction' => 'up'])->assertRedirect('/admin/login');
        $this->assertDatabaseHas('cms_items', ['id' => $item->id]);
    }

    public function test_menu_labels_are_escaped(): void
    {
        $this->add(['label' => '<script>alert(1)</script>', 'url' => '#safe']);
        $html = $this->publicHtml('/');
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }
}
