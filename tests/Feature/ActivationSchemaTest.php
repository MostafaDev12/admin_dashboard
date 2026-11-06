<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ActivationSchemaTest extends CmsFeatureTestCase
{
    public function test_sqlite_enforces_the_page_placement_foreign_key(): void
    {
        $keys = collect(DB::select('PRAGMA foreign_key_list(cms_items)'));
        $key = $keys->first(fn ($key) => $key->from === 'target_page_id');
        $this->assertNotNull($key);
        $this->assertSame('cms_pages', $key->table);
        $this->assertSame('SET NULL', $key->on_delete);

        $this->expectException(QueryException::class);
        DB::table('cms_items')->where('id', DB::table('cms_items')->value('id'))->update(['target_page_id' => 999999999]);
    }
}
