<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Create role for the user
         */
        $super = \App\Models\Role::create(['name' => 'Super Administrator', 'guard_name' => 'api' , 'tag' => 'core_service']);
        $administrator = \App\Models\Role::create(['name' => 'Administrator', 'guard_name' => 'api' , 'tag' => 'core_service']);
        $backendMember = \App\Models\Role::create(['name' => 'Backend member', 'guard_name' => 'api' , 'tag' => 'core_service']);

        $storeOwner = \App\Models\Role::create(['name' => 'Store Owner', 'guard_name' => 'api' , 'tag' => 'store']);
        $storeOwner = \App\Models\Role::create(['name' => 'Store Manager', 'guard_name' => 'api' , 'tag' => 'store']);
        $storeStaff = \App\Models\Role::create(['name' => 'Store Staff', 'guard_name' => 'api' , 'tag' => 'store']);

        $client = \App\Models\Role::create(['name' => 'Client', 'guard_name' => 'api' , 'tag' => 'webapp']);

        /**
         * Permissions
         */
        $permissions = [] ;
        /**
         * Loan permissions for admin
         */
        $loanPermissions = [] ;
        $loanPermissions['list'] = \App\Models\Permission::create(['name' => 'list loans', 'guard_name' => 'api']);
        $loanPermissions['create'] = \App\Models\Permission::create(['name' => 'create loan', 'guard_name' => 'api']);
        $loanPermissions['update'] = \App\Models\Permission::create(['name' => 'update loan', 'guard_name' => 'api']);
        $loanPermissions['delete'] = \App\Models\Permission::create(['name' => 'delete loan', 'guard_name' => 'api']);
        $permissions['loan'] = $loanPermissions;
        /**
         * User permissions for admin
         */
        $userPermissions = [] ;
        $userPermissions['list'] = \App\Models\Permission::create(['name' => 'list users', 'guard_name' => 'api']);
        $userPermissions['create'] = \App\Models\Permission::create(['name' => 'create user', 'guard_name' => 'api']);
        $userPermissions['update'] = \App\Models\Permission::create(['name' => 'update user', 'guard_name' => 'api']);
        $userPermissions['delete'] = \App\Models\Permission::create(['name' => 'delete user', 'guard_name' => 'api']);
        $permissions['user'] = $userPermissions;
        /**
         * Client permissions for admin
         */
        $clientPermissions = [] ;
        $clientPermissions['list'] = \App\Models\Permission::create(['name' => 'list clients', 'guard_name' => 'api']);
        $clientPermissions['create'] = \App\Models\Permission::create(['name' => 'create clients', 'guard_name' => 'api']);
        $clientPermissions['update'] = \App\Models\Permission::create(['name' => 'update clients', 'guard_name' => 'api']);
        $clientPermissions['delete'] = \App\Models\Permission::create(['name' => 'delete clients', 'guard_name' => 'api']);
        $permissions['client'] = $clientPermissions;
        /**
         * Store permissions for admin
         */
        $storePermissions = [] ;
        $storePermissions['list'] = \App\Models\Permission::create(['name' => 'list stores', 'guard_name' => 'api']);
        $storePermissions['create'] = \App\Models\Permission::create(['name' => 'create stores', 'guard_name' => 'api']);
        $storePermissions['update'] = \App\Models\Permission::create(['name' => 'update stores', 'guard_name' => 'api']);
        $storePermissions['delete'] = \App\Models\Permission::create(['name' => 'delete stores', 'guard_name' => 'api']);
        $permissions['store'] = $storePermissions;
        /**
         * Product permissions for admin
         */
        $productPermissions = [] ;
        $productPermissions['list'] = \App\Models\Permission::create(['name' => 'list products', 'guard_name' => 'api']);
        $productPermissions['create'] = \App\Models\Permission::create(['name' => 'create products', 'guard_name' => 'api']);
        $productPermissions['update'] = \App\Models\Permission::create(['name' => 'update products', 'guard_name' => 'api']);
        $productPermissions['delete'] = \App\Models\Permission::create(['name' => 'delete products', 'guard_name' => 'api']);
        $permissions['product'] = $productPermissions;
        /**
         * Warehouse permissions for admin
         */
        $stockPermissions = [] ;
        $stockPermissions['list'] = \App\Models\Permission::create(['name' => 'list stocks', 'guard_name' => 'api']);
        $stockPermissions['create'] = \App\Models\Permission::create(['name' => 'create stocks', 'guard_name' => 'api']);
        $stockPermissions['update'] = \App\Models\Permission::create(['name' => 'update stocks', 'guard_name' => 'api']);
        $stockPermissions['delete'] = \App\Models\Permission::create(['name' => 'delete stocks', 'guard_name' => 'api']);
        $permissions['stock'] = $stockPermissions;
        
        /**
         * Create Super admin user for development purpose
         */
        $superUser = \App\Models\User::create([
            'firstname' => 'Super Administrator' ,
            'lastname' => 'Account' ,
            'email' => 'super@gmail.com' ,
            'active' => 1 ,
            'password' => bcrypt('1234567890+1') ,
            'phone' => '012345678' ,
            'username' => 'superadmin'
        ]);
        $superUserPeople = \App\Models\People::create([
            'firstname' => $superUser->firstname , 
            'lastname' => $superUser->lastname , 
            'gender' => 0 , // Male
            'dob' => \Carbon\Carbon::parse('1984-03-18 9:00') ,
            'mobile_phone' => $superUser->phone , 
            'email' => $superUser->email , 
            'member_since' => \Carbon\Carbon::today()->format('Y-m-d H:i:s')
        ]);
        $superUser->people_id = $superUserPeople->id ;
        $superUser->save();
        $superUserPeople->selfAssignCode();
        $superUser->assignRole( $super );
        /**
         * Create Admin user for development purpose
         */
        $adminUser = \App\Models\User::create([
            'firstname' => 'Administrator' ,
            'lastname' => 'Account' ,
            'email' => 'admin@gmail.com' ,
            'active' => 1 ,
            'password' => bcrypt('1234567890+1') ,
            'phone' => '012456789' ,
            'username' => 'admin'
        ]);
        $adminUserPeople = \App\Models\People::create([
            'firstname' => $adminUser->firstname , 
            'lastname' => $adminUser->lastname , 
            'gender' => 0 , // Male
            'dob' => \Carbon\Carbon::parse('1984-03-18 9:00') ,
            'mobile_phone' => $adminUser->phone , 
            'email' => $adminUser->email , 
            'member_since' => \Carbon\Carbon::today()->format('Y-m-d H:i:s')
        ]);
        $adminUser->people_id = $adminUserPeople->id ;
        $adminUser->save();
        $adminUserPeople->selfAssignCode();
        $adminUser->assignRole( $administrator );
        /**
         * Create Backend user for development purpose
         */
        $backendUser = \App\Models\User::create([
            'firstname' => 'Backend Member' ,
            'lastname' => 'Account' ,
            'email' => 'backend@gmail.com' ,
            'active' => 1 ,
            'password' => bcrypt('1234567890+1') ,
            'phone' => '012567890' ,
            'username' => 'backend'
        ]);
        $backendUserPeople = \App\Models\People::create([
            'firstname' => $backendUser->firstname , 
            'lastname' => $backendUser->lastname , 
            'gender' => 0 , // Male
            'dob' => \Carbon\Carbon::parse('1984-03-18 9:00') ,
            'mobile_phone' => $backendUser->phone , 
            'email' => $backendUser->email , 
            'member_since' => \Carbon\Carbon::today()->format('Y-m-d H:i:s')
        ]);
        $backendUser->people_id = $backendUserPeople->id ;
        $backendUser->save();
        $backendUserPeople->selfAssignCode();
        $backendUser->assignRole( $backendMember );

        /**
         * Create Store owner user for development purpose
         */
        $storeOwnerUser = \App\Models\User::create([
            'firstname' => 'Store Owner' ,
            'lastname' => 'Account' ,
            'email' => 'storeowner@gmail.com' ,
            'active' => 1 ,
            'password' => bcrypt('1234567890+1') ,
            'phone' => '011234567' ,
            'username' => 'storeowner'
        ]);
        $storeOwnerUserPeople = \App\Models\People::create([
            'firstname' => $storeOwnerUser->firstname , 
            'lastname' => $storeOwnerUser->lastname , 
            'gender' => 0 , // Male
            'dob' => \Carbon\Carbon::parse('1984-03-18 9:00') ,
            'mobile_phone' => $storeOwnerUser->phone , 
            'email' => $storeOwnerUser->email , 
            'member_since' => \Carbon\Carbon::today()->format('Y-m-d H:i:s')
        ]);
        $storeOwnerUser->people_id = $storeOwnerUserPeople->id ;
        $storeOwnerUser->save();
        $backendUserPeople->selfAssignCode();
        $storeOwnerUser->assignRole( $storeOwner );

        /**
         * Create Store staff user for development purpose
         */
        $storeStaffUser = \App\Models\User::create([
            'firstname' => 'Store staff' ,
            'lastname' => 'Account' ,
            'email' => 'storestaff@gmail.com' ,
            'active' => 1 ,
            'password' => bcrypt('1234567890+1') ,
            'phone' => '011345678' ,
            'username' => 'storestaff'
        ]);
        $storeStaffUserPeople = \App\Models\People::create([
            'firstname' => $storeStaffUser->firstname , 
            'lastname' => $storeStaffUser->lastname , 
            'gender' => 0 , // Male
            'dob' => \Carbon\Carbon::parse('1984-03-18 9:00') ,
            'mobile_phone' => $storeStaffUser->phone , 
            'email' => $storeStaffUser->email , 
            'member_since' => \Carbon\Carbon::today()->format('Y-m-d H:i:s')
        ]);
        $storeStaffUser->people_id = $storeStaffUserPeople->id ;
        $storeStaffUser->save();
        $storeStaffUserPeople->selfAssignCode();
        $storeStaffUser->assignRole( $storeStaff );

        /**
         * Generate Stock Transaction Type
         */
        foreach( [
            [
                'name' => 'stock_in' ,
                'description' => 'Put in product into stock.'
            ],
            [
                'name' => 'stock_out' ,
                'description' => 'Take out product from stock.'
            ],
            [
                'name' => 'stock_defeat' ,
                'description' => 'Put product in with defeat status'
            ],
            [
                'name' => 'stock_lost' ,
                'description' => 'Lost product in stock.'
            ],
            [
                'name' => 'stock_transfer_in' ,
                'description' => 'Transfer product from warehouse to another.'
            ],
            [
                'name' => 'stock_transfer_out' ,
                'description' => 'Transfer product from warehouse to another.'
            ],
            [
                'name' => 'stock_breakdown' ,
                'description' => 'Break stock with big unit into small unit.'
            ],
            [
                'name' => 'stock_buildup' ,
                'description' => 'Build up big unit of stock from small unit.'
            ]
        ] As $index => $values ) \App\Models\Stock\StockTransactionType::create( $values );

        // Uint
        foreach( [ 'កេស' , 'បាវ' , 'ប្រអប់' , 'ដប' , 'កំប៉ុង' , 'កញ្ចប់' , 'ថង់' , 'កែវ' , 'ដើម' , 'សន្លឹក' , 'ក្បាល' ] AS $index => $unit )  \App\Models\Stock\Unit::create([ 'name' => $unit ]);

        // $this->call(TransactionTypesTableSeeder::class);

        /**
         * Create 
         */
    }
}
