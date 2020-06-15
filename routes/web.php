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

Route::get('/welcome', function () {
    return view('welcome');
});

//Rutas del controlador cliente
Route::post('/api/client/register', 'ClientController@register');
Route::post('/api/client/login', 'ClientController@login');
Route::put('/api/client/update', 'ClientController@update');
Route::post('/api/client/upload' ,'ClientController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/client/avatar/{filename}', 'ClientController@getImage');
Route::get('/api/client/detail/{id}', 'ClientController@detail')->middleware(ApiAuthMiddleware::class);

//Rutas del controlador Comercio
Route::post('/api/commerce/register', 'CommerceController@register');
Route::post('/api/commerce/login', 'CommerceController@login');
Route::put('/api/commerce/update', 'CommerceController@update');
Route::get('/api/getcommerces', 'CommerceController@getCommerces');
Route::get('/api/getcommerce/{id}', 'CommerceController@getCommerce');
Route::post('/api/commerce/upload' ,'CommerceController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/commerce/avatar/{filename}', 'CommerceController@getImage');
Route::get('/api/commerce/detail/{id}', 'CommerceController@detail')->middleware(ApiAuthMiddleware::class);

// Rutas del controlador de Categoria
Route::resource('/api/category', 'CategoryController');
    
// Rutas del controlador de promocion
Route::resource('/api/promotion', 'PromotionController'); //CRUD
Route::get('/api/promotion/getpromos/{id}', 'PromotionController@getPromotionsByCommerce'); //optener promos por medio de la llave foranea
Route::get('/api/promotion/getvalidpromotion/{date}', 'PromotionController@getValidPromotion');
Route::post('/api/promotion/upload' ,'PromotionController@upload');
Route::get('/api/promotion/image/{filename}', 'PromotionController@getImage');
   
// Rutas del controlador de Servicio
Route::resource('/api/service', 'ServiceController'); //CRUD
Route::get('/api/getservicecommerce/{id}', 'ServiceController@getServicesByCommerce');

// Rutas del controlador de Cita
Route::resource('/api/appointment', 'AppointmentController'); //CRUD
Route::get('/api/getappointmentscommercerecord/{id}', 'AppointmentController@getAppointmentsByCommerceRecord'); 
Route::put('/api/changestatus', 'AppointmentController@changeStatus');
Route::get('/api/getappointmentsclientrecord/{id}', 'AppointmentController@getAppointmentsByClientRecord');
Route::get('/api/getappointmentsclientconfirmed/{id}', 'AppointmentController@getAppointmentsByClientConfirmed');
Route::get('/api/getappointmentsclientcanceled/{id}', 'AppointmentController@getAppointmentsByClientCanceled');
Route::get('/api/getappointmentsclientpending/{id}', 'AppointmentController@getAppointmentsByClientPending');
Route::get('/api/getappointmentscommercepending/{id}', 'AppointmentController@getAppointmentsByCommercePending');
Route::get('/api/getappointmentscommercevalid/{date}', 'AppointmentController@getAppointmentsByCommerceValid');