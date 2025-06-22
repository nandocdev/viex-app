<?php

namespace Phast\App\Modules\Workflow\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class WorkflowController {
    public function indexAction(Request $request, Response $response){
        return $response->view('workflow/index', []);
    }
}