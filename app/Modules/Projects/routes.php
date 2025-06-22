<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'projects', 'middleware' => []], function() {
   Router::get('/', 'Projects\Controllers\ProjectsController@indexAction')->name('projects.index');
});