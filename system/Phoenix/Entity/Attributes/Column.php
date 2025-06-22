<?php
/**
 * @package     Phoenix/Entity
 * @subpackage  Attributes
 * @file        Column
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 23:23:40
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Entity\Attributes;

use Attribute;

/**
 * Atributo para mapear una propiedad de una entidad a una columna de la base de datos.
 *
 * Este atributo se coloca directamente sobre una propiedad en una clase de Entidad
 * para proporcionar metadatos sobre cómo se relaciona con la tabla de la base de datos.
 *
 * @example
 * #[Column(name: 'user_id', primary: true)]
 * public int $id;
 *
 * #[Column(name: 'email_address')]
 * public string $email;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Column {
   /**
    * @param ?string $name El nombre exacto de la columna en la base de datos.
    *                      Si es null, el ORM inferirá el nombre a partir del nombre
    *                      de la propiedad (convirtiéndolo a snake_case).
    * @param bool $primary Indica si esta columna es (o forma parte de) la clave primaria.
    */
   public function __construct(
      public readonly ?string $name = null,
      public readonly bool $primary = false
   ) {
   }
}