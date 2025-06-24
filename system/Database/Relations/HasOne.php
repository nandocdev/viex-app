<?php

/**
 * @package     phast/system
 * @subpackage  Database/Relations
 * @file        HasOne
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Relación "uno a uno"
 */

declare(strict_types=1);

namespace Phast\System\Database\Relations;

use Phast\System\Database\Model;

class HasOne extends Relation
{
   public function getResults()
   {
      $related = new $this->related();
      $table = $related->getTable();

      return $this->query
         ->from($table)
         ->where($this->foreignKey, '=', $this->parent->getKey())
         ->first();
   }

   /**
    * Crea una nueva instancia del modelo relacionado
    */
   public function create(array $attributes = []): Model
   {
      $related = new $this->related();
      $attributes[$this->foreignKey] = $this->parent->getKey();

      return $related->create($attributes);
   }

   /**
    * Actualiza o crea el modelo relacionado
    */
   public function updateOrCreate(array $attributes, array $values = []): Model
   {
      $related = new $this->related();
      $attributes[$this->foreignKey] = $this->parent->getKey();

      $existing = $related->where($attributes)->first();

      if ($existing) {
         $existing->update($values);
         return $existing;
      }

      return $this->create(array_merge($attributes, $values));
   }
}
