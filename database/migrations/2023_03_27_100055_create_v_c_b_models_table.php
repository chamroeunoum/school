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
        Schema::create('v_c_b_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table',191)->nullable(false)->comment('Name of the table');
            $table->string('class',191)->nullable(false)->comment('The path to the class name of this table');
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
        Schema::dropIfExists('v_c_b_models');
    }
};
