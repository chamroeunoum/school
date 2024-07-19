<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('people_id')->nullable()->default(0);
			$table->string('lastname', 191)->nullable();
			$table->string('firstname', 191)->nullable();
			$table->string('phone', 191)->nullable();
			$table->string('username', 100)->nullable();
			$table->string('email', 191);
			$table->string('role', 191)->default(0)->comment('1: admin,0: member');
			$table->string('email_verified_at',191)->default('')->comment('field of email verification');
			$table->string('password', 191);
			$table->string('avatar_url',191)->nullable(true);
			$table->string('avatar',191)->nullable(true);
			$table->string('activation_token',191)->nullable(true);
			$table->string('forgot_password_token',191)->nullable(true);
			$table->string('remember_token', 100)->nullable();
			$table->integer('login_count')->nullable();
			$table->dateTime('last_login')->nullable();
			$table->dateTime('last_logout')->nullable();
			$table->string('active', 191)->nullable()->comment('0 : Blocked , 1 : Active , 2 : Verifing , 4 : Activitated');
			$table->string('ip', 100)->nullable();
			$table->string('authenip', 100)->nullable()->comment('The IP that registed with system. if defined then only the defined ip can login');
			$table->string('mac_address', 191)->nullable()->comment('The MAC address that registed with system. if defined then only the defined MAC address can login');
			$table->string('image', 191)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->string('api_token', 191)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
