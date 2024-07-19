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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stock_id')->nullable(false)->comment('The stock id');
            $table->integer('stock_unit_id')->nullable(false)->comment('Id of stock which represent of the unique product with it features.');
            $table->integer('unit_id')->nullable(false)->comment('Id of unit which represent the masurement of the stock.');
            $table->integer('quantity')->nullable(false)->comment('The number of product take out or take in or defeat.');
            $table->integer('user_id')->nullable(false)->comment('The user that act for this transaction.');
            $table->integer('transaction_type_id')->nullable(false)->comment('The type of the stock transaction.');
            $table->double('unit_price',8,4)->nullable(true)->comment('The price of the product that buy in.');
            $table->integer('parent_stock_id')->default(0)->comment('Id of stock which take it out and put it back in with small unit. it is the parent of the new stock_id');
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
        Schema::dropIfExists('stock_transactions');
    }
};
