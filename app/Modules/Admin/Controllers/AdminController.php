<?php

namespace Phast\App\Modules\Admin\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class AdminController {
    public function indexAction(Request $request, Response $response){
        return $response->view('admin/index', []);
    }
}