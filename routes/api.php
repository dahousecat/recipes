<?php

Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');
Route::post('register', 'AuthController@register');

Route::post('recipes/{id}/vote', 'RecipeController@vote');

Route::resource('recipes', 'RecipeController');
Route::resource('ingredients', 'IngredientController');

Route::resource('units', 'UnitController');
Route::resource('attribute-types', 'AttributeTypeController');

Route::get('ingredients/search/{str}', 'IngredientController@search');
Route::get('ingredient/{id}/attributes', 'IngredientController@attributes');
Route::get('ingredient/{id}/recipes', 'IngredientController@recipes');

Route::get('ndb/search/{term}', 'NdbController@search');
Route::get('ndb/view/{ndbno}', 'NdbController@view');

//Route::get('dri', 'DriScraperController@test');
