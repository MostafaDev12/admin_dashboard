<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Generalsetting;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->increments('id');
            
           
            $table->text('title_ar')->nullable();
            $table->text('title_en')->nullable();
            $table->text('title_fr')->nullable();
            
            $table->text('details_ar')->nullable();
            $table->text('details_en')->nullable();
            $table->text('details_fr')->nullable();
            
            $table->text('photo')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
         
        });
 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('models');
    }
};
