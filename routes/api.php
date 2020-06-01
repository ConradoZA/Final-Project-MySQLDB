<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('users')->group(function () {
        Route::post('register', 'UserController@register');
        Route::post('login', 'UserController@login');
        Route::post('recover-mail', 'UserController@sendRecoverPasswordEmail');

        Route::prefix('password')->group(function () {
            Route::post('forgotten', 'PasswordResetController@create');
            Route::post('reset', 'PasswordResetController@reset');
        });

        Route::middleware('auth:api')->group(function () {
            Route::get('', 'UserController@getProfile');
            Route::get('all', 'UserController@getAll');
            Route::get('logout', 'UserController@logout');
            Route::put('update', 'UserController@updateUser');
            Route::delete('delete', 'UserController@deleteUser');
            Route::post('upload', 'UserController@uploadImage');
            Route::get('confirm-mail', 'UserController@sendConfirmEmail');
            Route::post('mail-confirmed', 'UserController@mailConfirmed');
        });
    });
});
