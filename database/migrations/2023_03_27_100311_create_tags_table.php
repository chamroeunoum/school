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
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',191)->nullable(false)->comment('The name of tag.');
            $table->text('description')->nullable(true)->comment('The description of tag.');
            $table->integer('pid')->nullable(false)->comment('The id of the parent tag');
            $table->integer('model_id')->nullable(true)->comment('The id of the model which own this tag');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
};
