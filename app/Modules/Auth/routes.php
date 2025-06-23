<?php

use Phast\System\Routing\Facades\Router;

Router::group(['prefix' => 'auth', 'middleware' => []], function () {
   Router::get('/', 'Auth\Controllers\AuthController@indexAction')->name('auth.login.form');
   Router::post('/', 'Auth\Controllers\AuthController@loginAction')->name('auth.login.attempt');

   Router::get('/logout', 'Auth\Controllers\AuthController@logoutAction')->name('auth.logout');
});


// Agrupamos todas las rutas de perfil para aplicar el middleware de autenticación una sola vez.
Router::group(['prefix' => 'profile', 'middleware' => ['Authenticate']], function () {

   // Ruta para mostrar el formulario del perfil
   Router::get('/', 'Auth\\Controllers\\ProfileController@showProfileAction')->name('profile.show');

   // Ruta para procesar la actualización del perfil
   Router::post('/update', 'Auth\\Controllers\\ProfileController@updateProfileAction')->name('profile.update');

});