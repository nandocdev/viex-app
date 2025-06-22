<?php
/**
 * @package Phast/System
 * @subpackage Routing/Exceptions
 * @file RouteNotFoundException.php
 * @author Fernando Castillo <nando.castillo@outlook.com>
 * @date 2024-06-09
 * @version 1.0.0
 * @description Excepción lanzada cuando no se encuentra una ruta para la petición actual.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Exceptions;

class RouteNotFoundException extends \RuntimeException {
   /**
    * El código de estado HTTP asociado con esta excepción.
    * @var int
    */
   protected int $statusCode = 404;

   /**
    * Constructor de la excepción.
    *
    * @param string $message Mensaje de error descriptivo.
    * @param int $code Código de la excepción (no el código HTTP).
    * @param \Throwable|null $previous Excepción anterior para encadenamiento.
    */
   public function __construct(string $message = 'Route not found.', int $code = 0, ?\Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
   }

   /**
    * Obtiene el código de estado HTTP recomendado para esta excepción.
    *
    * @return int
    */
   public function getStatusCode(): int {
      return $this->statusCode;
   }
}