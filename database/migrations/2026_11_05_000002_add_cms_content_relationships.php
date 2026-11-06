<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Additive schema for page relationships, editorial recovery and import deletion intent.
 * Existing rows are not changed here; `website:upgrade` backfills them separately.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $t) {
            // Listing identity shared by every card that points to this page.
            $t->json('listing')->nullable();
            $t->boolean('auto_listing')->default(false);
        });
        Schema::table('cms_sections', function (Blueprint $t) {
            // Fixed link fields that lead to a page: field => page id.
            $t->json('page_links')->nullable();
        });
        // Laravel's SQLite ALTER TABLE compiler omits table-level foreign keys.
        // An inline nullable reference is supported without rebuilding existing rows.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE "cms_items" ADD COLUMN "target_page_id" INTEGER REFERENCES "cms_pages" ("id") ON DELETE SET NULL');
        }
        Schema::table('cms_items', function (Blueprint $t) {
            if (DB::getDriverName() !== 'sqlite') {
                $t->foreignId('target_page_id')->nullable()->constrained('cms_pages')->nullOnDelete();
            }
            $t->boolean('is_managed')->default(false);
            // Roles this placement customises instead of inheriting from its page.
            $t->json('overrides')->nullable();
            // Title of a removed page, kept so editors can see why the placement is hidden.
            $t->string('detached_from')->nullable();
        });
        Schema::table('cms_redirects', function (Blueprint $t) {
            // Renames are permanent; a page in the trash can still come back.
            $t->unsignedSmallInteger('status')->default(301);
        });
        Schema::create('cms_trash', function (Blueprint $t) {
            $t->id();
            $t->string('type')->index();
            $t->string('label');
            $t->longText('payload');
            $t->unsignedBigInteger('deleted_by')->nullable();
            $t->timestamps();
        });
        Schema::create('cms_tombstones', function (Blueprint $t) {
            $t->id();
            $t->string('kind');
            $t->string('key');
            $t->timestamp('created_at')->nullable();
            $t->unique(['kind', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_tombstones');
        Schema::dropIfExists('cms_trash');
        Schema::table('cms_items', function (Blueprint $t) {
            if (DB::getDriverName() !== 'sqlite') {
                $t->dropForeign(['target_page_id']);
            }
        });
        foreach (['target_page_id', 'is_managed', 'overrides', 'detached_from'] as $column) {
            Schema::table('cms_items', fn (Blueprint $t) => $t->dropColumn($column));
        }
        Schema::table('cms_redirects', fn (Blueprint $t) => $t->dropColumn('status'));
        Schema::table('cms_sections', fn (Blueprint $t) => $t->dropColumn('page_links'));
        foreach (['listing', 'auto_listing'] as $column) {
            Schema::table('cms_pages', fn (Blueprint $t) => $t->dropColumn($column));
        }
    }
};
