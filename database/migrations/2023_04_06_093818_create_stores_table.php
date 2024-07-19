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
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',191)->nullable(false)->comment('Store name');
            $table->string('location_name',191)->nullable(true)->comment('Location name, ex: Toul Tok ...');
            $table->string('lat_long')->nullable(true)->comment("Latitute Longtitute of the location on map");
            $table->text('address')->nullable(true)->comment('The actual address of the location');
            $table->text('images')->nullable(true)->comment('The collection of image of the store');
            $table->string('phone',191)->nullable(true)->comment('The phone of the store');
            $table->integer('active')->default(0);
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
        Schema::dropIfExists('stores');
    }
};
