<?php

namespace Tests\Feature;

use App\Models\CmsItem;
use App\Models\CmsSection;
use App\Models\CmsTrashEntry;
use App\Services\CmsSectionCatalog;
use App\Services\WebsiteImporter;

class SectionManagementTest extends CmsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->asOwner();
    }

    private function catalog(): CmsSectionCatalog
    {
        return app(CmsSectionCatalog::class);
    }

    private function typeFor(string $template): string
    {
        return $this->catalog()->typeOf($template)['key'];
    }

    private function show(CmsSection $section): void
    {
        $this->patch(route('cms.sections.visibility', $section), ['is_visible' => 1])->assertRedirect();
    }

    public function test_only_compatible_trusted_designs_are_offered(): void
    {
        foreach (['about' => 'about-s', 'services' => 'services-s', 'articles' => 'articles-s', 'contact' => 'contact-s', 'index' => 'index-s'] as $key => $prefix) {
            $offered = $this->catalog()->availableFor($this->page($key));
            $this->assertNotEmpty($offered, $key);
            foreach ($offered as $type) {
                $this->assertStringStartsWith('website.generated.'.$prefix, $type['template'], $key);
            }
        }
        // Service and article pages share one stylesheet, so their designs are interchangeable.
        foreach (['service-exam', 'article-3'] as $key) {
            $page = $this->page($key);
            foreach ($this->catalog()->availableFor($page) as $type) {
                $this->assertMatchesRegularExpression('~^website\.generated\.(service|article)-~', $type['template']);
                $this->assertSame([], array_diff($type['styles'], array_map(fn ($url) => strtok($url, '?'), $page->assets['styles'])));
            }
        }
    }

    public function test_identical_designs_are_offered_once(): void
    {
        // Eight identical service layouts and nine identical article layouts collapse to their designs.
        foreach (['service-exam', 'article-1'] as $key) {
            $offered = $this->catalog()->availableFor($this->page($key));
            $this->assertCount(6, $offered);
            $this->assertSame(6, $offered->pluck('key')->unique()->count());
        }
    }

    public function test_single_instance_designs_are_not_offered_twice(): void
    {
        $contact = $this->page('contact');
        $templates = $this->catalog()->availableFor($contact)->pluck('template');
        $this->assertNotContains('website.generated.contact-s2', $templates, 'id="appointment" is already on the page');
        $this->assertNotContains('website.generated.contact-s3', $templates, 'id="location" is already on the page');

        // Once the original is removed its design can be added back.
        $this->delete(route('cms.sections.destroy', $this->section('contact-s3')))->assertRedirect();
        $this->assertContains('website.generated.contact-s3', $this->catalog()->availableFor($contact->fresh())->pluck('template'));
    }

    public function test_added_section_starts_hidden_then_can_be_edited_shown_and_managed(): void
    {
        $page = $this->page('services');
        $before = $page->sections()->count();
        $publicBefore = $this->publicHtml('/services.html');
        $this->post(route('cms.sections.store', $page), ['type' => $this->typeFor('website.generated.services-s4')])->assertSessionHasNoErrors()->assertRedirect();
        $section = $this->newestSection($page);

        $this->assertSame($before + 1, $page->sections()->count());
        $this->assertFalse($section->is_visible);
        $this->assertSame((int) $page->sections()->reorder()->max('position'), $section->position);
        $this->assertStringStartsWith('section-', $section->key);
        $this->assertSame('website.generated.services-s4', $section->template);
        $this->assertNotEmpty($section->items);
        $this->assertSame(strlen(preg_replace('/name="csrf-token" content="[^"]*"|name="_token" value="[^"]*"/', '', $publicBefore)), strlen(preg_replace('/name="csrf-token" content="[^"]*"|name="_token" value="[^"]*"/', '', $this->publicHtml('/services.html'))));

        // Edit its content.
        $field = collect($section->schema)->search(fn ($f) => $f['type'] === 'text');
        $content = $section->content;
        $content[$field] = 'ADDED_SECTION_HEADING';
        $this->put(route('cms.sections.update', $section), $this->payload($section, ['content' => $content]))->assertSessionHasNoErrors();
        $this->get('/services.html')->assertDontSee('ADDED_SECTION_HEADING');
        $this->show($section);
        $this->get('/services.html')->assertSee('ADDED_SECTION_HEADING');

        // Manage its repeatable items.
        $group = $section->groups[0]['key'];
        $this->post(route('cms.items.store', $section), ['group_key' => $group])->assertSessionHasNoErrors();
        $item = $this->newestItem($section);
        $text = collect($item->schema)->search(fn ($f) => $f['type'] === 'text');
        $itemContent = $item->content;
        $itemContent[$text] = 'ADDED_SECTION_ITEM';
        $this->put(route('cms.items.update', $item), $this->payload($item, ['content' => $itemContent]))->assertSessionHasNoErrors();
        $this->get('/services.html')->assertSee('ADDED_SECTION_ITEM');
        $this->delete(route('cms.items.destroy', $item))->assertRedirect();
        $this->get('/services.html')->assertDontSee('ADDED_SECTION_ITEM');
    }

    public function test_incompatible_or_unknown_designs_are_rejected(): void
    {
        $page = $this->page('about');
        $count = CmsSection::count();
        foreach ([$this->typeFor('website.generated.index-s4'), 'not-a-real-type', 'website.page'] as $type) {
            $this->from(route('cms.pages.edit', $page))->post(route('cms.sections.store', $page), ['type' => $type])->assertSessionHasErrors('type');
        }
        $this->assertSame($count, CmsSection::count());
    }

    public function test_templates_and_unsafe_markup_cannot_be_supplied_by_an_editor(): void
    {
        $section = $this->section('about-s1');
        $template = $section->template;
        $this->put(route('cms.sections.update', $section), $this->payload($section, ['template' => 'website.page', 'key' => 'forged', 'schema' => ['x' => ['type' => 'text']]]))->assertSessionHasNoErrors();
        $fresh = $section->fresh();
        $this->assertSame($template, $fresh->template);
        $this->assertSame('about-s1', $fresh->key);
        $this->assertSame($section->schema, $fresh->schema);

        $field = collect($section->schema)->search(fn ($f) => $f['type'] === 'text');
        $content = $section->content;
        $content[$field] = '<img src=x onerror=alert(1)>@php echo 1; @endphp {{ 7*7 }}';
        $this->put(route('cms.sections.update', $section), $this->payload($section, ['content' => $content]))->assertSessionHasNoErrors();
        $html = $this->publicHtml('/about.html');
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('{{ 7*7 }}', html_entity_decode($html));
    }

    public function test_every_compatible_design_can_be_added_without_duplicate_ids_or_errors(): void
    {
        foreach (['index', 'about', 'services', 'articles', 'contact', 'service-exam', 'article-1'] as $key) {
            $page = $this->page($key);
            foreach ($this->catalog()->availableFor($page) as $type) {
                $this->post(route('cms.sections.store', $page), ['type' => $type['key']])->assertSessionHasNoErrors();
                $this->show($this->newestSection($page));
            }
            $html = $this->publicHtml('/'.$page->slug);
            $this->assertNoDuplicateIds($html, $key);
            $this->get(route('cms.pages.edit', $page))->assertOk();
        }
    }

    public function test_duplicating_copies_items_without_duplicate_ids_and_places_the_copy_next_to_the_source(): void
    {
        $source = $this->section('services-s2');
        $page = $source->page;
        $following = $page->sections()->where('position', '>', $source->position)->pluck('position', 'id');
        $this->post(route('cms.sections.duplicate', $source))->assertSessionHasNoErrors()->assertRedirect();
        $copy = $this->newestSection($page);

        $this->assertSame($source->position + 1, $copy->position);
        $this->assertFalse($copy->is_visible);
        $this->assertSame($source->items()->count(), $copy->items()->count());
        $this->assertTrue($copy->items()->whereNotNull('import_key')->doesntExist());
        $this->assertFalse($copy->items()->where('is_managed', true)->exists());
        foreach ($following as $id => $position) {
            $this->assertSame($position + 1, CmsSection::find($id)->position);
        }
        $this->show($copy);
        $this->assertNoDuplicateIds($this->publicHtml('/services.html'), 'duplicated services grid');
        // Automatic cards keep targeting the original listing.
        $this->assertSame($source->id, $this->cardFor($this->page('service-exam'), 'services-s2')->section_id);
    }

    public function test_sections_that_cannot_repeat_safely_are_not_duplicated(): void
    {
        $count = CmsSection::count();
        foreach (['contact-s2', 'contact-s3', 'index-s5', 'index-s7', 'index-s4', 'articles-s2', 'global-header', 'global-footer'] as $key) {
            $section = $this->section($key);
            $this->assertFalse($this->catalog()->canDuplicate($section), $key);
            $this->from(route('cms.sections.edit', $section))->post(route('cms.sections.duplicate', $section))->assertSessionHasErrors('section');
        }
        $this->assertSame($count, CmsSection::count());
    }

    public function test_sections_can_be_reordered_and_the_public_order_follows(): void
    {
        $page = $this->page('services');
        $order = fn () => $page->sections()->get()->pluck('key')->all();
        $original = $order();
        $this->assertSame('services-s2', $original[1]);

        $this->post(route('cms.sections.move', $this->section('services-s2')), ['direction' => 'down'])->assertRedirect();
        $this->assertSame(['services-s1', 'services-s3', 'services-s2'], array_slice($order(), 0, 3));
        $html = $this->publicHtml('/services.html');
        $this->assertLessThan(strpos($html, 'services-grid'), strpos($html, $this->section('services-s3')->content['f1'] ?? 'services-grid'));

        $this->post(route('cms.sections.move', $this->section('services-s2')), ['direction' => 'up'])->assertRedirect();
        $this->assertSame($original, $order());

        // Moving past either end changes nothing.
        $this->post(route('cms.sections.move', $this->section($original[0])), ['direction' => 'up'])->assertRedirect();
        $this->post(route('cms.sections.move', $this->section(end($original))), ['direction' => 'down'])->assertRedirect();
        $this->assertSame($original, $order());
        $this->post(route('cms.sections.move', $this->section('services-s2')), ['direction' => 'sideways'])->assertSessionHasErrors('direction');
        $this->post(route('cms.sections.move', $this->section('global-header')), ['direction' => 'up'])->assertStatus(422);
    }

    public function test_sections_can_be_hidden_and_shown(): void
    {
        $section = $this->section('services-s4');
        $field = collect($section->schema)->search(fn ($f) => $f['type'] === 'text');
        $text = trim($section->content[$field]);
        $this->get('/services.html')->assertSee($text);
        $this->patch(route('cms.sections.visibility', $section), ['is_visible' => 0])->assertRedirect();
        $this->get('/services.html')->assertDontSee($text);
        $this->show($section);
        $this->get('/services.html')->assertSee($text);
    }

    public function test_removed_section_goes_to_trash_and_restores_exactly(): void
    {
        $section = $this->section('services-s4');
        $field = collect($section->schema)->search(fn ($f) => $f['type'] === 'text');
        $text = trim($section->content[$field]);
        $row = $section->fresh()->getAttributes();
        $items = CmsItem::where('section_id', $section->id)->orderBy('id')->get()->map->getAttributes()->all();
        $this->assertNotEmpty($items);

        $this->delete(route('cms.sections.destroy', $section))->assertRedirect(route('cms.pages.edit', $section->page_id));
        $this->assertDatabaseMissing('cms_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('cms_items', ['section_id' => $section->id]);
        $this->get('/services.html')->assertOk()->assertDontSee($text);

        // A later import does not silently undo the removal.
        app(WebsiteImporter::class)->import();
        $this->assertDatabaseMissing('cms_sections', ['key' => 'services-s4']);
        $this->get(route('cms.trash'))->assertOk()->assertSee('Restore');

        $this->post(route('cms.trash.restore', CmsTrashEntry::where('type', 'section')->firstOrFail()))->assertSessionHasNoErrors();
        $this->assertSame($row, $this->section('services-s4')->getAttributes());
        $this->assertSame($items, CmsItem::where('section_id', $section->id)->orderBy('id')->get()->map->getAttributes()->all());
        $this->get('/services.html')->assertSee($text);
        $this->assertSame(0, CmsTrashEntry::where('type', 'section')->count());

        // After restoring, imports treat it as a normal record again.
        app(WebsiteImporter::class)->import();
        $this->assertSame(1, CmsSection::where('key', 'services-s4')->count());
    }

    public function test_nested_items_restore_with_their_parent(): void
    {
        $footer = $this->section('global-footer');
        $parent = $footer->items()->get()->first(fn ($item) => $footer->items()->where('parent_id', $item->id)->exists());
        $children = $footer->items()->where('parent_id', $parent->id)->orderBy('id')->get()->map->getAttributes()->all();
        $this->delete(route('cms.items.destroy', $parent))->assertRedirect();
        $this->assertDatabaseMissing('cms_items', ['id' => $parent->id]);
        $this->post(route('cms.trash.restore', CmsTrashEntry::where('type', 'item')->firstOrFail()))->assertSessionHasNoErrors();
        $this->assertSame($children, $footer->items()->where('parent_id', $parent->id)->orderBy('id')->get()->map->getAttributes()->all());
        $this->get('/')->assertOk();
    }

    public function test_item_cannot_be_restored_without_its_section(): void
    {
        $section = $this->section('services-s4');
        $this->delete(route('cms.items.destroy', $section->items()->firstOrFail()))->assertRedirect();
        $item = CmsTrashEntry::where('type', 'item')->firstOrFail();
        $this->delete(route('cms.sections.destroy', $section))->assertRedirect();
        $this->from(route('cms.trash'))->post(route('cms.trash.restore', $item))->assertSessionHasErrors('trash');
        $this->assertDatabaseHas('cms_trash', ['id' => $item->id]);
    }

    public function test_shared_sections_cannot_be_removed_but_can_be_hidden(): void
    {
        foreach (['global-header', 'global-footer', 'global-site', 'global-messages', 'global-rotator'] as $key) {
            $this->delete(route('cms.sections.destroy', $this->section($key)))->assertStatus(422);
            $this->assertDatabaseHas('cms_sections', ['key' => $key]);
        }
    }

    public function test_trash_permissions_keep_permanent_deletion_with_owners(): void
    {
        $this->delete(route('cms.sections.destroy', $this->section('services-s4')))->assertRedirect();
        $entry = CmsTrashEntry::where('type', 'section')->firstOrFail();
        $editor = $this->adminWithRole('cms');
        $this->actingAs($editor, 'admin')->get(route('cms.trash'))->assertOk()->assertDontSee('Delete permanently');
        $this->delete(route('cms.trash.destroy', $entry))->assertForbidden();
        $this->assertDatabaseHas('cms_trash', ['id' => $entry->id]);
        $this->asOwner()->delete(route('cms.trash.destroy', $entry))->assertRedirect();
        $this->assertDatabaseMissing('cms_trash', ['id' => $entry->id]);
        // Removing it for good still respects the decision on later imports.
        app(WebsiteImporter::class)->import();
        $this->assertDatabaseMissing('cms_sections', ['key' => 'services-s4']);
    }

    public function test_anonymous_visitors_cannot_manage_sections(): void
    {
        $section = $this->section('services-s4');
        $this->post('/admin/logout');
        $this->post(route('cms.sections.duplicate', $section))->assertRedirect('/admin/login');
        $this->delete(route('cms.sections.destroy', $section))->assertRedirect('/admin/login');
        $this->post(route('cms.sections.move', $section), ['direction' => 'up'])->assertRedirect('/admin/login');
        $this->patch(route('cms.sections.visibility', $section), ['is_visible' => 0])->assertRedirect('/admin/login');
        $this->post(route('cms.sections.store', $section->page_id), ['type' => 'x'])->assertRedirect('/admin/login');
        $this->assertDatabaseHas('cms_sections', ['id' => $section->id, 'is_visible' => 1]);
    }
}
