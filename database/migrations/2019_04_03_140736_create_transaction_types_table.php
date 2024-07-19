<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',191)->nullable(false)->comment('name of transaction');
            $table->string('desc',191)->nullable(true)->comment('description of transaction');
            $table->string('icon',191)->nullable(false)->comment('icon of transaction');
            $table->string('color',191)->nullable(false)->comment('color of transaction');
            $table->boolean('active')->default(true)->comment('check whether this record is using or not');
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
        Schema::dropIfExists('transaction_types');
    }
}
