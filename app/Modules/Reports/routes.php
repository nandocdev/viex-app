<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'reports', 'middleware' => []], function() {
   Router::get('/', 'Reports\Controllers\ReportsController@indexAction')->name('reports.index');
});