<?php
/**
 * @package     Phoenix/Entity
 * @subpackage  Attributes
 * @file        PrimaryKey
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-23 16:28:21
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Entity\Attributes;

class PrimaryKey {
   /**
    * Nombre de la clave primaria.
    * @var string
    */
   protected string $name;

   /**
    * Constructor que inicializa el nombre de la clave primaria.
    *
    * @param string $name Nombre de la clave primaria.
    */
   public function __construct(string $name = 'id') {
      $this->name = $name;
   }

   /**
    * Obtiene el nombre de la clave primaria.
    *
    * @return string Nombre de la clave primaria.
    */
   public function getName(): string {
      return $this->name;
   }
}