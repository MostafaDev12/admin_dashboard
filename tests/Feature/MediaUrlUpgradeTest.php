<?php

namespace Tests\Feature;

use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Services\CmsMediaUsage;
use App\Services\CmsTrash;
use App\Services\CmsUrls;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaUrlUpgradeTest extends CmsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_11_06_000003_add_cms_public_slugs.php', '--force' => true])->assertExitCode(0);
        $urls = app(CmsUrls::class);
        $urls->apply($urls->plan()['hash']);
        $this->asOwner();
    }

    public function test_every_original_page_has_a_clean_canonical_and_direct_legacy_redirect(): void
    {
        foreach (config('cms_urls') as $key => $leaf) {
            $page = $this->page($key);
            $this->assertSame($leaf, $page->public_slug);
            $this->assertSame(app(CmsUrls::class)->path($page->type, $leaf), $page->slug);
            $html = $this->get('/'.$page->slug)->assertOk()->getContent();
            $this->assertStringContainsString('<link rel="canonical" href="'.$page->public_url.'">', $html);
            $this->assertStringContainsString('<meta property="og:url" content="'.$page->public_url.'">', $html);
        }
        foreach (DB::table('cms_redirects')->get() as $redirect) {
            $this->get('/'.$redirect->from)->assertStatus(301)->assertRedirect(url($redirect->to));
            $this->get('/'.$redirect->to)->assertOk();
        }
        $this->get('/service-details.html?service=unknown')->assertNotFound();
        $this->get('/article-details.html?article[]=1')->assertNotFound();
        $this->get('/article-details.html?article=1&utm_source=test')->assertStatus(301)->assertRedirect(url('articles/dry-eye').'?utm_source=test');
        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringNotContainsString('.html', $sitemap);
        $this->assertStringNotContainsString('?article=', $sitemap);
    }

    public function test_all_rendered_internal_links_are_canonical_and_resolve(): void
    {
        $checked = [];
        foreach (CmsPage::all() as $page) {
            $html = $this->get('/'.$page->slug)->assertOk()->getContent();
            foreach ($this->xpath($html)->query('//a[@href]') as $node) {
                $href = $node->getAttribute('href');
                $parsed = app(\App\Services\CmsPageLinks::class)->parse($href);
                if (! $parsed) {
                    continue;
                }
                $this->assertStringNotContainsString('.html', $href, $page->key.' → '.$href);
                if (isset($checked[$href])) {
                    continue;
                } $checked[$href] = true;
                $this->get($href)->assertOk();
            }
        }
    }

    public function test_slug_edits_flatten_redirects_and_titles_do_not_change_urls(): void
    {
        $page = $this->page('service-cataract');
        foreach (['cataract-care', 'cataract-surgery'] as $leaf) {
            $this->put(route('cms.pages.update', $page), ['title' => 'Changed title', 'slug' => $leaf, 'is_visible' => 1])->assertSessionHasNoErrors();
            $page->refresh();
            $this->assertSame('services/'.$leaf, $page->slug);
        }
        $this->get('/service-details.html?service=cataract')->assertStatus(301)->assertRedirect(url('services/cataract-surgery'));
        $this->get('/services/cataract')->assertStatus(301)->assertRedirect(url('services/cataract-surgery'));
        $this->put(route('cms.pages.update', $page), ['title' => 'Another title', 'slug' => 'cataract-surgery', 'is_visible' => 1])->assertSessionHasNoErrors();
        $this->assertSame('services/cataract-surgery', $page->fresh()->slug);
        foreach (['../admin', 'UPPER', 'bad.html', 'bad%2fslug', 'retina', 'admin'] as $bad) {
            $this->putJson(route('cms.pages.update', $page), ['title' => 'Title', 'slug' => $bad, 'is_visible' => 1])->assertUnprocessable();
        }
        $this->put(route('cms.pages.update', $page), ['title' => 'Title', 'slug' => 'cataract-surgery', 'is_visible' => 0])->assertSessionHasNoErrors();
        $this->get('/services/cataract-surgery')->assertNotFound();
        $this->get('/service-details.html?service=cataract')->assertNotFound();
    }

    public function test_new_pages_receive_unique_meaningful_slugs_and_arabic_requires_an_explicit_slug(): void
    {
        $source = $this->page('article-1');
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('cms.pages.store'), ['template_id' => $source->id, 'title' => 'Eye Health'])->assertSessionHasNoErrors();
        }
        $this->assertTrue(CmsPage::where('slug', 'articles/eye-health')->exists());
        $this->assertTrue(CmsPage::where('slug', 'articles/eye-health-2')->exists());
        $this->postJson(route('cms.pages.store'), ['template_id' => $source->id, 'title' => 'العين'])->assertUnprocessable();
        $this->post(route('cms.pages.store'), ['template_id' => $source->id, 'title' => 'العين', 'slug' => 'eye-care'])->assertSessionHasNoErrors();
        $this->get('/articles/eye-care')->assertNotFound();
    }

    public function test_trash_restore_preserves_clean_urls_and_aliases(): void
    {
        $page = $this->page('article-1');
        $entry = app(CmsTrash::class)->trashPage($page);
        $this->get('/articles/dry-eye')->assertStatus(302);
        $this->get('/article-details.html?article=1')->assertStatus(302);
        $this->assertNull(app(\App\Services\CmsPageLinks::class)->pageFor('/article-details.html?article=1', CmsPage::all()->keyBy('slug')), 'Temporary trash fallback must not establish a different page identity.');
        $this->post(route('cms.trash.restore', $entry))->assertSessionHasNoErrors();
        $this->get('/articles/dry-eye')->assertOk();
        $this->get('/article-details.html?article=1')->assertStatus(301)->assertRedirect(url('articles/dry-eye'));
        $this->assertSame($page->id, app(\App\Services\CmsPageLinks::class)->pageFor('/article-details.html?article=1', CmsPage::all()->keyBy('slug'))->id);
    }

    public function test_upgrade_is_repeatable_and_refuses_changed_or_conflicting_plans(): void
    {
        $urls = app(CmsUrls::class);
        $before = CmsPage::pluck('slug', 'key')->all();
        $urls->apply($urls->plan()['hash']);
        $this->assertSame($before, CmsPage::pluck('slug', 'key')->all());
        $hash = $urls->plan()['hash'];
        $this->page('about')->update(['title' => 'Editorial change']);
        try {
            $urls->apply($hash);
            $this->fail('Stale plan accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
        foreach (['/admin/no-such-page', '/api/no-such-page', '/storage/no-such-file', '/build/no-such-file', '/assets/no-such-file', '/website/assets/missing', '/unknown.php'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    private function upload(): CmsMedia
    {
        Storage::fake('public');
        $this->postJson(route('cms.media.store'), ['files' => [UploadedFile::fake()->image('replacement.png')]])->assertCreated();

        return CmsMedia::where('name', 'replacement.png')->firstOrFail();
    }

    public function test_media_usage_and_one_field_replacement_leave_other_fields_unchanged(): void
    {
        $target = $this->upload();
        $source = CmsMedia::where('path', $this->section('about-s2')->content['f1'])->first();
        if (! $source) {
            $source = CmsMedia::create(['path' => $this->section('about-s2')->content['f1'], 'name' => 'Missing clinic', 'mime' => 'image/jpeg', 'size' => 0, 'is_original' => true]);
        }
        $usage = app(CmsMediaUsage::class);
        $locations = collect($usage->locations($source->path))->filter(fn ($v) => $v['editable'])->all();
        $this->assertCount(2, $locations);
        $one = [array_key_first($locations) => reset($locations)];
        $preview = $this->post(route('cms.media.preview', $source), ['target' => $target->path, 'mode' => 'one', 'location' => array_key_first($one)])->assertOk();
        $preview->assertSee('Confirm replacement');
        $this->post(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($one), 'token' => $usage->token($source->path, $target->path, $one), 'confirmed' => 1])->assertRedirect();
        $this->assertCount(1, collect($usage->locations($source->path))->filter(fn ($v) => $v['editable']));
        $this->assertCount(1, $usage->locations($target->path));
        $this->delete(route('cms.media.destroy', $target))->assertUnprocessable();
        $this->get(route('cms.media.show', $target))->assertOk()->assertSee('Where used');
    }

    public function test_global_replacement_requires_review_confirmation_and_rejects_stale_content(): void
    {
        $target = $this->upload();
        $source = CmsMedia::where('mime', 'like', 'image/%')->where('is_original', true)->firstOrFail();
        $usage = app(CmsMediaUsage::class);
        $locations = collect($usage->locations($source->path))->filter(fn ($v) => $v['editable'])->all();
        $this->assertNotEmpty($locations);
        $token = $usage->token($source->path, $target->path, $locations);
        $this->postJson(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($locations), 'token' => $token])->assertUnprocessable();
        $this->post(route('cms.media.preview', $source), ['target' => $target->path, 'mode' => 'all'])->assertOk();
        $this->post(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($locations), 'token' => $token, 'confirmed' => 1])->assertRedirect();
        $this->assertEmpty(collect($usage->locations($source->path))->filter(fn ($v) => $v['editable']));
        $this->postJson(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($locations), 'token' => $token, 'confirmed' => 1])->assertUnprocessable();
        $this->delete(route('cms.media.destroy', $source))->assertUnprocessable();
    }

    public function test_picker_alt_security_and_immutable_uploads(): void
    {
        $target = $this->upload();
        $this->postJson(route('cms.media.store'), ['file' => UploadedFile::fake()->image('replacement.png')])->assertCreated();
        $this->assertSame(2, CmsMedia::where('name', 'replacement.png')->distinct()->count('path'));
        $this->put(route('cms.media.update', $target), ['alt' => '<script>alert(1)</script>'])->assertSessionHasNoErrors();
        $this->get(route('cms.media.show', $target))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
        $this->getJson(route('cms.media.picker', ['q' => 'replacement']))->assertOk()->assertJsonFragment(['path' => $target->path]);
        foreach (['php', 'svg', 'html'] as $extension) {
            $this->postJson(route('cms.media.store'), ['file' => UploadedFile::fake()->create('unsafe.'.$extension, 1, 'text/plain')])->assertUnprocessable();
        }
        $this->get(route('cms.pages.edit', $this->page('article-1')))->assertOk()->assertSee('cms-media-picker');
        $this->delete(route('cms.media.destroy', $target))->assertRedirect();
        $this->assertFalse(CmsMedia::whereKey($target->id)->exists());
        $this->app['auth']->guard('admin')->logout();
        $this->getJson(route('cms.media.picker'))->assertRedirect(route('admin.login'));
    }

    public function test_shared_settings_report_inherited_locations_and_allow_one_dependent_override(): void
    {
        $target = $this->upload();
        $settings = $this->section('global-site');
        $source = CmsMedia::where('path', $settings->content['logo'])->firstOrFail();
        $usage = app(CmsMediaUsage::class);
        $locations = collect($usage->locations($source->path))->filter(fn ($v) => $v['editable']);
        $setting = $locations->first(fn ($v) => $v['shared_setting']);
        $this->assertNotNull($setting);
        $this->assertGreaterThan(1, $locations->count());
        $this->post(route('cms.media.preview', $source), ['target' => $target->path, 'mode' => 'one', 'location' => $setting['id']])->assertUnprocessable();
        $all = $locations->all();
        $this->post(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($all), 'token' => $usage->token($source->path, $target->path, $all), 'confirmed' => 1])->assertRedirect();
        $this->assertSame($target->path, $settings->fresh()->content['logo']);
        $this->get('/')->assertSee($target->path, false);
        $this->get('/about')->assertSee($target->path, false);
    }

    public function test_stale_review_and_unrelated_text_cannot_be_modified_by_replacement(): void
    {
        $target = $this->upload();
        $source = CmsMedia::where('path', $this->section('index-s1')->content['f11'])->firstOrFail();
        $section = $this->section('index-s1');
        $content = $section->content;
        $content['unrelated_text'] = $source->path;
        $schema = $section->schema;
        $schema['unrelated_text'] = ['type' => 'text', 'label' => 'Unrelated text'];
        $section->update(['content' => $content, 'schema' => $schema]);
        $usage = app(CmsMediaUsage::class);
        $all = collect($usage->locations($source->path))->filter(fn ($v) => $v['editable'])->all();
        $token = $usage->token($source->path, $target->path, $all);
        $content['another_text'] = 'Concurrent edit';
        $section->update(['content' => $content]);
        $this->postJson(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($all), 'token' => $token, 'confirmed' => 1])->assertStatus(409);
        $all = collect($usage->locations($source->path))->filter(fn ($v) => $v['editable'])->all();
        $this->post(route('cms.media.replace', $source), ['target' => $target->path, 'locations' => array_keys($all), 'token' => $usage->token($source->path, $target->path, $all), 'confirmed' => 1])->assertRedirect();
        $this->assertSame($source->path, $section->fresh()->content['unrelated_text']);
        $this->assertSame('Concurrent edit', $section->fresh()->content['another_text']);
        $this->assertSame($target->path, $section->fresh()->content['f11']);
    }

    public function test_link_normalization_preserves_tracking_fragments_external_and_asset_urls(): void
    {
        $links = app(\App\Services\CmsPageLinks::class);
        $content = ['page' => 'article-details.html?article=1&utm_source=campaign#questions', 'external' => 'https://elsewhere.test/about.html', 'port' => 'http://localhost:9999/about.html', 'asset' => '/website/assets/images/example.jpg', 'mail' => 'mailto:test@example.test', 'anchor' => '#appointment'];
        $schema = array_fill_keys(array_keys($content), ['type' => 'link']);
        $result = $links->canonicalize($content, $schema);
        $this->assertSame('/articles/dry-eye?utm_source=campaign#questions', $result['page']);
        foreach (['external', 'port', 'asset', 'mail', 'anchor'] as $key) {
            $this->assertSame($content[$key], $result[$key]);
        }
        Storage::fake('public');
        $this->postJson(route('cms.media.store'), ['file' => UploadedFile::fake()->createWithContent('spoof.jpg', '<?php echo 123;')])->assertUnprocessable();
    }

    public function test_replacement_preserves_listing_inheritance_and_explicit_one_field_choices(): void
    {
        $target = $this->upload();
        $page = $this->page('service-cataract');
        $source = CmsMedia::where('path', $page->listing['image'])->firstOrFail();
        $card = $this->cardFor($page, 'services-s2');
        $field = app(\App\Services\CmsPlacements::class)->roles($card->section, $card)['image'];
        $this->assertNotContains('image', $card->overrides ?? []);
        $usage = app(CmsMediaUsage::class);
        $all = collect($usage->locations($source->path))->filter(fn ($row) => $row['editable'])->all();
        $usage->replace($source->path, $target->path, array_keys($all), $usage->token($source->path, $target->path, $all));
        $this->assertNotContains('image', $card->fresh()->overrides ?? []);
        $this->assertSame($target->path, $card->fresh()->content[$field]);
        // A one-location listing edit must not change existing cards on a later title save.
        $location = collect($usage->locations($target->path))->first(fn ($row) => $row['model'] === CmsPage::class && $row['record_id'] === $page->id && $row['column'] === 'listing');
        $one = [$location['id'] => $location];
        $usage->replace($target->path, $source->path, array_keys($one), $usage->token($target->path, $source->path, $one));
        $this->assertSame($target->path, $card->fresh()->content[$field]);
        $this->assertContains('image', $card->fresh()->overrides);
        $page->refresh();
        app(\App\Services\CmsPlacements::class)->syncPage($page);
        $this->assertSame($target->path, $card->fresh()->content[$field]);
    }
}
