<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/


Route::group(['middleware' => ['web'], 'namespace' => 'Admin', 'prefix' => 'admin', 'as' => 'admin::'], function () {

    Route::get('organization',         ['uses' => 'OrganizationController@fetch']);
    Route::get('organization/create',  ['uses' => 'OrganizationController@createPage']);
    Route::post('organization/create', ['uses' => 'OrganizationController@create']);

    Route::get('status',              ['uses' => 'StatusController@main', 'as' => 'status']);
    Route::post('status/create',      ['uses' => 'StatusController@create']);
    Route::delete('status/{status}',  ['uses' => 'StatusController@delete']);

    Route::get('opf',                 ['uses' => 'OpfController@main', 'as' => 'opf']);
    Route::post('opf/create',         ['uses' => 'OpfController@create']);
    Route::delete('opf/{opf}',        ['uses' => 'OpfController@delete']);
});
