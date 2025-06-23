<?php

namespace Phast\App\Modules\Home\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class HomeController {
    public function indexAction(Request $request, Response $response) {
        return $response->view('home/index', [], 'landing');
    }

    public function aboutAction(Request $request, Response $response) {
        return $response->view('home/about', [], 'landing');
    }

    public function contactAction(Request $request, Response $response) {
        return $response->view('home/contact', [], 'landing');
    }

    // dashboard action
    public function dashboardAction(Request $request, Response $response) {
        return $response->view('home/dashboard', []);
    }

    // proyectos publicos
    public function proyectosPublicosAction(Request $request, Response $response) {
        return $response->view('home/proyectos_publicos', [], 'landing');
    }
}
