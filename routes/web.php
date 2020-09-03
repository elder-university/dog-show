<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get ('/', 'DogController@index') -> name ('index');

Route::get ('/dog/{dog}/', 'DogController@show');
Route::delete ('/dog/{dog}/', 'DogController@destroy');

Route::get ('/dog/', 'DogController@create');
Route::post ('/dog/', 'DogController@store');

Route::get ('/dog/{dog}/edit', 'DogController@edit');
Route::patch ('/dog/{dog}/', 'DogController@update');
