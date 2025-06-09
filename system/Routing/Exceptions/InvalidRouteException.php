<?php

/**
 * @package     system/Routing
 * @subpackage  Exceptions
 * @file        InvalidRouteException
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:36:55
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Routing\Exceptions;

class InvalidRouteException extends \Exception {
   function __construct(string $message = "Invalid route", int $code = 400, \Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
   }
}
