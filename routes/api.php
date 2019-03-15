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
//用法和web.php一模一样，访问时多个api
Route::middleware('auth:api')->get('/user', 'Auth\LoginController@test');

Route::get('/report/balance', 'report\BalanceController@balanceSheet');
