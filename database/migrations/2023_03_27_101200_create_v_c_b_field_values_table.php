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
        Schema::create('v_c_b_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('record_id')->nullable(false)->comment("The id of the record of which model who own this field manage");
            $table->integer('field_id')->nullable(false)->comment("The id of the field");
            $table->text('value')->nullable(true)->comment('The valueof the field which define by field_id');
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
        Schema::dropIfExists('v_c_b_field_values');
    }
};
