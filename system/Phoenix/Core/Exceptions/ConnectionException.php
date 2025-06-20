<?php
/**
 * @package     Phoenix/Core
 * @subpackage  Exceptions
 * @file        ConnectionException
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:09:45
 * @version     1.0.0
 * @description
 */


declare(strict_types=1);

namespace Phast\System\Phoenix\Core\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Lanzada cuando el ORM no puede establecer una conexión con la base de datos.
 *
 * Esta excepción se utiliza para señalar problemas que ocurren durante la fase
 * de inicialización de la conexión, como credenciales incorrectas, un host
 * inalcanzable, un driver no soportado, o configuración faltante.
 *
 * Esto la diferencia de QueryException, que se lanza por errores durante
 * la ejecución de una consulta sobre una conexión ya establecida.
 */
class ConnectionException extends RuntimeException {
   /**
    * Constructor de la excepción.
    *
    * @param string $message Mensaje descriptivo del error de conexión.
    * @param int $code El código de la excepción.
    * @param ?Throwable $previous La excepción anterior, si existe (usualmente PDOException).
    */
   public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
   }
}