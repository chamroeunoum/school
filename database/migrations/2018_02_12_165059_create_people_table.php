<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePeopleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('people', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('code',191)->nullable();
			$table->string('firstname', 50);
			$table->string('lastname', 191);
			$table->integer('gender')->nullable()->comment('0 is Female');
			$table->date('dob')->nullable();
			$table->string('mobile_phone', 50)->nullable();
			$table->string('office_phone', 50)->nullable();
			$table->string('email', 191)->nullable();
			$table->string('image', 191)->nullable();
			$table->string('marry_status', 191)->nullable();
			$table->string('father', 191)->nullable();
			$table->string('mother', 191)->nullable();
			$table->char('enlastname', 20)->nullable(true)->default('');
			$table->char('enfirstname', 40)->nullable(true)->default('');
			$table->text('pob')->nullable(true);
			$table->string('nid',191)->nullable(true);
			$table->string('nationality',191)->nullable(true);
			$table->string('nation',191)->nullable(true);
			$table->string('religion',191)->nullable(true);
			$table->text('contact_address')->nullable(true);
			$table->string('degree',191)->nullable(true);
			$table->string('skill',191)->nullable(true);
			$table->string('current_career',191)->nullable(true);
			$table->string('organization',191)->nullable(true);
			$table->string('type_id', 100)->nullable(true)->comment('The type of member');
			$table->string('member_since')->nullable(true)->comment('Specify the date of member register to the committee');
			$table->integer('active')->default(0);
			$table->string('services_description')->nullable(true)->comment('Description of the service(s) that member has been running.');
			$table->string('deleted_at', 191)->nullable();
			$table->string('created_by', 191)->nullable();
			$table->string('modified_by', 191)->nullable();
			$table->string('deleted_by', 191)->nullable();
			$table->timestamps();

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('people');
	}

}
