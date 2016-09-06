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

Route::group(['middleware' => 'web'], function () {
  Route::auth();

  // открытые роуты админки
  Route::group(['namespace' => 'Admin'], function () {
    Route::get('translate', 'TranslateController@fioPage')->name('translate');
    Route::post('admin/api/translate/fio', 'TranslateController@fio');

    Route::get('check_list', 'WorkerController@checkPage');
    Route::post('admin/api/worker/check_new_workers', 'WorkerController@checkNewWorkers');
    Route::get('admin/api/organization/search', 'OrganizationController@search');
  });
});

// роуты админки
Route::group(['middleware' => ['web', 'role:admin'], 'namespace' => 'Admin', 'prefix' => 'admin', 'as' => 'admin::'], function () {

    Route::get('organization',                                        'OrganizationController@fetch')->name('organization');
    Route::get('organization/create',                                 'OrganizationController@createPage');
    Route::post('organization/create',                                'OrganizationController@create');
    Route::get('organization/{organization}/edit',                    'OrganizationController@editPage')->name('edit_organization');
    Route::get('organization/{organization}/delete',                  'OrganizationController@delete')->name('delete_organization');
    Route::post('organization/{organization}/edit',                   'OrganizationController@create');
    Route::get('organization/{organization}/addDepartment',           'OrganizationController@addDepartmentPage')->name('add_department');
    Route::get('organization/{organization}/inquiry',                 'OrganizationController@inquiryPage')->name('organization_inquiries');
    Route::post('organization/{organization}/inquiry',                'OrganizationController@addInquiry')->name('add_organization_inquiry');
    Route::delete('organization/{organization}/inquiry/{inquiry}',    'OrganizationController@removeInquiry')->name('remove_inquiry');
    Route::get('organization/{organization}/workers',                 'WorkerController@workersPage')->name('workers');
    Route::get('organization/{organization}/addWorkers',              'WorkerController@addWorkersPage')->name('add_workers');
    Route::get('organization/{organization}/snapshot/{snapshot}',     'WorkerController@snapshotPage');
    Route::get('worker/{worker}',                                     'WorkerController@workerNotesPage')->name('worker_notes');
    Route::post('worker/{worker}',                                    'WorkerController@addNote')->name('add_note');
    Route::delete('note/{note}',                                      'WorkerController@deleteNote')->name('delete_note');

    Route::get('status',                                ['uses' => 'StatusController@main', 'as' => 'status']);
    Route::post('status/create',                        ['uses' => 'StatusController@create']);
    Route::delete('status/{status}',                    ['uses' => 'StatusController@delete']);

    Route::get('opf',                                   ['uses' => 'OpfController@main', 'as' => 'opf']);
    Route::post('opf/create',                           ['uses' => 'OpfController@create']);
    Route::delete('opf/{opf}',                          ['uses' => 'OpfController@delete']);

    Route::get('type',                                  ['uses' => 'TypeController@main', 'as' => 'type']);
    Route::post('type/create',                          ['uses' => 'TypeController@create']);
    Route::delete('type/{type}',                        ['uses' => 'TypeController@delete']);

    Route::get('inquiry',                               ['uses' => 'InquiryController@main', 'as' => 'inquiry']);
    Route::post('inquiry/create',                       ['uses' => 'InquiryController@create']);
    Route::delete('inquiry/{inquiry}',                  ['uses' => 'InquiryController@delete']);

    Route::get('city',                                  ['uses' => 'CityController@main', 'as' => 'city']);
    Route::post('city/create',                          ['uses' => 'CityController@create']);
    Route::delete('city/{city}',                        ['uses' => 'CityController@delete']);

    Route::get('feedback',                              ['uses' => 'FeedbackController@main', 'as' => 'feedback']);
    Route::get('feedback/{feedback}/delete',            ['uses' => 'FeedbackController@delete', 'as' => 'delete_feedback']);

    // API
    Route::group(['prefix' => 'api'], function () {
        Route::post('organization_inquiry/{inquiry}',             'OrganizationController@editInquiry');
        Route::post('worker/change_department',                   'WorkerController@updateDepartment');
        Route::post('worker/{worker}',                            'WorkerController@update');
        Route::post('organization/{organization}/push_workers',   'WorkerController@pushWorkers');
        Route::get('city/search',                                 'CityController@search');
    });
});

// site
Route::group(['middleware' => ['web'], 'namespace' => 'Site'], function () {
  Route::get('/', 'HomeController@index')->name('home');
  Route::get('/help', 'HomeController@help')->name('help');
  Route::get('/feedback', 'HomeController@feedbackPage')->name('feedback');
  Route::post('/feedback', 'HomeController@saveFeedback')->name('save_feedback');
  Route::get('worker/{worker}', 'WorkerController@workerPage')->name('worker');
  Route::get('organization/{organization}', 'OrganizationController@organizationPage')->name('organization');
  Route::get('organization', 'OrganizationController@organizationsPage')->name('organizations');

  Route::post('/hypercomments', 'WorkerController@getcounthypercomments');	// добавлено Брелем как Notify URL для Hypercomments для уведомления о событиях с добавлением/удалением отзыва

  Route::group(['prefix' => 'api'], function () {
    Route::post('search', 'SearchController@searchApi');
  });
});
