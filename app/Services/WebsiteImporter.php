<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WebsiteImporter
{
    public function import(): array
    {
        $seed = json_decode(File::get(database_path('content/website.json')), true, 512, JSON_THROW_ON_ERROR);

        return DB::transaction(function () use ($seed) {
            $defaults = json_decode(File::get(database_path('content/globals.json')), true, 512, JSON_THROW_ON_ERROR);
            $schema = [];
            foreach ($defaults as $key => $value) {
                $schema[$key] = ['label' => ucfirst(str_replace('_', ' ', $key)), 'type' => in_array($key, ['logo', 'favicon']) ? 'image' : ($key === 'whatsapp_url' ? 'link' : 'text')];
            }
            CmsSection::firstOrCreate(['key' => 'global-site'], ['label' => 'Website identity & contact settings', 'template' => 'website.empty', 'content' => $defaults, 'schema' => $schema, 'groups' => []]);
            foreach ($seed['pages'] as $p) {
                CmsPage::firstOrCreate(['key' => $p['key']], collect($p)->only(['type', 'slug', 'title', 'description', 'body_page'])->all() + ['assets' => ['styles' => $p['styles'], 'scripts' => $p['scripts']]]);
            }
            foreach ($seed['sections'] as $s) {
                $section = CmsSection::firstOrCreate(['key' => $s['key']], ['page_id' => $s['page_key'] ? CmsPage::where('key', $s['page_key'])->value('id') : null, 'label' => $s['label'], 'template' => $s['template'], 'position' => $s['position'], 'content' => $s['fields'], 'schema' => $s['schema'], 'groups' => $s['groups']]);
                $map = [];
                $pending = $s['items'];
                $section->update(['groups' => $s['groups']]);
                // Nested items may precede their parents in the source compiler.
                while ($pending) {
                    $progress = false;
                    foreach ($pending as $k => $item) {
                        if ($item['parent_import_id'] && ! isset($map[$item['parent_import_id']])) {
                            continue;
                        }
                        $record = CmsItem::firstOrCreate(['import_key' => $s['key'].':'.$item['import_id']], ['section_id' => $section->id, 'parent_id' => $item['parent_import_id'] ? $map[$item['parent_import_id']] : null, 'group_key' => $item['group_key'], 'label' => $item['label'], 'position' => $item['position'], 'content' => $item['fields'], 'schema' => $item['schema']]);
                        // Add trusted structural attributes without replacing edited content.
                        $attributes = collect($item['fields'])->filter(fn ($value, $key) => str_starts_with($key, 'attributes_'))->all();
                        if (array_diff_key($attributes, $record->content)) {
                            $record->update(['content' => $record->content + $attributes, 'schema' => $record->schema + collect($item['schema'])->only(array_keys($attributes))->all()]);
                        }
                        $map[$item['import_id']] = $record->id;
                        unset($pending[$k]);
                        $progress = true;
                    }
                    if (! $progress) {
                        throw new \RuntimeException('Invalid nested content in '.$s['key']);
                    }
                }
            }
            $slideSchema = ['image' => ['type' => 'image', 'label' => 'Image'], 'title' => ['type' => 'text', 'label' => 'Title'], 'copy' => ['type' => 'text', 'label' => 'Description'], 'link' => ['type' => 'link', 'label' => 'Destination']];
            $rotator = CmsSection::firstOrCreate(['key' => 'global-rotator'], ['label' => 'Home article carousel', 'template' => 'website.empty', 'content' => [], 'schema' => [], 'groups' => []]);
            $rotator->update(['groups' => [['key' => 'slides', 'label' => 'Carousel slides', 'parent_group' => null, 'schema' => $slideSchema, 'defaults' => ['image' => '', 'title' => '', 'copy' => '', 'link' => '']]]]);
            foreach ($seed['rotator'] as $i => $item) {
                $item['image'] = '/website/'.$item['image'];
                $item['link'] = '/article-details.html?article='.($i + 1);
                CmsItem::firstOrCreate(['import_key' => 'rotator:'.$i], ['section_id' => $rotator->id, 'group_key' => 'slides', 'label' => $item['title'], 'position' => $i, 'content' => $item, 'schema' => ['image' => ['type' => 'image', 'label' => 'Image'], 'title' => ['type' => 'text', 'label' => 'Title'], 'copy' => ['type' => 'text', 'label' => 'Description'], 'link' => ['type' => 'link', 'label' => 'Destination']]]);
            }
            foreach (File::allFiles(public_path('website/assets/images')) as $file) {
                $relative = '/website/assets/images/'.str_replace('\\', '/', $file->getRelativePathname());
                CmsMedia::firstOrCreate(['path' => $relative], ['name' => $file->getFilename(), 'mime' => File::mimeType($file->getPathname()) ?: 'application/octet-stream', 'size' => $file->getSize(), 'is_original' => true]);
            }

            return ['pages' => CmsPage::count(), 'sections' => CmsSection::count(), 'items' => CmsItem::count()];
        });
    }
}
