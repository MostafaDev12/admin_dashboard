<?php

namespace Tests\Feature;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsTrashEntry;
use App\Services\CmsMenu;
use App\Services\CmsUpgrade;
use App\Services\WebsiteImporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Starts from the pre-consolidation structure an existing installation has. */
class MenuMigrationTest extends CmsFeatureTestCase
{
    protected bool $importOnSetUp = false;

    protected function setUp(): void
    {
        parent::setUp();
        app(WebsiteImporter::class)->import(false);
    }

    /** Hrefs and text in document order for each device. */
    private function structure(string $url = '/about.html'): array
    {
        $xpath = $this->xpath($this->publicHtml($url));
        $read = fn ($nodes) => array_map(fn ($node) => [$node->getAttribute('href'), trim($node->textContent)], iterator_to_array($nodes));

        return [
            'desktop' => $read($xpath->query('//nav[contains(@class,"main-nav")]//a')),
            'mobile' => $read($xpath->query('//div[contains(@class,"mobile-menu__inner")]//a | //div[contains(@class,"mobile-menu__inner")]//button')),
        ];
    }

    private function header(): CmsSection
    {
        return $this->section('global-header');
    }

    private function legacy(string $group, string $textOrLink): CmsItem
    {
        return $this->header()->items()->where('group_key', $group)->get()->firstOrFail(fn ($item) => in_array($textOrLink, $item->content, true));
    }

    public function test_an_installation_that_has_not_been_upgraded_keeps_working(): void
    {
        $this->assertSame('website.generated.global-header', $this->header()->template);
        foreach (CmsPage::all() as $page) {
            $this->get('/'.$page->slug)->assertOk();
        }
        // Rendered by the original template, which marks the active link in the browser.
        $this->get('/')->assertSee('data-nav="home"', false);
        $this->get(route('cms.sections.edit', $this->header()))->assertRedirect('/admin/login');
    }

    public function test_the_current_code_serves_a_database_without_the_new_columns(): void
    {
        $this->artisan('migrate:rollback', ['--path' => ['database/migrations/2026_11_05_000002_add_cms_content_relationships.php'], '--force' => true])->assertExitCode(0);
        $this->assertFalse(Schema::hasColumn('cms_items', 'target_page_id'));
        foreach (CmsPage::all() as $page) {
            $this->get('/'.$page->slug)->assertOk();
        }
        $this->postJson('/appointment', ['name' => 'Patient', 'phone' => '+201001234567'])->assertCreated();
    }

    public function test_migration_keeps_the_seeded_navigation_identical(): void
    {
        foreach (['/', '/about.html', '/service-details.html?service=retina', '/article-details.html?article=2'] as $url) {
            $before = $this->structure($url);
            DB::beginTransaction();
            app(CmsUpgrade::class)->run();
            $this->assertSame($before, $this->structure($url), $url);
            DB::rollBack();
        }
    }

    public function test_migration_replaces_fixed_groups_with_one_menu_and_archives_the_original(): void
    {
        $before = $this->header()->items()->count();
        app(CmsUpgrade::class)->run();
        $header = $this->header();
        $this->assertSame(CmsMenu::TEMPLATE, $header->template);
        $groups = collect($header->groups)->pluck('key')->all();
        $this->assertSame(['g-1', 'g-2', 'g-6', 'menu', 'menu-child'], $groups);
        foreach (['f8', 'f9', 'f10', 'f15', 'f16'] as $retired) {
            $this->assertArrayNotHasKey($retired, $header->schema);
            $this->assertArrayNotHasKey($retired, $header->content);
        }
        $this->assertSame(5, $header->items()->where('group_key', CmsMenu::TOP)->count());
        $this->assertSame(8, $header->items()->where('group_key', CmsMenu::CHILD)->count());
        $this->assertSame(0, $header->items()->whereIn('group_key', ['g-3', 'g-4', 'g-5', 'g-7', 'g-8', 'g-9'])->count());
        foreach ($header->items()->where('group_key', CmsMenu::CHILD)->get() as $child) {
            $this->assertNotNull($child->parent_id);
            $this->assertNotNull($child->target_page_id, $child->label);
        }
        $archive = CmsTrashEntry::where('type', 'archive')->firstOrFail();
        $this->assertCount($before, $archive->payload['items']);
        $this->assertSame('website.generated.global-header', $archive->payload['section']['template']);
    }

