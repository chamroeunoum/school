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
        Schema::create('v_c_b_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',191)->nullable(false)->comment('Name of the field');
            $table->string('type',191)->nullable(false)->comment('The description of the type of the field');
            $table->integer('model_id')->nullable(true)->comment('The id of the model which own this field');
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
        Schema::dropIfExists('v_c_b_fields');
    }
};
