<?php

use Illuminate\Support\Facades\Route;

// Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

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
Route::post('/api/user/upload' ,'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');


//Rutas del controlador Comercio
Route::post('/api/commerce/register', 'CommerceController@register');
Route::post('/api/commerce/login', 'CommerceController@login');
Route::put('/api/commerce/update', 'CommerceController@update');
Route::post('/api/commerce/upload' ,'CommerceController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/commerce/avatar/{filename}', 'CommerceController@getImage');
Route::get('/api/commerce/detail/{id}', 'CommerceController@detail');


  // Rutas del controlador de Categoria
  Route::resource('/api/category', 'CategoryController');
    
  // Rutas del controlador de Categoria
    Route::resource('/api/promotion', 'PromotionController'); //CRUD
    Route::post('/api/promotion/upload/{id}', 'PromotionController@upload');
    Route::get('/api/promotion/getpromos/{id}', 'PromotionController@getPromotionsBycommerce'); //optener promos por medio de la llave foranea