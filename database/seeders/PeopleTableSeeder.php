<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PeopleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('people')->delete();
        
        \DB::table('people')->insert(array (
            0 => 
            array (
                'id' => 1,
                'code' => NULL,
                'firstname' => 'Chamroeun',
                'lastname' => 'OUM',
                'gender' => NULL,
                'dob' => NULL,
                'mobile_phone' => '012391848',
                'office_phone' => NULL,
                'email' => 'chamroeunoum@gmail.com',
                'nid' => NULL,
                'image' => NULL,
                'marry_status' => NULL,
                'father' => NULL,
                'mother' => NULL,
                'created_at' => '2023-03-01 06:49:14',
                'updated_at' => '2023-03-01 06:49:14',
                'deleted_at' => NULL,
                'created_by' => NULL,
                'modified_by' => NULL,
                'deleted_by' => NULL,
            ),
        ));
        
        
    }
}