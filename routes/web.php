<?php
// routes/web.php
declare(strict_types=1);

use Phast\System\Core\Container;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\Facades\Router;

Router::get("/", function (): Response {
   return (new Response())
      ->view('example');
})->name('home.welcome');
