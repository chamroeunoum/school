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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description',255)->nullable(false)->comment('The description of the product.');
            $table->string('origin',199)->nullable(false)->comment('The origin of the product.');
            $table->text('images')->nullable(false)->comment('The image of the product.');
            $table->integer('tag_id')->nullable(true)->comment('The id of the tag.');
            $table->string('upc',199)->nullable(false)->comment('Universal Product Code for the company. It is 12 digits');
            $table->string('vendor_sku',199)->nullable(false)->comment('Universal Product Code for the vendor company. It is 12 digits');
            $table->string('vendor_upc',199)->nullable(false)->comment('Universal Product Code for the vendor company. It is 12 digits');
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
        Schema::dropIfExists('products');
    }
};
