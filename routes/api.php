<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;


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

Route::post('register', [UserController::class,'register']);
Route::post('login', [UserController::class,'authenticate']);

Route::group(['middleware' => ['jwtVerify']], function() {
	Route::get('logout', [UserController::class,'logout']);
	Route::post('product', [ProductController::class,'store']);	
	Route::get('products', [ProductController::class,'getProductList']);
	Route::get('product/{id}', [ProductController::class,'details']);
	Route::post('edit/{id}', [ProductController::class,'updateProduct']);
	Route::get('delete/{id}', [ProductController::class,'delete']);
});