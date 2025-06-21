<?php
/**
 * @package     Phoenix/Entity
 * @subpackage  Attributes
 * @file        Table
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-21 00:04:10
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Entity\Attributes;

use Attribute;

/**
 * Atributo para mapear una clase de Entidad a una tabla específica de la base de datos.
 *
 * Este atributo se coloca sobre una clase que implementa EntityInterface para
 * declarar explícitamente el nombre de la tabla a la que está asociada. Si este
 * atributo no está presente, el ORM puede optar por inferir el nombre de la tabla
 * a partir del nombre de la clase.
 *
 * @example
 * #[Table(name: 'app_users')]
 * class User implements EntityInterface
 * {
 * // ...
 * }
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Table {
   /**
    * @param string $name El nombre exacto de la tabla en la base de datos.
    */
   public function __construct(
      public readonly string $name
   ) {
   }
}