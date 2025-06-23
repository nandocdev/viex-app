<?php

use Phast\System\Routing\Facades\Router;

// Router::group(['prefix' => 'home', 'middleware' => []], function() {
//    Router::get('/', 'Home\Controllers\HomeController@indexAction')->name('home.index');
// });


Router::get('/about', 'Home\Controllers\HomeController@aboutAction')->name('home.about');
Router::get('/contact', 'Home\Controllers\HomeController@contactAction')->name('home.contact');
Router::get('/projects/publics', 'Home\Controllers\HomeController@proyectosPublicosAction')->name('home.public_projects');
Router::get('/dashboard', 'Home\Controllers\HomeController@dashboardAction')->name('home.dashboard')->middleware('Authenticate');
