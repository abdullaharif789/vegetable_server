<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\API\TemplateController; 
use App\Http\Controllers\API\RegisterController; 
use App\Http\Controllers\API\PartyController; 
use App\Http\Controllers\API\InventoryController; 
use App\Http\Controllers\API\ItemController; 
  
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
Route::post('register', [RegisterController::class, 'register'])->name('register');
Route::post('login', [RegisterController::class, 'login'])->name('login');
Route::post('adminlogin', [RegisterController::class, 'loginadmin'])->name('loginadmin');
Route::middleware('auth:api')->group( function () {
	Route::get('logout','App\Http\Controllers\API\RegisterController@logout');
});
/*Testing*/
Route::resource('parties', PartyController::class);
Route::resource('inventories', InventoryController::class);
Route::resource('items', ItemController::class);