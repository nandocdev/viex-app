<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'auth', 'middleware' => []], function () {
   Router::get('/', 'Auth\Controllers\AuthController@indexAction')->name('auth.login.form');
   Router::post('/', 'Auth\Controllers\AuthController@loginAction')->name('auth.login.attempt');

   Router::get('/logout', 'Auth\Controllers\AuthController@logoutAction')->name('auth.logout');
});
