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
use Phast\System\Database\DB;
use Phast\System\Database\Connection;
class HomeController {
   public function index(Request $request, Response $response): mixed {


      $db = new DB(new Connection());
      $sql = "SELECT * FROM migrations";
      $migrations = $db->select()->execute($sql);
      $data = [
         'title' => 'Página de Inicio',
         'welcomeMessage' => '¡Bienvenido a nuestra aplicación Phast!',
         'user' => [
            'name' => 'Jane Doe',
            'role' => 'Admin'
         ],
         'migrations' => $migrations
      ];



      // retorna json
      return $response->json($data);
   }
}
