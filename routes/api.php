<?php

use Illuminate\Http\Request;

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

Route::get('/match/search', 'MatchController@search');
Route::post('/match/participants', 'MatchController@participants');
Route::get('/match/dictionnaries', 'MatchController@dictionnaries');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
