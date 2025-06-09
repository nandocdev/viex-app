<?php

/**
 * @package     system/Routing
 * @subpackage  Exceptions
 * @file        RouteNotFoundException
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:36:15
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Routing\Exceptions;

class RouteNotFoundException extends \Exception {
   function __construct(string $message = "Route not found", int $code = 404, \Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
   }
}
