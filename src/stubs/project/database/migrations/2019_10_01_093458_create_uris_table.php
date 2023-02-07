<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uris', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('site_id');
            $table->string('language', 2);
            $table->unsignedBigInteger('uriable_id');
            $table->string('uriable_type');
            $table->unsignedBigInteger('pointable_id')->nullable();
            $table->string('pointable_type')->nullable();
            $table->unsignedTinyInteger('is_link')->default(0);
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'language', 'uriable_id', 'uriable_type'], 'site_language_uriable_owner');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uris');
    }
};
