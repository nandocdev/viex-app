<?php
/**
 * @package     phast/system
 * @subpackage  Http/Exceptions
 * @file        TokenMismatchException
 * @description Excepción para fallos de verificación de token CSRF.
 */
declare(strict_types=1);

namespace Phast\System\Http\Exceptions;

class TokenMismatchException extends \Exception {
   public function __construct(string $message = 'CSRF token mismatch.', int $code = 419, \Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
   }
}