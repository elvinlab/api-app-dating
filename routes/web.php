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

//Rutas del controlador usuario
Route::post('/api/user/register', 'UserController@register');
Route::post('/api/user/login', 'UserController@login');
Route::put('/api/user/update', 'UserController@update');
//Aqui va algo
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');


//Rutas del controlador Comercio
Route::post('/api/commerce/register', 'CommerceController@register');
Route::post('/api/commerce/login', 'CommerceController@login');
Route::put('/api/commerce/update', 'CommerceController@update');
//Aqui va algo
Route::get('/api/commerce/avatar/{filename}', 'CommerceController@getImage');
Route::get('/api/commerce/detail/{id}', 'CommerceController@detail');