    public function test_migration_preserves_customised_labels_visibility_order_and_one_device_items(): void
    {
        // Desktop and mobile were edited independently before consolidation.
        $desktopAbout = $this->legacy('g-3', 'about.html');
        $content = $desktopAbout->content;
        $content['f4'] = 'Desktop about';
        $desktopAbout->update(['content' => $content]);
        $mobileAbout = $this->legacy('g-7', 'about.html');
        $content = $mobileAbout->content;
        $content['f2'] = 'Mobile about';
        $mobileAbout->update(['content' => $content]);

        $this->legacy('g-5', 'contact.html')->update(['is_visible' => false]);
        $this->legacy('g-8', 'service-details.html?service=retina')->update(['is_visible' => false]);

        // Only on mobile, only on desktop, and a mobile item in a different destination.
        $this->post('/admin/logout');
        $mobileOnly = $this->legacy('g-9', 'articles.html')->replicate();
        $mobileOnly->import_key = null;
        $mobileOnly->position = 50;
        $content = $mobileOnly->content;
        $content['f1'] = 'https://mobile-only.example.test';
        $content['f2'] = 'Mobile only';
        $mobileOnly->content = $content;
        $mobileOnly->save();
        $desktopOnly = $this->legacy('g-5', 'articles.html')->replicate();
        $desktopOnly->import_key = null;
        $desktopOnly->position = 60;
        $content = $desktopOnly->content;
        $content['f3'] = 'https://desktop-only.example.test';
        $content['f4'] = 'Desktop only';
        $desktopOnly->content = $content;
        $desktopOnly->save();

        // The trigger's own text.
        $header = $this->header();
        $content = $header->content;
        $content['f9'] = 'Desktop services';
        $content['f15'] = 'Mobile services';
        $header->update(['content' => $content]);

        $before = $this->structure();
        app(CmsUpgrade::class)->run();
        $after = $this->structure();
        $this->assertSame($before['desktop'], $after['desktop']);
        $this->assertSame($before['mobile'], $after['mobile']);
        $this->assertContains(['about.html', 'Desktop about'], $after['desktop']);
        $this->assertContains(['about.html', 'Mobile about'], $after['mobile']);
        $this->assertContains(['https://mobile-only.example.test', 'Mobile only'], $after['mobile']);
        $this->assertNotContains(['https://mobile-only.example.test', 'Mobile only'], $after['desktop']);
        $this->assertContains(['https://desktop-only.example.test', 'Desktop only'], $after['desktop']);
        $this->assertNotContains(['https://desktop-only.example.test', 'Desktop only'], $after['mobile']);
        $this->assertContains(['service-details.html?service=retina', 'علاج أمراض الشبكية'], $after['desktop']);
        $this->assertNotContains(['service-details.html?service=retina', 'علاج أمراض الشبكية'], $after['mobile']);
        $this->assertNotContains('contact.html', array_column($after['desktop'], 0));
        $this->assertContains('contact.html', array_column($after['mobile'], 0));
        $this->assertSame('Mobile services', $this->xpath($this->publicHtml('/'))->query('//button[@data-mobile-services-trigger]/span')->item(0)->textContent);
    }

    public function test_migration_uses_a_single_record_for_both_devices(): void
    {
        app(CmsUpgrade::class)->run();
        $home = $this->header()->items()->where('group_key', CmsMenu::TOP)->get()->first(fn ($item) => $item->content['link'] === 'index.html');
        $this->assertSame('global-header:'.json_decode(file_get_contents(database_path('content/website.json')), true)['sections'][array_search('global-header', array_column(json_decode(file_get_contents(database_path('content/website.json')), true)['sections'], 'key'))]['items'][7]['import_id'], $home->import_key);
        $this->assertSame('', $home->content['mobile_label']);
        $this->assertSame('1', $home->content['show_desktop']);
        $this->assertSame('1', $home->content['show_mobile']);
    }

    public function test_repeating_the_upgrade_and_import_does_not_restore_retired_groups(): void
    {
        app(CmsUpgrade::class)->run();
        $counts = fn () => [CmsItem::count(), CmsSection::count(), CmsTrashEntry::count(), $this->header()->items()->orderBy('id')->get()->map->getAttributes()->all(), $this->header()->getAttributes()];
        $before = $counts();
        app(CmsUpgrade::class)->run();
        app(WebsiteImporter::class)->import();
        app(WebsiteImporter::class)->import(false);
        $this->assertSame($before, $counts());
    }

    public function test_dry_run_reports_without_saving(): void
    {
        $items = CmsItem::count();
        $this->artisan('website:upgrade', ['--dry-run' => true])->expectsOutputToContain('No changes were saved')->assertExitCode(0);
        $this->assertSame('website.generated.global-header', $this->header()->template);
        $this->assertSame($items, CmsItem::count());
        $this->assertSame(0, CmsItem::whereNotNull('target_page_id')->count());
        $this->assertSame(0, CmsTrashEntry::count());
        $this->assertDatabaseMissing('cms_sections', ['key' => 'global-messages']);

        $this->artisan('website:upgrade')->assertExitCode(0);
        $this->assertSame(CmsMenu::TEMPLATE, $this->header()->template);
        $this->assertGreaterThan(0, CmsItem::whereNotNull('target_page_id')->count());
    }

    public function test_edited_records_are_not_marked_as_changed_by_the_backfill(): void
    {
        $page = $this->page('about');
        $card = $this->section('services-s2')->items()->firstOrFail();
        $stamp = $card->updated_at->toDateTimeString();
        $this->travel(1)->hours();
        app(CmsUpgrade::class)->run();
        $this->assertNotNull($card->fresh()->target_page_id);
        $this->assertSame($stamp, $card->fresh()->updated_at->toDateTimeString());
        $this->assertSame('about', $page->key);
    }

    public function test_schema_change_rolls_back_cleanly_on_sqlite(): void
    {
        $this->artisan('migrate:rollback', ['--path' => ['database/migrations/2026_11_05_000002_add_cms_content_relationships.php'], '--force' => true])->assertExitCode(0);
        foreach (['target_page_id', 'is_managed', 'overrides', 'detached_from'] as $column) {
            $this->assertFalse(Schema::hasColumn('cms_items', $column), $column);
        }
        foreach (['listing', 'auto_listing'] as $column) {
            $this->assertFalse(Schema::hasColumn('cms_pages', $column), $column);
        }
        $this->assertFalse(Schema::hasTable('cms_trash'));
        $this->assertFalse(Schema::hasTable('cms_tombstones'));
        $this->assertSame(272, CmsItem::count());
    }
}
