<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('type')->default('page')->index();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('body_page');
            $t->json('assets');
            $t->json('seo')->nullable();
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
        });
        Schema::create('cms_sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('page_id')->nullable()->constrained('cms_pages')->cascadeOnDelete();
            $t->string('key')->unique();
            $t->string('label');
            $t->string('template');
            $t->json('content');
            $t->json('schema');
            $t->json('groups');
            $t->unsignedInteger('position')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
        });
        Schema::create('cms_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('section_id')->constrained('cms_sections')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('cms_items')->cascadeOnDelete();
            $t->string('import_key')->nullable()->unique();
            $t->string('group_key');
            $t->string('label');
            $t->json('content');
            $t->json('schema');
            $t->unsignedInteger('position')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
            $t->index(['section_id', 'group_key', 'position']);
        });
        Schema::create('cms_media', function (Blueprint $t) {
            $t->id();
            $t->string('path')->unique();
            $t->string('name');
            $t->string('mime');
            $t->unsignedBigInteger('size');
            $t->string('alt')->nullable();
            $t->boolean('is_original')->default(false);
            $t->timestamps();
        });
        Schema::create('cms_submissions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone');
            $t->string('email')->nullable();
            $t->json('details');
            $t->string('status')->default('new')->index();
            $t->timestamps();
        });
        Schema::create('cms_redirects', function (Blueprint $t) {
            $t->id();
            $t->string('from')->unique();
            $t->string('to');
        });
    }

    public function down(): void
    {
        foreach (['cms_redirects', 'cms_submissions', 'cms_media', 'cms_items', 'cms_sections', 'cms_pages'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
