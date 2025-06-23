<?php

namespace Phast\App\Modules\Home\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class HomeController
{
    public function indexAction(Request $request, Response $response)
    {
        return $response->view('home/index', []);
    }

    public function aboutAction(Request $request, Response $response)
    {
        return $response->view('home/about', []);
    }

    public function contactAction(Request $request, Response $response)
    {
        return $response->view('home/contact', []);
    }

    // dashboard action
    public function dashboardAction(Request $request, Response $response)
    {
        return $response->view('home/dashboard', []);
    }
}
