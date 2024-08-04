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
        Schema::create('generalsettings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('favicon')->nullable();
            $table->string('logo_ar')->nullable();
            $table->string('logo_en')->nullable();
            $table->string('logo_fr')->nullable();
            
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_fr')->nullable();
           
            $table->text('footer_ar')->nullable();
            $table->text('footer_en')->nullable();
            $table->text('footer_fr')->nullable();
            
            $table->text('phones')->nullable();
            $table->text('emails')->nullable();

            $table->text('addresses_ar')->nullable();
            $table->text('addresses_en')->nullable();
            $table->text('addresses_fr')->nullable();

            $table->text('map')->nullable();

            $table->text('contact_emails')->nullable();
            $table->text('admin_loader')->nullable();
            $table->text('talkto')->nullable();
            $table->text('drift')->nullable();
            $table->string('smtp_host')->nullable();
            $table->string('smtp_port')->nullable();
            $table->string('smtp_user')->nullable();
            $table->string('smtp_pass')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('is_verification_email')->nullable();
         
        });

        Generalsetting::create([
            'title_en' => 'CangrowOnline',
            'title_ar' => 'CangrowOnline',
            'title_fr' => 'CangrowOnline',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generalsettings');
    }
}
