<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'works', 'middleware' => ['Authenticate']], function () {

   // Ruta para mostrar el formulario de creación de un nuevo trabajo
   // UC02.01
   Router::get('/create', 'Work\\Controllers\\WorkController@createAction')->name('work.create');

   // Ruta para guardar el nuevo trabajo en la base de datos
   // UC02.01 / UC02.02
   Router::post('/store', 'Work\\Controllers\\WorkController@storeAction')->name('work.store');

});