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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->double('discount_percentage')->nullable(false)->comment('The percentage of discount to sale or a product in a sale.');
            $table->double('discount_amount')->nullable(false)->comment('The amount to be discount to a sale or a product within a sale.');
            $table->text('remark')->nullable(true)->comment('The remark of the discount');
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
        Schema::dropIfExists('sale_discounts');
    }
};
