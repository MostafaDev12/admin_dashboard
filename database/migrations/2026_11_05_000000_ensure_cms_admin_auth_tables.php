<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->text('section')->nullable();
            });
        }
        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('email')->unique();
                $t->string('password');
                $t->unsignedBigInteger('role_id')->default(0);
                $t->string('phone')->nullable();
                $t->string('photo')->nullable();
                $t->rememberToken();
                $t->timestamps();
            });
        }
    }

    // These may be existing authentication tables; never drop them on rollback.
    public function down(): void {}
};
