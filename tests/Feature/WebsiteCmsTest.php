<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CmsItem;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsSubmission;
use App\Models\Role;
use App\Services\WebsiteImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteCmsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        $this->artisan('migrate', ['--path' => ['database/migrations/2026_11_05_000000_ensure_cms_admin_auth_tables.php', 'database/migrations/2026_11_05_000001_create_website_cms_tables.php', 'database/migrations/2026_11_05_000002_add_cms_content_relationships.php'], '--force' => true])->assertExitCode(0);
        app(WebsiteImporter::class)->import();
    }

    private function owner(): Admin
    {
        return Admin::create(['name' => 'Test owner', 'email' => 'owner@example.test', 'password' => Hash::make('a-test-password-only'), 'role_id' => 0]);
    }

    private function contentPayload($record, array $extra = []): array
    {
        return array_replace(['label' => $record->label, 'position' => $record->position, 'is_visible' => 1, 'content' => $record->content], $extra);
    }

    public function test_every_original_page_and_detail_renders_with_seo(): void
    {
        foreach (CmsPage::all() as $page) {
            $this->get('/'.$page->slug)->assertOk()->assertSee('<link rel="canonical"', false)->assertSee($page->title);
        }
        $this->get('/sitemap.xml')->assertOk()->assertSee('urlset');
        $this->get('/missing-page')->assertNotFound();
    }

    public function test_import_is_idempotent_and_preserves_editor_changes(): void
    {
        $page = CmsPage::first();
        $page->update(['title' => 'Edited title']);
        $before = [CmsPage::count(), CmsSection::count(), CmsItem::count()];
        app(WebsiteImporter::class)->import();
        $this->assertSame($before, [CmsPage::count(), CmsSection::count(), CmsItem::count()]);
        $this->assertSame('Edited title', $page->fresh()->title);
    }

    public function test_authentication_regenerates_session_and_restricts_access(): void
    {
        $this->get('/admin/cms')->assertRedirect('/admin/login');
        $this->post('/admin/cms/pages', [])->assertRedirect('/admin/login');
        $owner = $this->owner();
        $this->post('/admin/login', ['email' => $owner->email, 'password' => 'a-test-password-only'])->assertRedirect('/admin/cms');
        $this->assertAuthenticatedAs($owner, 'admin');
        $this->get('/admin/cms')->assertOk();
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_editor_cannot_manage_users_and_non_editor_cannot_manage_cms(): void
    {
        $this->owner();
        $role = Role::create(['name' => 'Editor', 'section' => 'cms']);
        $editor = Admin::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => Hash::make('test-password-only'), 'role_id' => $role->id]);
        $this->actingAs($editor, 'admin')->get('/admin/cms/pages')->assertOk();
        $this->get('/admin/cms/users')->assertForbidden();
        $role->update(['section' => 'slider']);
        $editor->unsetRelation('role');
        $this->get('/admin/cms/pages')->assertForbidden();
    }

    public function test_section_edit_visibility_and_escaped_content_reach_public_page(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $section = CmsSection::where('key', 'index-s1')->firstOrFail();
        $content = $section->content;
        $key = collect($section->schema)->search(fn ($f) => $f['type'] === 'text');
        $content[$key] = 'CMS updated <script>alert(1)</script>';
        $this->put(route('cms.sections.update', $section), $this->contentPayload($section, ['content' => $content]))->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/')->assertSee('CMS updated &lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->put(route('cms.sections.update', $section), $this->contentPayload($section->fresh(), ['is_visible' => 0]))->assertRedirect();
        $this->get('/')->assertDontSee('CMS updated');
        $content = $section->fresh()->content;
        $content[$key] = '';
        $this->put(route('cms.sections.update', $section), $this->contentPayload($section->fresh(), ['content' => $content]))->assertSessionHasNoErrors();
        $this->assertSame('', $section->fresh()->content[$key]);
    }

    public function test_collection_add_edit_sort_hide_and_delete(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $section = CmsSection::where('key', 'index-s2')->firstOrFail();
        $source = $section->items()->first();
        $this->post(route('cms.items.store', $section), ['source_id' => $source->id])->assertRedirect();
        $copy = CmsItem::latest('id')->first();
        $content = $copy->content;
        $text = collect($copy->schema)->search(fn ($f) => $f['type'] === 'text');
        $content[$text] = 'New collection item';
        $this->put(route('cms.items.update', $copy), $this->contentPayload($copy, ['content' => $content, 'position' => 0]))->assertSessionHasNoErrors();
        $this->get('/')->assertSee('New collection item');
        $this->put(route('cms.items.update', $copy), $this->contentPayload($copy->fresh(), ['is_visible' => 0]))->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('New collection item');
        $this->delete(route('cms.items.destroy', $copy))->assertRedirect();
        $this->assertDatabaseMissing('cms_items', ['id' => $copy->id]);
        $this->assertDatabaseHas('cms_items', ['id' => $source->id]);
        $this->put(route('cms.items.update', $source), $this->contentPayload($source, ['position' => 99]))->assertSessionHasNoErrors();
        $html = $this->get('/')->assertOk()->getContent();
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $titles = (new \DOMXPath($dom))->query('//section[contains(@class,"trust-strip")]//b');
        $last = $titles->item($titles->length - 1)->textContent;
        $titleKey = collect($source->schema)->search(fn ($f) => $f['type'] === 'text');
        $this->assertSame(trim($source->content[$titleKey]), trim($last));
    }

    public function test_menus_and_shared_settings_are_editable(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $section = CmsSection::where('key', 'global-header')->first();
        $item = $section->items->first(fn ($i) => $i->group_key === \App\Services\CmsMenu::TOP && in_array('index.html', $i->content, true));
        $this->assertNotNull($item);
        $content = $item->content;
        $k = collect($item->schema)->search(fn ($f) => $f['type'] === 'text');
        $content[$k] = 'Updated navigation';
        $this->put(route('cms.items.update', $item), $this->contentPayload($item, ['content' => $content]))->assertSessionHasNoErrors();
        $this->get('/about.html')->assertSee('Updated navigation');
        $settings = CmsSection::where('key', 'global-site')->firstOrFail();
        $content = $settings->content;
        $content['email'] = 'new@example.test';
        $this->put(route('cms.sections.update', $settings), $this->contentPayload($settings, ['content' => $content]))->assertSessionHasNoErrors();
        $this->get('/contact.html')->assertSee('mailto:new@example.test', false)->assertDontSee('info@drmohamedbaz.com');
    }

    public function test_page_seo_redirects_drafts_and_template_creation(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $page = CmsPage::where('key', 'about')->first();
        $this->put(route('cms.pages.update', $page), ['title' => 'Updated title', 'description' => 'Updated description', 'slug' => 'doctor', 'is_visible' => 1, 'seo' => ['title' => 'SEO title', 'noindex' => 1]])->assertSessionHasNoErrors();
        $this->get('/about.html')->assertRedirect('/doctor');
        $this->get('/doctor')->assertOk()->assertSee('<title>SEO title</title>', false)->assertSee('noindex,nofollow');
        $this->post(route('cms.pages.store'), ['title' => 'New page', 'slug' => 'new-page', 'template_id' => $page->id])->assertSessionHasNoErrors();
        $new = CmsPage::where('slug', 'new-page')->firstOrFail();
        $this->assertFalse($new->is_visible);
        $this->assertSame($page->sections()->count(), $new->sections()->count());
        $this->get('/new-page')->assertNotFound();
    }

    public function test_unsafe_links_and_uploads_are_rejected_and_used_media_cannot_be_deleted(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $section = CmsSection::where('key', 'global-header')->first();
        $content = $section->content;
        $key = collect($section->schema)->search(fn ($f) => $f['type'] === 'link');
        $content[$key] = 'javascript:alert(1)';
        $this->put(route('cms.sections.update', $section), $this->contentPayload($section, ['content' => $content]))->assertSessionHasErrors('content.'.$key);
        Storage::fake('public');
        $this->post(route('cms.media.store'), ['file' => UploadedFile::fake()->create('bad.php', 5, 'application/x-php')])->assertSessionHasErrors('file');
        $image = UploadedFile::fake()->image('new.png');
        $this->post(route('cms.media.store'), ['file' => $image, 'alt' => 'Test image'])->assertSessionHasNoErrors();
        $media = CmsMedia::where('name', 'new.png')->firstOrFail();
        Storage::disk('public')->assertExists(substr($media->path, 9));
        $s = CmsSection::where('key', 'index-s1')->first();
        $data = $s->content;
        $imageKey = collect($s->schema)->search(fn ($f) => $f['type'] === 'image');
        $data[$imageKey] = $media->path;
        $s->update(['content' => $data]);
        $this->delete(route('cms.media.destroy', $media))->assertStatus(422);
    }

    public function test_contact_validation_persistence_and_admin_status(): void
    {
        $this->postJson('/appointment', [])->assertUnprocessable();
        $this->postJson('/appointment', ['name' => 'Patient', 'phone' => '+201001234567', 'email' => 'patient@example.test', 'details' => 'A real request'])->assertCreated();
        $this->assertDatabaseCount('cms_submissions', 1);
        $submission = CmsSubmission::first();
        $this->actingAs($this->owner(), 'admin')->get('/admin/cms/submissions')->assertOk()->assertSee('A real request');
        $this->put(route('cms.submissions.update', $submission), ['status' => 'contacted'])->assertRedirect();
        $this->assertSame('contacted', $submission->fresh()->status);
    }

    public function test_all_cms_editor_screens_render(): void
    {
        $this->actingAs($this->owner(), 'admin');
        foreach (['/admin/cms', '/admin/cms/pages', '/admin/cms/globals', '/admin/cms/media', '/admin/cms/submissions', '/admin/cms/users'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (CmsSection::all() as $section) {
            $this->get(route('cms.sections.edit', $section))->assertOk();
        }$this->get(route('cms.items.edit', CmsItem::first()))->assertOk();
        $this->get(route('cms.pages.edit', CmsPage::first()))->assertOk();
    }

    public function test_an_empty_collection_can_be_repopulated_and_nested_items_are_cloned(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $section = CmsSection::where('key', 'index-s2')->firstOrFail();
        $group = $section->groups[0];
        $section->items()->delete();
        $this->post(route('cms.items.store', $section), ['group_key' => $group['key']])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(1, $section->items()->count());
        $this->get('/')->assertOk();
        $footer = CmsSection::where('key', 'global-footer')->with('items')->first();
        $parent = $footer->items->first(fn ($i) => $footer->items->where('parent_id', $i->id)->isNotEmpty());
        $this->assertNotNull($parent);
        $count = $footer->items()->count();
        $children = $footer->items->where('parent_id', $parent->id)->count();
        $this->post(route('cms.items.store', $footer), ['source_id' => $parent->id])->assertSessionHasNoErrors();
        $this->assertSame($count + $children + 1, $footer->items()->count());
    }
}
