<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CmsUrls
{
    public function enabled(): bool
    {
        return Schema::hasColumn('cms_pages', 'public_slug') && CmsPage::where('key', 'index')->whereNotNull('public_slug')->exists();
    }

    public function path(string $type, string $leaf): string
    {
        return (in_array($type, ['service', 'article'], true) ? $type.'s/' : '').$leaf;
    }

    public function validate(string $leaf, string $type, ?CmsPage $page = null): string
    {
        $path = $this->path($type, $leaf);
        $reserved = ['admin', 'api', 'storage', 'build', 'assets', 'website', 'login', 'logout', 'register', 'password', 'appointment', 'sitemap', 'robots', 'index'];
        $invalid = ! preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/D', $leaf) || strlen($leaf) > 150 || in_array($leaf, $reserved, true);
        if ($page?->key === 'index') {
            $invalid = $leaf !== '';
        }
        if ($invalid || CmsPage::where('slug', $path)->when($page, fn ($q) => $q->where('id', '!=', $page->id))->exists()
            || DB::table('cms_redirects')->where('from', $path)->exists()) {
            throw ValidationException::withMessages(['slug' => 'Choose a unique English slug using lowercase words separated by hyphens. This URL may be reserved or already redirecting.']);
        }
        foreach (DB::table('cms_trash')->where('type', 'page')->get() as $entry) {
            $payload = json_decode($entry->payload, true);
            if (($payload['page']['slug'] ?? null) === $path) {
                throw ValidationException::withMessages(['slug' => 'This URL belongs to a page in Trash. Restore or permanently remove that page first.']);
            }
        }

        return $path;
    }

    public function plan(): array
    {
        $mapping = [];
        $seen = [];
        $conflicts = [];
        foreach (CmsPage::orderBy('key')->get() as $page) {
            $leaf = $page->public_slug ?? config('cms_urls.'.$page->key);
            if ($leaf === null && preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/D', $page->slug)) {
                $leaf = $page->slug;
            }
            if ($leaf === null) {
                $conflicts[] = 'No reviewed English slug for '.$page->key;

                continue;
            }
            $to = $this->path($page->type, $leaf);
            if (isset($seen[$to])) {
                $conflicts[] = 'Duplicate destination '.$to;
            }
            $seen[$to] = $page->key;
            $mapping[] = ['key' => $page->key, 'title' => $page->title, 'from' => $page->slug, 'to' => $to, 'public_slug' => $leaf];
        }
        foreach ($mapping as $row) {
            $redirect = DB::table('cms_redirects')->where('from', $row['to'])->first();
            if ($redirect && $redirect->to !== $row['from'] && $redirect->to !== $row['to']) {
                $conflicts[] = 'Conflicting redirect '.$row['to'];
            }
            $oldRedirect = DB::table('cms_redirects')->where('from', $row['from'])->first();
            if ($oldRedirect && $oldRedirect->to !== $row['to']) {
                $conflicts[] = 'Legacy URL already redirects elsewhere: '.$row['from'];
            }
        }
        // Restorable pages need an explicit review rather than silently changing their snapshots.
        if (! $this->enabled() && DB::table('cms_trash')->where('type', 'page')->exists()) {
            $conflicts[] = 'Restore trashed pages before preparing the URL migration.';
        }
        $plan = ['mapping' => $mapping, 'conflicts' => $conflicts];
        $plan['hash'] = hash('sha256', json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $plan;
    }

    public function apply(string $hash): array
    {
        abort_unless(Schema::hasColumn('cms_pages', 'public_slug'), 422, 'Apply the additive public-slug schema migration first.');

        return DB::transaction(function () use ($hash) {
            $plan = $this->plan();
            abort_unless(hash_equals($plan['hash'], $hash) && ! $plan['conflicts'], 422, 'Plan changed or has conflicts. Review a fresh dry run.');
            $links = app(CmsPageLinks::class);
            $pages = CmsPage::all()->keyBy('slug');
            foreach ($plan['mapping'] as $row) {
                $page = $pages->get($row['from']);
                $page->slug = $row['to'];
                $page->public_slug = $row['public_slug'];
                if ($page->isDirty()) {
                    $page->save();
                }
                if ($row['from'] !== $row['to']) {
                    DB::table('cms_redirects')->where('to', $row['from'])->update(['to' => $row['to']]);
                    DB::table('cms_redirects')->updateOrInsert(['from' => $row['from']], ['to' => $row['to'], 'status' => 301]);
                }
            }
            DB::table('cms_redirects')->updateOrInsert(['from' => 'index.html'], ['to' => '', 'status' => 301]);
            foreach ([CmsSection::class, CmsItem::class] as $model) {
                foreach ($model::all() as $record) {
                    $content = $record->content;
                    foreach ($record->schema ?? [] as $field => $definition) {
                        if (($definition['type'] ?? '') !== 'link' || ! is_string($content[$field] ?? null)) {
                            continue;
                        }
                        $target = $links->pageFor($content[$field], $pages);
                        if ($target) {
                            $content[$field] = $links->follow($content[$field], $target);
                        }
                    }
                    if ($content !== $record->content) {
                        $record->update(['content' => $content]);
                    }
                    if ($record instanceof CmsSection) {
                        $groups = $record->groups ?? [];
                        foreach ($groups as &$group) {
                            $group['defaults'] = $links->canonicalize($group['defaults'] ?? [], $group['schema'] ?? []);
                        } unset($group);
                        if ($groups !== ($record->groups ?? [])) {
                            $record->update(['groups' => $groups]);
                        }
                    }
                }
            }

            return $plan;
        });
    }
}
