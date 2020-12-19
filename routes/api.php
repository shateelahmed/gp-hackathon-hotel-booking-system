<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// All V1 routes
Route::prefix('v1')->group(function () {
    // All user routes
    Route::prefix('users')->group(function () {
        Route::post('/register', 'Api\V1\AuthController@register');
        Route::post('/login', 'Api\V1\AuthController@login');

        // All auth:api middleware routes
        Route::middleware('auth:api')->group(function () {
            Route::get('/user', 'Api\V1\AuthController@getAuthenticatedUser');
        });
    });

    // All customer routes
    Route::prefix('customers')->group(function () {
        
        // All auth:api middleware routes
        Route::middleware('auth:api')->group(function () {
            Route::get('/', 'Api\V1\CustomerController@index');
            Route::post('/create', 'Api\V1\CustomerController@create');
            Route::get('/{id}', 'Api\V1\CustomerController@get');
            Route::post('/{id}/update', 'Api\V1\CustomerController@update');
            Route::post('/{id}/delete', 'Api\V1\CustomerController@delete');
            Route::get('/{id}/bookings', 'Api\V1\CustomerController@bookings');
            Route::get('/{id}/payments', 'Api\V1\CustomerController@payments');
        });
    });

    // All room routes
    Route::prefix('rooms')->group(function () {
        
        // All auth:api middleware routes
        Route::middleware('auth:api')->group(function () {
            Route::get('/', 'Api\V1\RoomController@index');
            Route::post('/create', 'Api\V1\RoomController@create');
            Route::get('/{id}', 'Api\V1\RoomController@get');
            Route::post('/{id}/update', 'Api\V1\RoomController@update');
            Route::post('/{id}/delete', 'Api\V1\RoomController@delete');
            Route::get('/{id}/bookings', 'Api\V1\RoomController@bookings');
        });
    });

    // All booking routes
    Route::prefix('bookings')->group(function () {
        
        // All auth:api middleware routes
        Route::middleware('auth:api')->group(function () {
            Route::get('/', 'Api\V1\BookingController@index');
            Route::post('/create', 'Api\V1\BookingController@create');
            Route::get('/{id}', 'Api\V1\BookingController@get');
            Route::post('/{id}/update', 'Api\V1\BookingController@update');
            Route::post('/{id}/delete', 'Api\V1\BookingController@delete');
            Route::post('/{id}/checkIn', 'Api\V1\BookingController@checkIn');
            Route::post('/{id}/checkOut', 'Api\V1\BookingController@checkOut');
            Route::post('/{id}/makePayment', 'Api\V1\BookingController@makePayment');
        });
    });
});

