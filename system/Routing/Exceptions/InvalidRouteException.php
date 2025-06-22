<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Exceptions
 * @file        InvalidRouteException.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Excepción para errores en la definición o configuración de una ruta.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Exceptions;

/**
 * Representa un error de "servidor interno" (HTTP 500) causado por una
 * configuración de ruta incorrecta.
 *
 * Hereda de \LogicException, que es apropiada para errores en la lógica
 * del programa (como una ruta mal definida que nunca podría funcionar).
 */
class InvalidRouteException extends \LogicException {
   /**
    * El código de estado HTTP asociado con esta excepción.
    * @var int
    */
   protected int $statusCode = 500;

   /**
    * Constructor de la excepción.
    *
    * @param string $message Mensaje de error descriptivo.
    * @param int $code Código de la excepción.
    * @param \Throwable|null $previous Excepción anterior para encadenamiento.
    */
   public function __construct(string $message = 'Invalid route configuration.', int $code = 0, ?\Throwable $previous = null) {
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