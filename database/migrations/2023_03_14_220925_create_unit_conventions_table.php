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
        Schema::create('unit_conventions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('stock_id')->nullable(false)->comment('The id of the stock');
            $table->unsignedInteger('from_stock_unit_id')->nullable(false)->comment('The id of the unit');
            $table->unsignedInteger('to_stock_unit_id')->nullable(false)->comment('The id of the unit');
            $table->integer('gaps')->nullable(false)->comment('The number of times differentiate bunit_id and sunit_id');
            $table->unsignedInteger('pid')->default(0)->comment('The parent record of this unit convention');
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
        Schema::dropIfExists('unit_conventions');
    }
};
