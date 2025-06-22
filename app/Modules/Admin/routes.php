<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'admin', 'middleware' => []], function() {
   Router::get('/', 'Admin\Controllers\AdminController@indexAction')->name('admin.index');
});