<?php

namespace Phast\App\Modules\Auth\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class AuthController {
    public function indexAction(Request $request, Response $response){
        return $response->view('auth/index', []);
    }
}