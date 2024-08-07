<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pagesetting;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagesettings', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('about_title_ar')->nullable();
            $table->string('about_title_en')->nullable();
            $table->string('about_title_fr')->nullable();
           
         
            $table->string('about_details_ar')->nullable();
            $table->string('about_details_en')->nullable();
            $table->string('about_details_fr')->nullable();

            $table->string('about_photo')->nullable();
           
         
         
        });

        Pagesetting::create([
            'about_title_en' => '',
            'about_title_ar' => '',
            'about_title_fr' => '',
            
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagesettings');
    }
};
