<?php

// api for frontend
Route::group([
    'prefix'     => 'api',
    'namespace'  => 'Botble\RealEstate\Http\Controllers',
    'middleware' => ['api'],
], function () {
    Route::get('/categories', 'CategoryController@index');
    Route::get('/projects/{slug?}', 'ProjectController@index');
    Route::get('/properties/{slug?}', 'PropertyController@index');
    Route::get('/agents', 'PropertyController@getAgents');
    Route::get('/cities', 'PropertyController@getCities');
    Route::get('/facilities', 'PropertyController@getFacilities');
    Route::get('/features', 'PropertyController@getFeatures');

    Route::get('/services/{id?}', 'PublicController@getServices');

    Route::get('/agencies/{id?}', 'AccountController@getAgency');
    Route::post('/consult', 'PublicController@postSendConsult');

    Route::post('/login', 'API\AuthenticationController@login');
    Route::post('/register', 'API\AuthenticationController@register');


    Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'user'], function() {
        Route::post('/image/upload', 'AccountController@postUpload');

        Route::get('/', 'API\AccountController@getProfile');
        Route::patch('/', 'API\AccountController@updateProfile');

        Route::get('/consult', 'ConsultController@ajaxGetConsult');
        Route::patch('/consult/{id}', 'ConsultController@ajaxChangeConsult');

        Route::patch('/update-avatar', 'API\AccountController@updateAvatar');
        Route::patch('/update-password', 'API\AccountController@updatePassword');

        Route::delete('/properties/{id}', 'PropertyController@destroy');

        Route::get('/activity', 'API\AccountController@getActivity');
        Route::get('/properties/{id?}', 'PropertyController@index');

        Route::post('/property', 'PropertyController@store');
        Route::patch('/property/{id}', 'PropertyController@update');
    });
});

Route::group([
    'prefix'     => 'api/v1',
    'namespace'  => 'Botble\RealEstate\Http\Controllers\API',
    'middleware' => ['api'],
], function () {

    Route::post('register', 'AuthenticationController@register');
    Route::post('login', 'AuthenticationController@login');

    Route::post('password/forgot', 'ForgotPasswordController@sendResetLinkEmail');

    Route::post('verify-account', 'VerificationController@verify');
    Route::post('resend-verify-account-email', 'VerificationController@resend');

    Route::group(['middleware' => ['auth:account-api']], function () {
        Route::get('logout', 'AuthenticationController@logout');
        Route::get('me', 'AccountController@getProfile');
        Route::put('me', 'AccountController@updateProfile');
        Route::post('update-avatar', 'AccountController@updateAvatar');
        Route::put('change-password', 'AccountController@updatePassword');
    });

});
