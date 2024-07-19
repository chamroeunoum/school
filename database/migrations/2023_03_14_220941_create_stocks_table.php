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
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id')->nullable(true)->default(0)->comment('The id of the store');
            $table->integer('product_id')->nullable(false)->comment('The id of the product');
            $table->integer('attribute_variant_id')->nullable(true)->comment('The id of the attribute and variants.');
            $table->string('upc',50)->nullable(false)->comment('Stock Keeping Unit Code for the product. it is 12 digits.');
            $table->string('vendor_sku',50)->nullable(false)->comment('Stock Keeping Unit Code for the product of vendor company. it is 12 digits.');
            $table->text('location')->nullable(true)->comment('The name or id of the location of the stock located.');
            $table->unsignedInteger('pid')->comment('This is the stock id which is the parent of this record.');
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
        Schema::dropIfExists('stocks');
    }
};
