<?php

namespace App\Services;

use App\Models\CmsMedia;
use App\Models\CmsSection;
use Illuminate\Support\Facades\DB;

/**
 * Brings existing content up to the current structure without changing what it displays.
 * Every step is idempotent, so reruns neither duplicate records nor overwrite editors' work.
 */
class CmsUpgrade
{
    public function __construct(private CmsMessages $messages, private CmsMenu $menu, private CmsPlacements $placements) {}

    public function run(): array
    {
        return DB::transaction(function () {
            $this->messages->ensureSection();
            $report = $this->menu->migrate();
            $report += $this->placements->backfill();
            $report['map_fields'] = $this->addMapDestination();
            $report['pruned_media_records'] = $this->pruneNonMediaRecords();

            return $report;
        });
    }

    /** Optional link on the map block. Empty by default, which leaves its markup unchanged. */
    private function addMapDestination(): int
    {
        $count = 0;
        foreach (CmsSection::where('template', 'website.generated.contact-s3')->get() as $section) {
            if (isset($section->schema['map_url'])) {
                continue;
            }
            $section->update([
                'schema' => $section->schema + ['map_url' => ['type' => 'link', 'label' => 'Map destination (optional)', 'optional' => true]],
                'content' => $section->content + ['map_url' => ''],
            ]);
            $count++;
        }

        return $count;
    }

    /** The source's "put the clinic image here" note was registered as a media file by mistake. */
    private function pruneNonMediaRecords(): int
    {
        $pruned = 0;
        foreach (CmsMedia::where('is_original', true)->where('mime', 'like', 'text/%')->get() as $record) {
            if (! is_file(public_path(rawurldecode($record->path)))) {
                $record->delete();
                $pruned++;
            }
        }

        return $pruned;
    }
}
