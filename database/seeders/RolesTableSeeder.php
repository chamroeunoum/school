<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Super administrator',
                'guard_name' => 'superadmin',
                'tag' => 'core',
                'created_at' => '2023-02-13 14:33:52',
                'updated_at' => '2023-02-13 14:33:55',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Administrator',
                'guard_name' => 'admin',
                'tag' => 'core',
                'created_at' => '2023-02-13 14:34:12',
                'updated_at' => '2023-02-13 14:34:14',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Backend Member',
                'guard_name' => 'member',
                'tag' => 'core',
                'created_at' => '2023-02-13 14:34:48',
                'updated_at' => '2023-02-13 14:34:50',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Clent of Web Application',
                'guard_name' => 'web',
                'tag' => 'webapp',
                'created_at' => '2023-02-13 14:35:13',
                'updated_at' => '2023-02-13 14:35:15',
            ),
        ));
        
        
    }
}