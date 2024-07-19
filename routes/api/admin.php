<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ClientController;
use App\Http\Controllers\Api\Admin\PeopleController;
use App\Http\Controllers\Api\Admin\LoanController;
use App\Http\Controllers\Api\Admin\LoanTransactionController;
use App\Http\Controllers\Api\Admin\TagController;
use App\Http\Controllers\Api\Admin\VCBModelController;

use App\Http\Controllers\Api\Admin\Product\ProductController;
use App\Http\Controllers\Api\Admin\Product\AttributeController;
use App\Http\Controllers\Api\Admin\Product\VariantController;
use App\Http\Controllers\Api\Admin\Product\AttributeVariantController;
use App\Http\Controllers\Api\Admin\Product\UnitController;
use App\Http\Controllers\Api\Admin\Product\UnitConventionController;
use App\Http\Controllers\Api\Admin\Product\StockController;

use App\Http\Controllers\Api\Admin\StoreController;

Route::group([
    'prefix' => 'admin' ,
    'api'
  ],function(){

    /** SIGNING SECTION */
    Route::group([
        'prefix' => 'authentication'
    ], function () {
        Route::post('login', [AuthController::class,'login']);
        Route::group([
        'middleware' => 'auth:api'
        ], function() {
            Route::post('logout', [AuthController::class,'logout']);
            Route::get('user', [AuthController::class,'user']);
            Route::put('password/change',[UserController::class,'changePasswordOfAuthenticatedUser']);
        });
    });

    /** USER/ACCOUNT SECTION */
    Route::group([
        'prefix' => 'users' ,
        'namespace' => 'Api' ,
        'middleware' => 'auth:api'
        ], function() {
            Route::get('',[UserController::class,'index']);
            Route::post('create',[UserController::class,'store']);
            Route::put('update',[UserController::class,'update']);
            Route::get('{id}/read',[UserController::class,'read']);
            Route::delete('{id}/delete',[UserController::class,'destroy']);
            Route::put('{id}/activate',[UserController::class,'active']);
            Route::put('password/change',[UserController::class,'passwordChange']);
            Route::get('compact',[ClientController::class,'compact']);
            /**
             * Check the unique user information
             */
            Route::get('username/exist',[UserController::class,'checkUsername']);
            Route::get('phone/exist',[UserController::class,'checkPhone']);
            Route::get('email/exist',[UserController::class,'checkEmail']);

    });

    /** USER/ACCOUNT SECTION */
    Route::group([
        'prefix' => 'clients' ,
        'namespace' => 'Api' ,
        'middleware' => 'auth:api'
        ], function() {
            Route::get('',[ClientController::class,'index']);
            Route::post('create',[ClientController::class,'store']);
            Route::put('update',[ClientController::class,'update']);
            Route::get('{id}/read',[ClientController::class,'read']);
            Route::delete('{id}/delete',[ClientController::class,'destroy']);
            Route::put('{id}/activate',[ClientController::class,'active']);
            Route::put('password/change',[ClientController::class,'passwordChange']);
            Route::get('compact',[ClientController::class,'compact']);
            /**
             * Check the unique user information
             */
            Route::get('username/exist',[ClientController::class,'checkUsername']);
            Route::get('phone/exist',[ClientController::class,'checkPhone']);
            Route::get('email/exist',[ClientController::class,'checkEmail']);
    });

    /** PROFILE SECTION */
    // Route::group([
    //     'prefix' => 'profile',
    //     'namespace' => 'Api' ,
    //     'middleware' => 'auth:api'
    // ], function() {
    //     Route::get('/getAuthUser',
    //                 'ProfileController@getAuthUser');
    //     Route::put('/updateAuthUser',
    //                 'ProfileController@updateAuthUser');
    //     Route::put('/updateAuthUserPassword',
    //                 'ProfileController@updateAuthUserPassword');
    //     Route::post('/picture/upload','ProfileController@upload');
    // });

    /** LOAN SECTION */
    Route::group([
        'prefix' => 'loans',
    ], function () {

        Route::get('', [LoanController::class,'index']);
        Route::post('create', [LoanController::class,'create']);
        Route::put('update', [LoanController::class,'update']);
        Route::get('{id}/read', [LoanController::class,'read']);
        Route::delete('{id}/delete', [LoanController::class,'delete']);

        Route::post('repayment', [LoanController::class,'repayment']);
        Route::post('addmoreloan', [LoanController::class,'addmoreloan']);
        Route::get('transactions', [LoanController::class,'getTransactions']);
        Route::get('schedule', [LoanController::class,'getSchedule']);

        Route::post('import', [LoanController::class,'importAccounts']);
        Route::post('importtransactions', [LoanController::class,'importTransactions']);

        /**
         * Transactions
         */
        Route::group([
            'prefix' => 'transactions'
        ], function () {
            Route::put('update', [LoanTransactionController::class,'update']);
            Route::get('{id}/read', [LoanTransactionController::class,'read']);
            Route::delete('{id}/delete', [LoanTransactionController::class,'delete']);
        });

        /**
         * Dashboard 
         */
        // Total balances, principles, interests, owned interests of all loans
        Route::get('total_b_p_i_w', function(Request $request){
            return response()->json([
                'balance' => \App\Models\Loan::getTotalBalances() ,
                // Total principle of all loans
                'principle' => \App\Models\Loan::getTotalPrinciples() ,
                // total interest of all loans
                'interest' => \App\Models\Loan::getTotalInterests() ,
                // Total owned interest of all loans
                'owned_interest' => \App\Models\Loan::getTotalOwnedInterests() ,
                'ok' => true ,
                'message' => 'តួលេខសរុបនៃទឹកប្រាក់កម្ចី។'
            ], 200 );
        });
        
    });
     /** LOAN SECTION */
    Route::group([
        'prefix' => 'people',
    ], function () {
        Route::get('', [PeopleController::class,'index']);
        Route::post('create', [PeopleController::class,'create']);
        Route::put('update', [PeopleController::class,'update']);
        Route::get('{id}/read', [PeopleController::class,'read']);
        Route::delete('{id}/delete', [PeopleController::class,'delete']);
        Route::get('compact', [PeopleController::class,'compact']);
    });

    /** ATTRIBUTE SECTION */
    Route::group([
        'prefix' => 'attributes',
    ], function () {
        Route::get('', [AttributeController::class,'index']);
        Route::post('create', [AttributeController::class,'create']);
        Route::put('update', [AttributeController::class,'update']);
        Route::get('{id}/read', [AttributeController::class,'read']);
        Route::delete('{id}/delete', [AttributeController::class,'delete']);
        Route::get('compact', [AttributeController::class,'compact']);
    /**
         * Check the unique user information
         */
        Route::get('name/exist',[AttributeController::class,'checkName']);
    });

    /** VARIANT SECTION */
    Route::group([
        'prefix' => 'variants',
    ], function () {
        Route::get('', [VariantController::class,'index']);
        Route::post('create', [VariantController::class,'create']);
        Route::put('update', [VariantController::class,'update']);
        Route::get('{id}/read', [VariantController::class,'read']);
        Route::delete('{id}/delete', [VariantController::class,'delete']);
        Route::get('compact', [VariantController::class,'compact']);
    });

    /** ATTRINUTE VARIANT SECTION */
    Route::group([
        'prefix' => 'attributevariants',
    ], function () {
        Route::get('', [AttributeVariantController::class,'index']);
        Route::post('create', [AttributeVariantController::class,'create']);
        Route::put('update', [AttributeVariantController::class,'update']);
        Route::get('{id}/read', [AttributeVariantController::class,'read']);
        Route::delete('{id}/delete', [AttributeVariantController::class,'delete']);
        Route::get('compact', [AttributeVariantController::class,'compact']);
    });

    /** PRODUCT SECTION */
    Route::group([
        'prefix' => 'products',
    ], function () {
        Route::get('', [ProductController::class,'index']);
        Route::post('create', [ProductController::class,'create']);
        Route::put('update', [ProductController::class,'update']);
        Route::get('{id}/read', [ProductController::class,'read']);
        Route::delete('{id}/delete', [ProductController::class,'delete']);
        Route::get('compact', [ProductController::class,'compact']);

        Route::post('upload',[ProductController::class,'upload']);
        Route::put('featurepicture', [ProductController::class,'featurePicture']);
        Route::delete('removepicture', [ProductController::class,'removePicture']);
    });

    /** STOCK SECTION */
    Route::group([
        'middleware' => 'auth:api' ,
        'prefix' => 'stocks',
    ], function () {
        Route::get('', [StockController::class,'index']);
        Route::post('create', [StockController::class,'create']);
        Route::put('update', [StockController::class,'update']);
        Route::get('{id}/read', [StockController::class,'read']);
        Route::delete('{id}/delete', [StockController::class,'delete']);
        Route::get('compact', [StockController::class,'compact']);

        Route::post('stockin', [StockController::class,'stockIn']);
        Route::post('stockout', [StockController::class,'stockTransfer']);
        Route::get('transactions', [StockController::class,'transactions']);
        Route::post('convention',[StockController::class,'convention']);

        Route::get('units',[StockController::class,'stockunits']);
        Route::put('breakdownunit', [StockController::class,'breakdownUnit']);
        Route::put('buildupunit', [StockController::class,'buildupUnit']);
        Route::put('defeat', [StockController::class,'stockDefeat']);
        Route::put('lost', [StockController::class,'stockLost']);
        Route::put('transfer', [StockController::class,'stockTransfer']);
    });

    /** UNIT SECTION */
    Route::group([
        'prefix' => 'units',
    ], function () {
        Route::get('', [UnitController::class,'index']);
        Route::post('create', [UnitController::class,'create']);
        Route::put('update', [UnitController::class,'update']);
        Route::get('{id}/read', [UnitController::class,'read']);
        Route::delete('{id}/delete', [UnitController::class,'delete']);
        Route::get('compact', [UnitController::class,'compact']);
    });

    /** UNIT CONVENTION SECTION */
    Route::group([
        'prefix' => 'unitconventions',
    ], function () {
        Route::get('', [UnitConventionController::class,'index']);
        Route::post('create', [UnitConventionController::class,'create']);
        Route::put('update', [UnitConventionController::class,'update']);
        Route::get('{id}/read', [UnitConventionController::class,'read']);
        Route::delete('{id}/delete', [UnitConventionController::class,'delete']);
        Route::get('compact', [UnitConventionController::class,'compact']);
    });

    /** TAG SECTION */
    Route::group([
        'prefix' => 'tags',
    ], function () {
        Route::get('', [TagController::class,'index']);
        Route::post('create', [TagController::class,'create']);
        Route::put('update', [TagController::class,'update']);
        Route::get('{id}/read', [TagController::class,'read']);
        Route::delete('{id}/delete', [TagController::class,'delete']);
        Route::get('compact', [TagController::class,'compact']);
    });

    /** MODEL SECTION */
    Route::group([
        'prefix' => 'models',
    ], function () {
        Route::get('', [VCBModelController::class,'index']);
        Route::post('create', [VCBModelController::class,'create']);
        Route::put('update', [VCBModelController::class,'update']);
        Route::get('{id}/read', [VCBModelController::class,'read']);
        Route::delete('{id}/delete', [VCBModelController::class,'delete']);
        Route::get('compact', [VCBModelController::class,'compact']);
    });

    /** STORE SECTION */
    Route::group([
        'prefix' => 'stores' ,
        'namespace' => 'Api' ,
        'middleware' => 'auth:api'
        ], function() {
            Route::get('',[StoreController::class,'index']);
            Route::post('create',[StoreController::class,'store']);
            Route::put('update',[StoreController::class,'update']);
            Route::get('{id}/read',[StoreController::class,'read']);
            Route::delete('{id}/delete',[StoreController::class,'destroy']);
            Route::put('{id}/activate',[StoreController::class,'active']);
            Route::get('compact',[StoreController::class,'compact']);

            Route::post('upload',[StoreController::class,'upload']);
            Route::put('featurepicture', [StoreController::class,'featurePicture']);
            Route::delete('removepicture', [StoreController::class,'removePicture']);

            /**
             * Store Product
             */
            Route::get('products', [ProductController::class,'storeProducts']);
            
    });

});
