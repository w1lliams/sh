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

    Route::get('organization',                          'OrganizationController@fetch')->name('organization');
    Route::get('organization/create',                   'OrganizationController@createPage');
    Route::post('organization/create',                  'OrganizationController@create');
    Route::get('organization/{organization}/edit',      'OrganizationController@editPage')->name('edit_organization');
    Route::post('organization/{organization}/edit',     'OrganizationController@create');
    Route::get('organization/{organization}/addDepartment', 'OrganizationController@addDepartmentPage')->name('add_department');
    Route::get('organization/{organization}/workers',   'WorkerController@workersPage')->name('workers');
    Route::get('organization/{organization}/addWorkers', 'WorkerController@addWorkersPage')->name('add_workers');
    Route::get('organization/{organization}/snapshot/{snapshot}', 'WorkerController@snapshotPage');

    Route::get('status',                                ['uses' => 'StatusController@main', 'as' => 'status']);
    Route::post('status/create',                        ['uses' => 'StatusController@create']);
    Route::delete('status/{status}',                    ['uses' => 'StatusController@delete']);

    Route::get('opf',                                   ['uses' => 'OpfController@main', 'as' => 'opf']);
    Route::post('opf/create',                           ['uses' => 'OpfController@create']);
    Route::delete('opf/{opf}',                          ['uses' => 'OpfController@delete']);

    Route::get('type',                                  ['uses' => 'TypeController@main', 'as' => 'type']);
    Route::post('type/create',                          ['uses' => 'TypeController@create']);
    Route::delete('type/{type}',                        ['uses' => 'TypeController@delete']);

    Route::get('city',                                  ['uses' => 'CityController@main', 'as' => 'city']);
    Route::post('city/create',                          ['uses' => 'CityController@create']);
    Route::delete('city/{city}',                        ['uses' => 'CityController@delete']);

    // API
    Route::group(['prefix' => 'api'], function () {
        Route::get('organization/search',                           'OrganizationController@search');
        Route::post('worker/check_new_workers',                     'WorkerController@checkNewWorkers');
        Route::post('worker/change_department',                     'WorkerController@updateDepartment');
        Route::post('worker/{worker}',                              'WorkerController@update');
        Route::post('organization/{organization}/push_workers',     'WorkerController@pushWorkers');
        Route::get('city/search',                                   'CityController@search');
    });
});
