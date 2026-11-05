<?php

namespace Tests\Feature;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Services\CmsPageLinks;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/** Sweeps the whole site rather than one feature, so regressions anywhere surface here. */
class SiteRegressionTest extends CmsFeatureTestCase
{
    public function test_every_original_page_renders_with_complete_seo_and_no_duplicate_ids(): void
    {
        $this->assertSame(22, CmsPage::count());
        foreach (CmsPage::all() as $page) {
            $html = $this->publicHtml('/'.$page->slug);
            $xpath = $this->xpath($html);
            $this->assertSame(1, $xpath->query('//title')->length, $page->key);
            $this->assertSame($page->title, $xpath->query('//title')->item(0)->textContent);
            $this->assertSame(1, $xpath->query('//link[@rel="canonical"]')->length, $page->key);
            $this->assertSame(1, $xpath->query('//meta[@name="description"]')->length, $page->key);
            $this->assertSame(1, $xpath->query('//meta[@name="robots"]')->length, $page->key);
            $this->assertSame(1, $xpath->query('//meta[@property="og:title"]')->length, $page->key);
            $this->assertSame(1, $xpath->query('//meta[@property="og:description"]')->length, $page->key);
            $this->assertSame(1, $xpath->query('//meta[@name="csrf-token"]')->length, $page->key);
            $this->assertNoDuplicateIds($html, $page->key);
        }
    }

    public function test_every_internal_link_leads_to_a_published_page_or_an_existing_anchor(): void
    {
        $pages = CmsPage::where('is_visible', true)->get()->keyBy('slug');
        $links = app(CmsPageLinks::class);
        $idsByPage = [];
        foreach ($pages as $slug => $page) {
            $idsByPage[$slug] = array_map(fn ($node) => $node->getAttribute('id'), iterator_to_array($this->xpath($this->publicHtml('/'.$slug))->query('//*[@id]')));
        }
        foreach ($pages as $slug => $page) {
            foreach ($this->xpath($this->publicHtml('/'.$slug))->query('//a[@href]') as $anchor) {
                $href = $anchor->getAttribute('href');
                if ($href === '#' || preg_match('~^(https?:|mailto:|tel:)~', $href)) {
                    continue;
                }
                if ($href[0] === '#') {
                    $this->assertContains(substr($href, 1), $idsByPage[$slug], $slug.' → '.$href);

                    continue;
                }
                $target = $links->parse($href);
                $this->assertNotNull($target, $href);
                $this->assertTrue($pages->has($target['slug']), $slug.' → '.$href);
                if ($target['fragment'] !== '') {
                    $this->assertContains($target['fragment'], $idsByPage[$target['slug']], $slug.' → '.$href);
                }
            }
        }
    }

    public function test_sitemap_lists_published_indexable_pages_only(): void
    {
        $this->asOwner();
        $about = $this->page('about');
        $contact = $this->page('contact');
        $this->get('/sitemap.xml')->assertOk()->assertSee('about.html')->assertSee('contact.html');
        $this->put(route('cms.pages.update', $about), ['title' => $about->title, 'slug' => $about->slug, 'is_visible' => 0])->assertSessionHasNoErrors();
        $this->put(route('cms.pages.update', $contact), ['title' => $contact->title, 'slug' => $contact->slug, 'is_visible' => 1, 'seo' => ['noindex' => 1]])->assertSessionHasNoErrors();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('about.html')->assertDontSee('contact.html');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin');
    }

