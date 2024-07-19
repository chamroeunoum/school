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
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->integer('sale_id')->nullable(false)->comment('The id of the sale / receipt');
            $table->integer('stock_unit_id')->nullable(false)->comment('The id of the product in stock');
            $table->integer('discount_id')->default(0)->comment('The id of the discount on this product');
            $table->double('discount',8,4)->default(0)->comment('The amount of discount of this product');
            $table->integer('quantity')->default(0)->comment('The quanaity of the product be sold');
            $table->double('unit_price',8,4)->default(0)->comment('The unit price of the product be sold');
            $table->double('amount',8,4)->default(0)->comment('The amount of the product be sold');
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
        Schema::dropIfExists('sale_details');
    }
};
