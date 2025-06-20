<?php

namespace Phast\App\Modules\Usuarios\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class UsuariosController {
    public function indexAction(Request $request, Response $response){
        return $response->view('usuarios/index', []);
    }
}