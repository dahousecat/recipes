<?php

Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::post('register', 'AuthController@register');

Route::resource('recipes', 'RecipeController');
Route::resource('ingredients', 'IngredientController');
Route::resource('units', 'UnitController');
Route::resource('attribute-types', 'AttributeTypeController');

//Route::post('ingredients/search/{str}', function ($str) {
//
//})->where('name', '[A-Za-z]+');

Route::get('ingredients/search/{str}', 'IngredientController@search');
Route::get('ingredient/{id}/attributes', 'IngredientController@attributes');

Route::get('ndb/search/{term}', 'NdbController@search');
Route::get('ndb/view/{ndbno}', 'NdbController@view');

//Route::get('/search',['uses' => 'SearchController@getSearch','as' => 'search']);