<?php

/**
 * @package     phast/app
 * @subpackage  Controllers
 * @file        HomeController
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 22:16:36
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Controllers;

use Phast\System\Http\Response;
use Phast\System\Http\Request;

class HomeController {
   public function index(Request $request, Response $response): mixed {
      $data = [
         'title' => 'Página de Inicio',
         'welcomeMessage' => '¡Bienvenido a nuestra aplicación Phast!',
         'user' => [
            'name' => 'Jane Doe',
            'role' => 'Admin'
         ]
      ];
      return $response->view('Welcome/home', $data);
   }
}
