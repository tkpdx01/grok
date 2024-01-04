<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::namespace('App\Http\Controllers\Web')->group(function (){
    Route::prefix('google')->group(function (){
        Route::get('googleauth', 'GoogleAuthController@index');
        Route::post('googleauth', 'GoogleAuthController@doadd');
    });
});
