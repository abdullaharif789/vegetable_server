<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\API\TemplateController; 
use App\Http\Controllers\API\RegisterController; 
use App\Http\Controllers\API\PartyController; 
use App\Http\Controllers\API\InventoryController; 
use App\Http\Controllers\API\ItemController; 
use App\Http\Controllers\API\OrderController; 
use App\Http\Controllers\API\InvoiceController; 
use App\Http\Controllers\API\CategoryController;
  
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
// Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::post('login', [RegisterController::class, 'login'])->name('login');
Route::post('validate', [RegisterController::class, 'validateToken'])->name('validate');
Route::post('adminlogin', [RegisterController::class, 'loginadmin'])->name('loginadmin');
Route::middleware('auth:api')->group( function () {
	Route::get('logout','App\Http\Controllers\API\RegisterController@logout');
});
/*Testing*/
Route::resource('parties', PartyController::class);
Route::resource('categories', CategoryController::class);
Route::resource('inventories', InventoryController::class);
Route::resource('items', ItemController::class);
Route::resource('invoices', InvoiceController::class);
Route::resource('orders', OrderController::class);
Route::get('reports', [OrderController::class, 'reports']);