<?php
/**
 * @package     app/Modules
 * @subpackage  Users
 * @file        routes
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-19 12:34:35
 * @version     1.0.0
 * @description
 */

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'users', 'middleware' => []], function () {
   Router::get('/', 'Modules\Users\Controllers\UserController@indexAction')->name('users.index');
   Router::get('{id:\d+}', 'Modules\Users\Controllers\UserController@showAction')->name('users.show');
   // ... más rutas de usuarios
});