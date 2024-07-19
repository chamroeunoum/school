<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TransactionTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('transaction_types')->delete();
        
        \DB::table('transaction_types')->insert(array (
            0 => 
            array (
                'active' => 0,
                'color' => 'text-green-500',
                'created_at' => '2019-04-10 21:40:57',
                'deleted_at' => NULL,
                'desc' => 'ដាក់ប្រាប់សន្សំ',
                'icon' => 'ios-cash',
                'id' => 1,
                'name' => 'ដាក់ប្រាក់',
                'updated_at' => '2019-04-10 21:40:59',
            ),
            1 => 
            array (
                'active' => 0,
                'color' => 'text-red-700',
                'created_at' => '2019-04-10 21:41:15',
                'deleted_at' => NULL,
                'desc' => 'ដកប្រាក់ពីសន្សំ',
                'icon' => 'ios-cash',
                'id' => 2,
                'name' => 'ដកប្រាក់',
                'updated_at' => '2019-04-10 21:41:17',
            ),
            2 => 
            array (
                'active' => 0,
                'color' => 'text-orange-500',
                'created_at' => '2019-05-12 23:16:32',
                'deleted_at' => NULL,
                'desc' => 'ខ្ចីប្រាក់',
                'icon' => 'ios-cash',
                'id' => 3,
                'name' => 'ខ្ចីប្រាក់',
                'updated_at' => '2019-05-12 23:16:33',
            ),
            3 => 
            array (
                'active' => 0,
                'color' => 'text-blue-500',
                'created_at' => '2019-05-12 23:16:55',
                'deleted_at' => NULL,
                'desc' => 'សងប្រាក់ត្រឡប់វិញ នៃកម្ចី',
                'icon' => 'ios-cash',
                'id' => 4,
                'name' => 'សងប្រាក់',
                'updated_at' => '2019-05-12 23:16:56',
            ),
            4 => 
            array (
                'active' => 0,
                'color' => 'text-red-700',
                'created_at' => '2019-05-12 23:53:22',
                'deleted_at' => NULL,
                'desc' => 'ថែមប្រាក់កម្ចី',
                'icon' => 'ios-cash',
                'id' => 5,
                'name' => 'ថែមកម្ចី',
                'updated_at' => '2019-05-12 23:53:23',
            ),
            5 => 
            array (
                'active' => 0,
                'color' => 'text-orange-500',
                'created_at' => '2019-05-26 23:05:08',
                'deleted_at' => NULL,
                'desc' => 'ការប្រាក់ នៃសន្សំ',
                'icon' => 'ios-cash',
                'id' => 6,
                'name' => 'ការប្រាក់សន្សំ',
                'updated_at' => '2019-05-26 23:05:09',
            ),
            6 => 
            array (
                'active' => 0,
                'color' => 'text-blue-300',
                'created_at' => '2019-05-26 23:05:34',
                'deleted_at' => NULL,
                'desc' => 'ការប្រាក់ នៃកម្ចី',
                'icon' => 'ios-cash',
                'id' => 7,
                'name' => 'ការប្រាក់កម្ចី',
                'updated_at' => '2019-05-26 23:05:35',
            ),
            7 => 
            array (
                'active' => 0,
                'color' => 'text-red-700',
                'created_at' => '2020-08-09 11:07:37',
                'deleted_at' => NULL,
                'desc' => 'ការប្រាក់ជំពាក់ នៃកម្ចី',
                'icon' => 'ios-cash',
                'id' => 8,
                'name' => 'ជំពាក់ការប្រាក់កម្ចី',
                'updated_at' => '2020-08-09 11:07:39',
            ),
            8 => 
            array (
                'active' => 0,
                'color' => 'text-blue-700',
                'created_at' => '2022-09-28 22:26:13',
                'deleted_at' => NULL,
                'desc' => 'ការប្រាក់សន្សំមានកាលកំណត់',
                'icon' => 'ios-cash',
                'id' => 9,
                'name' => 'ការប្រាក់សន្សំមានកាលកំណត់',
                'updated_at' => '2022-09-28 22:26:16',
            ),
        ));
        
        
    }
}