<?php

use Illuminate\Http\Request;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\UsersController;


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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/recipes', 'RecipeController@store');
Route::get('/recipes', [RecipeController::class, 'index']);
Route::get('/display', [RecipeController::class, 'display']);
Route::get('/show/{id}', [RecipeController::class, 'show']);

Route::get('/edit/{id}', [RecipeController::class, 'edit']);

Route::post('/update/{id}', [RecipeController::class, 'update']);
Route::get('/recipes/{id}', [RecipeController::class, 'destroy']);

// USERS
Route::get('/users', [UsersController::class, 'index']);


Route::get('/cymer', function () {
    return view('cymer');
});

