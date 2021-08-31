<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUberTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uber_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 88);
            $table->string('display_name', 256)->nullable();
            $table->integer('parent')->default(0);
            $table->boolean('children')->default(false);
            $table->string('directory', 85)->nullable();
            $table->integer('count')->default(0);
            $table->longText('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uber_tags');
    }
}
