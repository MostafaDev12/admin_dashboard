<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagesettings', function (Blueprint $table) {
             
              
            $table->string('portfolio_title_ar')->nullable();
            $table->string('portfolio_title_en')->nullable();
            $table->string('portfolio_title_fr')->nullable();
           
         
            $table->string('portfolio_details_ar')->nullable();
            $table->string('portfolio_details_en')->nullable();
            $table->string('portfolio_details_fr')->nullable();

            $table->string('portfolio_photo')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagesettings', function (Blueprint $table) {
            $table->dropColumn(['portfolio_title_ar','portfolio_title_en','portfolio_title_fr','portfolio_details_ar','portfolio_details_en','portfolio_details_fr','portfolio_photo']);
             
        });
    }
};
