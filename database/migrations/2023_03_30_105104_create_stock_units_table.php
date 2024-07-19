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
        Schema::create('stock_units', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_id')->nullable(false)->comment('The stock id');
            $table->integer('unit_id')->nullable(false)->comment('The unit id');
            $table->integer('quantity')->nullable(false)->comment('The quantity');
            $table->double('unit_price',8,4)->nullable(false)->default(0)->comment('The unit price of the product');
            $table->double('sale_price',8,4)->nullable(false)->default(0)->comment('The sale price of the product');
            $table->string('sku',50)->nullable(false)->comment('The sku represent of an item in new unit.');
            $table->text('images')->nullable(false)->comment('The image of the product.');
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
        Schema::dropIfExists('stock_units');
    }
};
