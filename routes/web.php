<?php

use App\Http\Controllers\RecipeController;

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

Route::get('/users', function () {
    return view('users');
});

Route::get('/edit', function () {
    return view('edit');
});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/edit/{id}', [RecipeController::class, 'edit']);

// Route::get('/edit/{id}', [RecipeController::class, 'edit']);
// Route::get('/recipes', [RecipeController::class, 'index']);


