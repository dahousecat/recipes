<?php

Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::post('register', 'AuthController@register');

Route::resource('recipes', 'RecipeController');
Route::resource('ingredients', 'IngredientController');
Route::resource('units', 'UnitController');

//Route::post('ingredients/search/{str}', function ($str) {
//
//})->where('name', '[A-Za-z]+');

Route::get('ingredients/search/{str}', 'IngredientController@search');

//Route::get('/search',['uses' => 'SearchController@getSearch','as' => 'search']);