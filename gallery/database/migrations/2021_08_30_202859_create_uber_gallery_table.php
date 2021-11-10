<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUberGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uber_gallery', function (Blueprint $table) {
            $table->id();
            $table->date('occurred');
            $table->string('img', 88)->unique();
            $table->string('thumb', 88);
            $table->string('type', 4);
            $table->text('text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uber_gallery');
    }
}
