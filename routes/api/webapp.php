<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Webapp\AuthController;
use App\Http\Controllers\Api\Webapp\UserController;
use App\Http\Controllers\Api\Webapp\ProfileController;

Route::group([
  'prefix' => 'webapp' ,
  'api'
],function(){
  /** SIGNING SECTION */
  Route::group([
    'prefix' => 'authentication'
  ], function () {
    Route::post('login', [AuthController::class,'login']);
    Route::post('signup', [AuthController::class,'signup']);
    Route::put('signup/activate', [AuthController::class,'signupActivate']);
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
      Route::delete('',[UserController::class,'destroy']);
      Route::put('activate',[UserController::class,'active']);
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

  
  /** RESET PASSWORD */
  Route::put('password/forgot',[UserController::class,'forgotPassword']);
  Route::put('password/forgot/confirm',[UserController::class,'checkConfirmationCode']);
  Route::put('password/reset',[UserController::class,'passwordReset']);

});