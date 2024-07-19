<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_log', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->nullable();
			$table->string('content_type', 72)->nullable();
			$table->integer('content_id')->nullable();
			$table->string('action', 32)->nullable();
			$table->text('description')->nullable();
			$table->text('details', 65535)->nullable();
			$table->text('data', 65535)->nullable();
			$table->boolean('language_key')->default(0);
			$table->boolean('public')->default(0);
			$table->boolean('developer')->default(0);
			$table->string('ip_address', 64);
			$table->string('user_agent', 191);
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
		Schema::drop('activity_log');
	}

}
