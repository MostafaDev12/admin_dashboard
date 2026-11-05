<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\Role;
use App\Services\WebsiteImporter;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

abstract class CmsFeatureTestCase extends TestCase
{
    protected const CMS_MIGRATIONS = [
        'database/migrations/2026_11_05_000000_ensure_cms_admin_auth_tables.php',
        'database/migrations/2026_11_05_000001_create_website_cms_tables.php',
        'database/migrations/2026_11_05_000002_add_cms_content_relationships.php',
    ];

    protected bool $importOnSetUp = true;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--path' => self::CMS_MIGRATIONS, '--force' => true])->assertExitCode(0);
        if ($this->importOnSetUp) {
            app(WebsiteImporter::class)->import();
        }
        // The first administrator is always an owner, so it must not be a test's restricted user.
        $this->owner();
    }

    protected function owner(): Admin
    {
        return Admin::firstOrCreate(['email' => 'owner@example.test'], ['name' => 'Owner', 'password' => Hash::make('owner-test-password'), 'role_id' => 0]);
    }

    protected function adminWithRole(string $sections, string $email = 'editor@example.test'): Admin
    {
        $role = Role::create(['name' => 'Role '.$sections, 'section' => $sections]);

        return Admin::create(['name' => 'Staff member', 'email' => $email, 'password' => Hash::make('staff-test-password'), 'role_id' => $role->id]);
    }

    protected function asOwner(): static
    {
        return $this->actingAs($this->owner(), 'admin');
    }

    protected function page(string $key): CmsPage
    {
        return CmsPage::where('key', $key)->firstOrFail();
    }

    protected function section(string $key): CmsSection
    {
        return CmsSection::where('key', $key)->firstOrFail();
    }

    protected function payload($record, array $changes = []): array
    {
        return array_replace(['label' => $record->label, 'position' => $record->position, 'is_visible' => (int) $record->is_visible, 'content' => $record->content], $changes);
    }

    protected function xpath(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        return new DOMXPath($dom);
    }

    protected function publicHtml(string $url): string
    {
        return $this->get($url)->assertOk()->getContent();
    }

    protected function newestSection(CmsPage $page): CmsSection
    {
        return CmsSection::where('page_id', $page->id)->orderByDesc('id')->firstOrFail();
    }

    protected function newestItem(CmsSection $section): CmsItem
    {
        return CmsItem::where('section_id', $section->id)->orderByDesc('id')->firstOrFail();
    }

    protected function cardFor(CmsPage $page, string $sectionKey): ?CmsItem
    {
        return $this->section($sectionKey)->items()->where('target_page_id', $page->id)->first();
    }

    protected function assertNoDuplicateIds(string $html, string $context): void
    {
        $ids = [];
        foreach ($this->xpath($html)->query('//*[@id]') as $node) {
            $ids[] = $node->getAttribute('id');
        }
        $this->assertSame(array_values(array_unique($ids)), $ids, 'Duplicate DOM ids on '.$context);
    }
}
