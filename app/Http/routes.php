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

    Route::get('organization',         ['uses' => 'OrganizationController@fetch', 'as' => 'organization']);
    Route::get('organization/create',  ['uses' => 'OrganizationController@createPage']);
    Route::post('organization/create', ['uses' => 'OrganizationController@create']);
    Route::get('organization/{organization}/edit',    ['uses' => 'OrganizationController@editPage']);
    Route::post('organization/{organization}/edit',   ['uses' => 'OrganizationController@create']);
    Route::get('organization/{organization}/workers', ['uses' => 'OrganizationController@workersPage']);

    Route::post('workers/check_new_workers', ['uses' => 'WorkerController@checkNewWorkers']);

    Route::get('status',              ['uses' => 'StatusController@main', 'as' => 'status']);
    Route::post('status/create',      ['uses' => 'StatusController@create']);
    Route::delete('status/{status}',  ['uses' => 'StatusController@delete']);

    Route::get('opf',                 ['uses' => 'OpfController@main', 'as' => 'opf']);
    Route::post('opf/create',         ['uses' => 'OpfController@create']);
    Route::delete('opf/{opf}',        ['uses' => 'OpfController@delete']);

    Route::get('type',                 ['uses' => 'TypeController@main', 'as' => 'type']);
    Route::post('type/create',         ['uses' => 'TypeController@create']);
    Route::delete('type/{type}',        ['uses' => 'TypeController@delete']);

    Route::get('city',                 ['uses' => 'CityController@main', 'as' => 'city']);
    Route::post('city/create',         ['uses' => 'CityController@create']);
    Route::delete('city/{city}',       ['uses' => 'CityController@delete']);

    Route::get('position',                 ['uses' => 'PositionController@main', 'as' => 'position']);
    Route::post('position/create',         ['uses' => 'PositionController@create']);
    Route::delete('position/{position}',   ['uses' => 'PositionController@delete']);
});
