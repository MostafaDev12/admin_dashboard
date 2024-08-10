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
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
           
         
            $table->text('subject')->nullable();
            $table->text('message')->nullable();
          
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
        Schema::dropIfExists('contacts');
    }
};
