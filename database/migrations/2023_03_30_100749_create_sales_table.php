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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->nullable(false)->comment('The code of the invoice');
            $table->double('total',8,4)->default(0)->comment('The total amount of');
            $table->double('discount',8,4)->default(0)->comment('The amount of discount of this invoice.');
            $table->double('vat',8,4)->default(0)->comment('The amount of VAT.');
            $table->double('grand_total',8,4)->default(0)->comment('The grand total amount of');
            $table->integer('saler_id')->default(0)->commeit('The id of the saler.');
            $table->integer('client_id')->default(0)->comment('The id of client, if it is 0 then it is a guest.');
            $table->integer('payment_id')->default(0)->comment('The id of the payment, if it is 0 then it is cash.');
            $table->integer('discount_id')->default(0)->comment('The id of the discount, if it is 0 then it is 0 discount.');
            $table->integer('store_id')->defaulta(0)->comment('The id of the store');
            $table->string('table_number',191)->comment('The number of the table or any place that represent the invoice for pending the invoice.');
            $table->text('note')->comment('The note of the sale');
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
        Schema::dropIfExists('sales');
    }
};
