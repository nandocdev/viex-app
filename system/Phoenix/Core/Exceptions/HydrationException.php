<?php
/**
 * @package     Phoenix/Core
 * @subpackage  Exceptions
 * @file        HydrationException
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:12:07
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Core\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Lanzada cuando ocurre un error durante el proceso de hidratación o deshidratación.
 *
 * La hidratación es el proceso de llenar un objeto (Entity) con datos provenientes
 * de la base de datos. La deshidratación es el proceso inverso. Esta excepción
 * señala problemas en esa capa de traducción, como tipos de datos incompatibles,
 * propiedades faltantes o clases de entidad no instanciables.
 */
class HydrationException extends RuntimeException {
   /**
    * Constructor de la excepción.
    *
    * @param string $message Mensaje descriptivo del error de hidratación.
    * @param int $code El código de la excepción.
    * @param ?Throwable $previous La excepción anterior, si existe (usualmente TypeError o ReflectionException).
    */
   public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
      parent::__construct($message, $code, $previous);
   }
}