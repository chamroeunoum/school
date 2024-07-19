<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'people_id' => 1,
                'lastname' => 'OUM',
                'firstname' => 'Chamroeun',
                'phone' => '012391848',
                'username' => 'chamroeunoum',
                'email' => 'chamroeunoum@gmail.com',
                'role' => '0',
                'email_verified_at' => '',
                'password' => '$2y$10$CVoIxH5yVIqtouliys8UKev1sorcGvTfkqNe6JmGzaZ7CqRTo12bC',
                'avatar_url' => NULL,
                'avatar' => NULL,
                'activation_token' => NULL,
                'forgot_password_token' => NULL,
                'remember_token' => NULL,
                'login_count' => NULL,
                'last_login' => NULL,
                'last_logout' => NULL,
                'active' => '1',
                'ip' => NULL,
                'authenip' => NULL,
                'mac_address' => NULL,
                'image' => NULL,
                'created_at' => '2023-03-01 03:45:30',
                'updated_at' => '2023-03-01 06:49:14',
                'deleted_at' => NULL,
                'api_token' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'people_id' => 0,
                'lastname' => 'គង់ចាន់',
                'firstname' => 'ពុទ្ធិរាជ',
                'phone' => '010517515',
                'username' => 'kongchanputhireach@gmail.com',
                'email' => 'kongchanputhireach@gmail.com',
                'role' => '0',
                'email_verified_at' => '',
                'password' => '$2y$10$7UyEbuxF5W1SYz18W3LDNOM58woSkEhWpaZuGgfDe06gIK0Ls8/om',
                'avatar_url' => NULL,
                'avatar' => NULL,
                'activation_token' => '',
                'forgot_password_token' => NULL,
                'remember_token' => NULL,
                'login_count' => NULL,
                'last_login' => NULL,
                'last_logout' => NULL,
                'active' => '1',
                'ip' => NULL,
                'authenip' => NULL,
                'mac_address' => NULL,
                'image' => NULL,
                'created_at' => '2023-03-01 03:54:17',
                'updated_at' => '2023-03-01 03:56:12',
                'deleted_at' => NULL,
                'api_token' => NULL,
            ),
        ));
        
        
    }
}