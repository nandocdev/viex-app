<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'usuarios', 'middleware' => []], function() {
   Router::get('/', 'Usuarios\Controllers\UsuariosController@indexAction')->name('usuarios.index');
});