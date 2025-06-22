<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'workflow', 'middleware' => []], function() {
   Router::get('/', 'Workflow\Controllers\WorkflowController@indexAction')->name('workflow.index');
});