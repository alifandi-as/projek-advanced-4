<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Controllers\Api\ApiOrderController;
use App\Http\Controllers\Api\ApiReviewController;
use App\Http\Controllers\Api\ApiProductController;
/*
Route::get('/users', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/
Route::prefix('/users')
->controller(ApiUserController::class)
->group(function(){
    //Route::post('/', 'index');
    //Route::post('/show', 'show');
    //Route::post('/profile', 'profile')->middleware('auth:api');
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::middleware('auth:api')->post('/profile', 'profile');
    Route::middleware('auth:api')->put('/edit-profile', 'edit_profile');
    Route::middleware('auth:api')->put('/edit-password', 'edit_password');
    Route::middleware('auth:api')->post('/logout', 'logout');
    Route::middleware('auth:api')->delete('delete', 'delete');
});

Route::prefix('/products')
->controller(ApiProductController::class)
->group(function(){
    Route::get('/', 'index');
    Route::get('/index_detailed', 'index_detailed');
    Route::get('/show/{id}', 'show');
    Route::get('/search', 'search');
    Route::middleware('auth:api')->post('/favorite', 'show_favorites');
    Route::middleware('auth:api')->post('/favorite/{id}', 'favorite');
    Route::middleware('auth:api')->delete('/unfavorite/{id}', 'unfavorite');
});

Route::prefix('/reviews')
->controller(ApiReviewController::class)
->group(function(){
    Route::get('/', 'index');
    Route::get('/show_product/{product_id}', 'show_product');
    Route::middleware('auth:api')->post('/create/{product_id}', 'create');
    Route::middleware('auth:api')->put('/update/{id}', 'update');
    Route::middleware('auth:api')->delete('/delete/{id}', 'remove');
});

Route::prefix("/orders")
->controller(ApiOrderController::class)
->group(function(){
    Route::middleware('auth:api')->post("/add", "add");
    Route::middleware('auth:api')->post("/", "index_user");
    Route::middleware('auth:api')->post("/select/{id}", "select");
    Route::middleware('auth:api')->post("/user", "select_user");
    Route::middleware('auth:api')->post("/multi/add", "multi_add");
    Route::middleware('auth:api')->put("/edit/{id}", "edit");
    Route::middleware('auth:api')->put("/multi/edit", "multi_edit");
    Route::middleware('auth:api')->delete("/delete/{id}", "delete");
    Route::middleware('auth:api')->delete("/multi/delete", "multi_delete");
    Route::middleware('auth:api')->post("/buy", "buy");
    Route::middleware('auth:api')->post("/direct-buy/{product_id}", "direct_buy");
    Route::middleware('auth:api')->post("/history", "order_history");
});