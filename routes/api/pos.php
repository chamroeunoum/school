<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Pos\AuthController;
use App\Http\Controllers\Api\Pos\UserController;
use App\Http\Controllers\Api\Pos\ProductController;
use App\Http\Controllers\Api\Pos\ProfileController;
use App\Http\Controllers\Api\Pos\SaleController;

Route::group([
  'prefix' => 'pos' ,
  'api'
],function(){
  /** SIGNING SECTION */
  Route::group([
    'prefix' => 'authentication'
  ], function () {
    Route::post('login', [AuthController::class,'login']);
    Route::group([
      'middleware' => 'auth'
    ], function() {
        Route::post('logout', [AuthController::class,'logout']);
        Route::get('user', [AuthController::class,'user']);
    });
  });

  /** USER/ACCOUNT SECTION */
  Route::group([
    'prefix' => 'users' ,
    'middleware' => 'auth:api'
    ], function() {
      /**
       * Api for cin
       */
      Route::get('',[UserController::class,'index']);
      Route::post('',[UserController::class,'index']);
      Route::put('',[UserController::class,'update']);
      Route::get('{id}',[UserController::class,'read']);
      Route::put('password/change',[UserController::class,'logout']);
      Route::post('upload',[UserController::class,'upload']);
  });

  Route::group([
    'prefix' => 'users/authenticated' ,
    'middleware' => 'auth:api'
    ], function() {
      /**
       * Api for profile
       */
          Route::get('',[ProfileController::class,'getAuthUser']);
          Route::put('',[ProfileController::class,'updateAuthUser']);
          Route::put('password',[ProfileController::class,'updateAuthUserPassword']);
          Route::post('picture/upload',[ProfileController::class,'upload']);
  });

  Route::group([
    'prefix' => 'products' ,
    'middleware' => 'auth:api'
    ], function() {
      /**
       * Api for profile
       */
          Route::get('',[ProductController::class,'index']);
  });

  Route::group([
    'prefix' => 'sales' ,
    'middleware' => 'auth:api'
    ], function() {
      Route::get('invoices_by_staff',[SaleController::class,'storeInvoiceByStaff']);
      Route::get('invoices_by_store',[SaleController::class,'storeInvoiceByStore']);
  });

  Route::group([
    'prefix' => 'shops' ,
    'middleware' => 'auth:api'
    ], function() {
      /**
       * Api for profile
       */
          Route::post('placeorders',[SaleController::class,'placeOrders']);
  });

});