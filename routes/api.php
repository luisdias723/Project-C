<?php

use Illuminate\Support\Facades\Route;

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
Route::namespace ('App\Http\Controllers\Api')->group(function () {

Route::post('auth/login', 'AuthController@login');
Route::post('auth/sanctum/csrf-cookie', 'AuthController@login');
Route::post('users/register', 'UserController@register');
Route::post('users/neworder', 'UserController@newOrder');
Route::post('users/getReset', 'UserController@getReset');
Route::post('users/reset/password', 'UserController@resetPassword');
Route::get('formularios/getForm/{id}', 'FormularioController@getForm');
Route::get('formularios/getCount', 'FormularioController@getCount');
Route::get('formularios/getCortejo', 'FormularioController@getCortejo');
Route::get('formularios/getActive', 'FormularioController@getActive');
Route::get('codigos/countries', 'FormularioController@getCountries');
Route::get('formularios/participants', 'FormularioController@getQuestionParticipantes');
Route::post('formularios/answers', 'FormularioController@saveAnswers');
Route::post('formularios/cortejo/answers', 'FormularioController@saveCortejoAnswers');
Route::post('formularios/edit/answers', 'FormularioController@editCortejoAnswers');
Route::post('formularios/uAnswers', 'FormularioController@uAnswers');
Route::post('formularios/editForm', 'FormularioController@EditForm');

Route::post('app/login', 'AuthController@mobileLogin');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('auth/logout', 'AuthController@logout');
    
    Route::get('auth/user', 'AuthController@user');

    // Resource to questions
    Route::apiResource('questions', 'QuestionController');
    Route::post('questions/visibility', 'QuestionController@changeVisibility');
    
    // Resource to Trajes
    Route::apiResource('trajes', 'TrajeController');
    Route::post('trajes/visibility', 'TrajeController@changeVisibility');

    // Resource to Quadros
    Route::apiResource('quadros', 'QuadroController');
    Route::post('quadros/visibility', 'QuadroController@changeVisibility');
    
    // Resource to Formulario
    Route::apiResource('formularios', 'FormularioController');
    Route::post('formularios/visibility', 'FormularioController@changeVisibility');
    Route::post('formularios/conditions', 'FormularioController@saveConditions');

    // Resource to Formulario
    Route::apiResource('condicoes', 'ConditionController');
    Route::post('conditions/getAll', 'ConditionController@getConditions');
    
    // Resource to Formulario
    Route::apiResource('registrations', 'RegistrationController');
    Route::get('registration/status', 'RegistrationController@getStatus');
    Route::get('registration/filters', 'RegistrationController@getFilters');
    Route::get('registration/templates', 'RegistrationController@getTemplates');
    Route::get('registration/history', 'RegistrationController@getHistory');
    Route::get('registration/counter', 'RegistrationController@getCount');
    Route::get('registration/estatistica', 'RegistrationController@getEstatistica');
    Route::get('registration/trajeCounter', 'RegistrationController@getTrajeCounter');
    Route::post('registration/upStatus', 'RegistrationController@updateStatus');
    Route::post('registration/upCortejoStatus', 'RegistrationController@updateCortejoStatus');
    Route::post('registration/upAnswer', 'RegistrationController@updateAnswer');
    Route::post('registration/eAnswer', 'RegistrationController@editAnswer');
    Route::post('registration/eCortejoAnswer', 'RegistrationController@editCortejoAnswer');
    Route::post('registration/saveObs', 'RegistrationController@SaveObservation');
    Route::post('registration/validateQR', 'RegistrationController@ValidateQR');
    Route::post('registration/saveQR', 'RegistrationController@SaveQR');
    Route::post('registration/saveQRCortejo', 'RegistrationController@SaveQRCortejo');

});
});
