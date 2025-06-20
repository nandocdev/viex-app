<?php
/**
 * @package     Modules/Users
 * @subpackage  Controllers
 * @file        UserController
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-19 10:21:26
 * @version     1.0.0
 * @description
 */

namespace Phast\App\Modules\Users\Controllers;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
class UserController {
   public function indexAction(Request $request, Response $response) {
      return $response->view('user/index', []);
   }

   public function showAction(Request $request, Response $response) {
      $id = $request->input('id');
      // Aquí podrías buscar el usuario por ID y devolverlo
      return $response->view('user/show', ['id' => $id]);
   }

}