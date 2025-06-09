<?php

/**
 * @package     http/phast
 * @subpackage  routes
 * @file        web
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 00:12:47
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

use Phast\System\Core\Container;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\Router;

$router = Container::getInstance()->resolve(Router::class);

$router->get('/', function (Request $req, Response $res) {
   return $res->send('Welcome to Phast Framework!');
});
