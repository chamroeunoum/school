<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoansTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loans', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('code',100)->nullable(true)->comment("the code to identify the loan account");
			$table->integer('people_id')->comment('the id of member in table members');
			$table->string('balance', 100)->default(0.00)->comment('the amount of loan to client');
			$table->datetime('start_date')->comment('the start date of loan to be start to count');
			$table->string('duration',50)->default(0)->comment('the number of months that client loan');
			$table->string('rate',50)->comment('the id of identify the rate to be use with loan');
			$table->string('reference_member_id',50)->nullable(true)->comment('the id of member whom is the referer of the new member');
			$table->string('pdf',191)->nullable(true)->comment('the id of identify the rate to be use with loan');
			$table->integer('rate_id')->nullable(true)->comment('The forieng key of rate_types table !');
			$table->timestamps();
			$table->softDeletes()->nullable(true);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('loans');
	}

}
