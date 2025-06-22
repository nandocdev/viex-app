<?php

namespace Phast\App\Modules\Projects\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class ProjectsController {
    public function indexAction(Request $request, Response $response){
        return $response->view('projects/index', []);
    }
}