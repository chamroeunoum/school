<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoanTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loan_transactions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('loan_id')->comment('the ID of the loan from table loans');
			$table->string('balance',100)->nullable(true)->comment('Current balance of the transaction');
			$table->string('transaction_id', 100)->default('0')->comment('the balance of loan');
			$table->string('amount', 100)->default('0')->comment('the new loan');
			$table->datetime('repayment_date')->nullable()->comment('the date of repayment cycle');
			$table->string('rate', 10)->default('0')->comment('the value of rate');
			// $table->string('repayback', 100)->default(0)->comment('The value of the amount to be returned');
			// $table->string('interest', 100)->default(0)->comment('The value of the interest');
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
		Schema::drop('loan_transactions');
	}

}
