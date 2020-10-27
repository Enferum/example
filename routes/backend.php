<?php
    
    use Illuminate\Support\Facades\Route;
    
    Route::get('login', 'AuthController@showLoginForm')->name('login');
    Route::post('login', 'AuthController@login')->name('auth');
    Route::any('logout', 'AuthController@logout')->name('logout');
    
    Route::group(['middleware' => 'auth:admin'], function () {
        
        Route::post('verifyOTP', 'AuthController@verify')->name('verifyOTP');
        
        Route::get('verifyOTP', 'AuthController@getVerifyForm')->name('verify_form');


    Route::group(['middleware' => 'google2fa'], function () {
        
        Route::get('enable2fa', 'AuthController@enableTwoFA')->name('enable2fa');
        
        Route::get('profile', 'UserController@myProfile')->name('profile');
        
        Route::get('login/2fa', 'AuthController@enableTwoFA')->name('2fa');
        
        Route::get('disable2fa', 'AuthController@disableTwoFA')->name('disable2fa');
        
        
        Route::get('/', 'DashboardController')->name('dashboard');
        
        Route::resource('user', 'UserController')->except(['show']);
        Route::resource('supervisor', 'SupervisorController')->except(['show']);
        Route::resource('category', 'CategoryController')->except(['show']);
        
        Route::get('permission/{user}', 'UserController@indexPermission')->name('permission.index');
        Route::post('permission/{user}', 'UserController@updatePermission')->name('permission.update');
        
        Route::get('user/{user}/edit/new-password/', 'ResetPasswordController')->name('new-password');
        
        Route::get('user/{user}/edit/service/{service}', 'ServiceController')->name('service.delete');
        
        Route::resource('order', 'OrderController')->except('store, create, show');
        
        Route::get('order-filter/{id}', 'OrderController@filter')->name('order-filter');
        
        Route::resource('support', 'SupportController')->except(['show','create','destroy']);
        
        Route::resource('pages', 'PageController')->except(['show']);
    });
});