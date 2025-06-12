<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Model
 * @description Trait para gestionar $hidden y $visible
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Concerns;

trait HidesAttributes {
   /**
    * Los atributos que deben ocultarse al serializar.
    * @var array
    */
   protected array $hidden = [];

   /**
    * Los atributos que deben ser visibles al serializar.
    * Si se define, actúa como una lista blanca, ignorando $hidden.
    * @var array
    */
   protected array $visible = [];

   /**
    * Convierte el modelo a un array.
    */
   public function toArray(): array {
      $attributes = $this->getAttributes();
      return $this->filterAttributes($attributes);
   }

   /**
    * Convierte el modelo a JSON.
    */
   public function toJson(int $options = 0): string {
      return json_encode($this->toArray(), $options);
   }

   /**
    * Filtra los atributos según las propiedades $visible y $hidden.
    */
   protected function filterAttributes(array $attributes): array {
      if (!empty($this->visible)) {
         // Modo "lista blanca": solo se incluyen los atributos en $visible.
         return array_intersect_key($attributes, array_flip($this->visible));
      }

      if (!empty($this->hidden)) {
         // Modo "lista negra": se excluyen los atributos en $hidden.
         return array_diff_key($attributes, array_flip($this->hidden));
      }

      return $attributes;
   }
}