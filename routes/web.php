<?php
// routes/web.php
declare(strict_types=1);

use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\Facades\Router;

$pathRoute = (glob(__DIR__ . '/../app/Modules/*/routes.php') ?: []);

foreach ($pathRoute as $routeFile) {
   if (file_exists($routeFile)) {
      require_once $routeFile;
   }
}

Router::get('/', function (Request $request, Response $response) {
   return $response->json(['message' => 'Welcome to the Phast Framework!']);
});