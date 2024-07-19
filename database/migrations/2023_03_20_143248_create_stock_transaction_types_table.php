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
        Schema::create('stock_transaction_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',191)->nullable(false)->comment('the name of the transaction type.');
            $table->text('description')->nullable(true)->comment('The description of the stock transaction type.');
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
        Schema::dropIfExists('stock_transaction_types');
    }
};
