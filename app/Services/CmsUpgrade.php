<?php

namespace App\Services;

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
            // Activation must retain even stale original media metadata. Content health
            // can report missing files; upgrading is not permission to remove records.
            $report['pruned_media_records'] = 0;

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
}
