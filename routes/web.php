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
    phpinfo();
    // return view('welcome');
});


Route::get('/test', function () {
    return "hello";
    // return view('welcome');
});

Route::get('/dumpdb', function () {
    return view('dumpdb');
});