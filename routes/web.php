<?php

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

Route::get('/', 'HomeController@root')->name('root');
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes(['verify' => 'false', 'reset' => 'false']);

Route::post('login', 'Auth\LoginController@userLogin');
Route::post('register', 'Auth\RegisterController@createClient');

Route::prefix('feedbacks')->group(function () {
    Route::get('create', 'FeedbackController@create')
        ->name('feedbacks.create');

    Route::post('', 'FeedbackController@store')
        ->name('feedbacks.store');
});

Route::prefix('requests')->group(function () {
    Route::get('', 'RequestController@index')
      ->name('requests.index');

    Route::delete('', 'RequestController@removeAll')
      ->name('requests.truncate');
});

Route::put('/manager/email', 'ManagerController@updateManagerEmail')
  ->middleware('auth')
  ->name('manager-email.update');
