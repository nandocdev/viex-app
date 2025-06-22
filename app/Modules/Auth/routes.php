<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'auth', 'middleware' => []], function() {
   Router::get('/', 'Auth\Controllers\AuthController@indexAction')->name('auth.index');
});