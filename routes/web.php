<?php
// routes/web.php
declare(strict_types=1);

use Phast\System\Core\Container;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\Facades\Router;

Router::post("/", function (): Response {
   return (new Response())
      ->send("<h1>Welcome to Phast Framework</h1><p>This is the home page.</p>");
})->name('home.welcome');
