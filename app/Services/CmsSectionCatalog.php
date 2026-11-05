<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The section types an editor may add: only the original trusted templates, offered only where
 * their stylesheet and scripts are already loaded so they cannot render unstyled or inert.
 */
class CmsSectionCatalog
{
    /** Scripts bind these once per page through querySelector, so a second copy would be inert. */
    private const SINGLE_INSTANCE_HOOKS = ['data-compare', 'data-range', 'data-home-article', 'data-article-search', 'data-article-filter', 'data-articles-grid'];

    private ?Collection $types = null;

    public function types(): Collection
    {
        if ($this->types) {
            return $this->types;
        }
        $seed = json_decode(File::get(database_path('content/website.json')), true, 512, JSON_THROW_ON_ERROR);
        $pages = collect($seed['pages'])->keyBy('key');
        $types = collect();
        foreach ($seed['sections'] as $section) {
            if (! $section['page_key'] || ! $pages->has($section['page_key'])) {
                continue;
            }
            $markup = $this->markup($section['template']);
            $key = md5($this->normalize($markup));
            if ($types->has($key)) {
                $types[$key] = array_merge($types[$key], ['templates' => [...$types[$key]['templates'], $section['template']]]);

                continue;
            }
            $page = $pages[$section['page_key']];
            $types[$key] = [
                'key' => $key,
                'label' => \App\Support\CmsLabels::section($section['label']).' — '.trim(explode('|', $page['title'])[0]),
                'template' => $section['template'],
                'templates' => [$section['template']],
                'styles' => $this->assets($page['styles']),
                'scripts' => $this->assets($page['scripts']),
                'single_instance' => $this->isSingleInstance($markup),
                'seed' => $section,
            ];
        }

        return $this->types = $types;
    }

    public function typeOf(string $template): ?array
    {
        return $this->types()->first(fn ($type) => in_array($template, $type['templates'], true));
    }

    /** Types compatible with a page's loaded assets and not already at their one allowed instance. */
    public function availableFor(CmsPage $page): Collection
    {
        $styles = $this->assets($page->assets['styles'] ?? []);
        $scripts = $this->assets($page->assets['scripts'] ?? []);
        $existing = $page->sections()->pluck('template');

        return $this->types()->filter(function ($type) use ($styles, $scripts, $existing) {
            if (array_diff($type['styles'], $styles) || array_diff($type['scripts'], $scripts)) {
                return false;
            }

            return ! $type['single_instance'] || $existing->intersect($type['templates'])->isEmpty();
        })->sortBy('label')->values();
    }

    public function canDuplicate(CmsSection $section): bool
    {
        $type = $this->typeOf($section->template);

        return $section->page_id !== null && $type !== null && ! $type['single_instance'];
    }

    /** New sections start hidden: their starter copy is the original design's, not this page's. */
    public function add(CmsPage $page, string $typeKey): CmsSection
    {
        $type = $this->availableFor($page)->firstWhere('key', $typeKey);
        if (! $type) {
            throw new RuntimeException('This section type is not available for this page.');
        }
        $seed = $type['seed'];

        return DB::transaction(function () use ($page, $type, $seed) {
            $section = CmsSection::create([
                'page_id' => $page->id,
                'key' => 'section-'.Str::uuid(),
                'label' => trim($seed['label']),
                'template' => $type['template'],
                'position' => ((int) $page->sections()->reorder()->max('position')) + 1,
                'content' => $seed['fields'],
                'schema' => $seed['schema'],
                'groups' => $seed['groups'],
                'is_visible' => false,
            ]);
            $created = [];
            $pending = $seed['items'];
            while ($pending) {
                $progress = false;
                foreach ($pending as $index => $item) {
                    if ($item['parent_import_id'] && ! isset($created[$item['parent_import_id']])) {
                        continue;
                    }
                    $created[$item['import_id']] = CmsItem::create([
                        'section_id' => $section->id,
                        'parent_id' => $item['parent_import_id'] ? $created[$item['parent_import_id']] : null,
                        'group_key' => $item['group_key'],
                        'label' => $item['label'],
                        'position' => $item['position'],
                        'content' => $item['fields'],
                        'schema' => $item['schema'],
                    ])->id;
                    unset($pending[$index]);
                    $progress = true;
                }
                if (! $progress) {
                    throw new RuntimeException('Invalid nested content in this section type.');
                }
            }
            app(CmsPlacements::class)->backfill();

            return $section;
        });
    }

    public function duplicate(CmsSection $source): CmsSection
    {
        if (! $this->canDuplicate($source)) {
            throw new RuntimeException('This section can appear only once on a page.');
        }

        return DB::transaction(function () use ($source) {
            // Open a slot directly after the source so the copy appears beside it.
            CmsSection::where('page_id', $source->page_id)->where('position', '>', $source->position)->increment('position');
            $copy = $source->replicate();
            $copy->key = 'section-'.Str::uuid();
            $copy->label = Str::limit($source->label, 180, '').' (copy)';
            $copy->position = $source->position + 1;
            $copy->is_visible = false;
            $copy->save();
            $this->cloneItems($source->load('items'), $copy);

            return $copy;
        });
    }

    public function cloneItems(CmsSection $source, CmsSection $target, ?int $parent = null, ?int $newParent = null): void
    {
        foreach ($source->items->where('parent_id', $parent) as $item) {
            $copy = $item->replicate();
            $copy->import_key = null;
            $copy->section_id = $target->id;
            $copy->parent_id = $newParent;
            $copy->is_managed = false;
            $copy->save();
            $this->cloneItems($source, $target, $item->id, $copy->id);
        }
    }

    private function markup(string $template): string
    {
        $file = view()->getFinder()->find($template);
        $source = File::get($file);

        return preg_replace_callback("~@include\('(website\.generated\.[a-z0-9-]+)'\)~i", fn ($m) => $this->markup($m[1]), $source);
    }

    private function normalize(string $markup): string
    {
        return preg_replace('~website\.generated\.[a-z0-9-]+~i', 'T', $markup);
    }

    private function isSingleInstance(string $markup): bool
    {
        if (preg_match('~\sid="[^"{]+"~', $markup)) {
            return true;
        }

        return Str::contains($markup, self::SINGLE_INSTANCE_HOOKS);
    }

    private function assets(array $urls): array
    {
        return array_values(array_map(fn ($url) => strtok($url, '?'), $urls));
    }
}