    public function test_seo_fields_reach_the_page_and_a_copied_page_starts_clean(): void
    {
        $this->asOwner();
        $page = $this->page('service-exam');
        $this->put(route('cms.pages.update', $page), ['title' => $page->title, 'slug' => $page->slug, 'is_visible' => 1, 'seo' => ['title' => 'SEO_TITLE', 'description' => 'SEO_DESCRIPTION', 'og_title' => 'OG_TITLE', 'canonical' => 'https://example.test/canonical', 'noindex' => 1]])->assertSessionHasNoErrors();
        $html = $this->publicHtml('/'.$page->slug);
        foreach (['<title>SEO_TITLE</title>', 'content="SEO_DESCRIPTION"', 'content="OG_TITLE"', 'href="https://example.test/canonical"', 'noindex,nofollow'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }
        $this->post(route('cms.pages.store'), ['template_id' => $page->id, 'title' => 'Copy', 'slug' => 'seo-copy', 'summary' => 'Summary'])->assertSessionHasNoErrors();
        $copy = CmsPage::where('slug', 'seo-copy')->firstOrFail();
        $this->assertSame([], $copy->seo);
    }

    public function test_every_dashboard_screen_renders_for_owners_and_editors(): void
    {
        $editor = $this->adminWithRole('cms');
        foreach ([$this->owner(), $editor] as $admin) {
            $this->actingAs($admin, 'admin');
            foreach (['/admin/cms', '/admin/cms/pages', '/admin/cms/pages?type=service', '/admin/cms/pages?type=article', '/admin/cms/globals', '/admin/cms/media', '/admin/cms/submissions', '/admin/cms/menu', '/admin/cms/health', '/admin/cms/trash'] as $url) {
                $this->get($url)->assertOk();
            }
            foreach (CmsPage::all() as $page) {
                $this->get(route('cms.pages.edit', $page))->assertOk();
            }
            foreach (CmsSection::all() as $section) {
                $this->get(route('cms.sections.edit', $section))->assertOk();
            }
        }
        foreach (CmsItem::all() as $item) {
            $this->get(route('cms.items.edit', $item))->assertOk();
        }
    }

    public function test_editor_labels_use_plain_language(): void
    {
        $this->asOwner();
        $screen = $this->get(route('cms.sections.edit', $this->section('services-s2')))->assertOk();
        $screen->assertSee('Service cards')->assertDontSee('services-grid · article');
        $this->get(route('cms.items.edit', $this->section('services-s2')->items()->firstOrFail()))->assertOk()->assertSee('Link — service-details.html?service=cataract', false)->assertDontSee('href ·');
        $this->get(route('cms.pages.edit', $this->page('index')))->assertOk()->assertSee('Trust strip')->assertSee('Services preview')->assertDontSee('section section--white services-preview');
    }

    public function test_admin_command_creates_owners_and_invalidates_remember_me_on_reset(): void
    {
        $this->artisan('website:admin', ['email' => 'cli@example.test', '--name' => 'CLI owner'])->expectsQuestion('Password (at least 12 characters)', 'first-cli-password')->assertExitCode(0);
        $admin = \App\Models\Admin::where('email', 'cli@example.test')->firstOrFail();
        $this->assertTrue($admin->IsSuper());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('first-cli-password', $admin->password));
        $token = $admin->remember_token;
        $this->artisan('website:admin', ['email' => 'cli@example.test', '--name' => 'CLI owner'])->expectsQuestion('Password (at least 12 characters)', 'second-cli-password')->assertExitCode(0);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('second-cli-password', $admin->fresh()->password));
        $this->assertNotSame($token, $admin->fresh()->remember_token);
    }

    public function test_cms_and_public_views_only_reference_defined_routes(): void
    {
        foreach (['cms', 'website', 'layouts', 'errors'] as $directory) {
            foreach (File::allFiles(resource_path('views/'.$directory)) as $file) {
                preg_match_all("~route\('([A-Za-z0-9_.-]+)'~", $file->getContents(), $matches);
                foreach ($matches[1] as $name) {
                    $this->assertTrue(Route::has($name), $file->getRelativePathname().' → '.$name);
                }
            }
        }
    }

    public function test_cms_mutations_cannot_be_reached_with_get(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'admin/cms') || ! in_array('GET', $route->methods(), true)) {
                continue;
            }
            $action = $route->getActionMethod();
            $this->assertMatchesRegularExpression('~^(dashboard|pages|page|globals|section|item|media|submissions|index|edit|messages)$~', $action, $route->uri());
        }
    }

    public function test_public_forms_still_validate_save_and_report_through_the_managed_copy(): void
    {
        $this->postJson('/appointment', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone']);
        $this->postJson('/appointment', ['name' => 'Patient', 'phone' => '+201001234567', 'details' => 'Needs follow-up', 'service' => 'Retina'])->assertCreated();
        $this->assertDatabaseHas('cms_submissions', ['name' => 'Patient']);
        foreach (['/contact.html', '/about.html', '/', '/service-details.html?service=exam'] as $url) {
            $this->assertGreaterThan(0, $this->xpath($this->publicHtml($url))->query('//form[@method="POST"][contains(@action,"/appointment")]')->length, $url);
        }
    }

    public function test_original_frontend_assets_and_templates_stay_in_place(): void
    {
        foreach (['layout.css', 'home.css', 'about.css', 'services.css', 'articles.css', 'contact.css', 'details.css', 'appointment-form.css'] as $sheet) {
            $this->assertFileExists(public_path('website/assets/css/'.$sheet));
        }
        foreach (['layout.js', 'home.js', 'articles.js', 'forms.js'] as $script) {
            $this->assertFileExists(public_path('website/assets/js/'.$script));
        }
        $this->assertFileExists(resource_path('views/website/generated/global-header.blade.php'));
    }
}
