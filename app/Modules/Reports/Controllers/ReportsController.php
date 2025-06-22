<?php

namespace Phast\App\Modules\Reports\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class ReportsController {
    public function indexAction(Request $request, Response $response){
        return $response->view('reports/index', []);
    }
}