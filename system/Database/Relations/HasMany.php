<?php

/**
 * @package     phast/system
 * @subpackage  Database/Relations
 * @file        HasMany
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Relación "uno a muchos"
 */

declare(strict_types=1);

namespace Phast\System\Database\Relations;

use Phast\System\Database\Model;

class HasMany extends Relation
{
   public function getResults()
   {
      $related = new $this->related();
      $table = $related->getTable();

      return $this->query
         ->from($table)
         ->where($this->foreignKey, '=', $this->parent->getKey())
         ->get();
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
    * Obtiene el número de modelos relacionados
    */
   public function count(): int
   {
      $related = new $this->related();
      $table = $related->getTable();

      return $this->query
         ->from($table)
         ->where($this->foreignKey, '=', $this->parent->getKey())
         ->count();
   }

   /**
    * Obtiene el primer modelo relacionado
    */
   public function first()
   {
      $related = new $this->related();
      $table = $related->getTable();

      return $this->query
         ->from($table)
         ->where($this->foreignKey, '=', $this->parent->getKey())
         ->first();
   }

   /**
    * Obtiene el último modelo relacionado
    */
   public function latest(string $column = 'created_at')
   {
      $related = new $this->related();
      $table = $related->getTable();

      return $this->query
         ->from($table)
         ->where($this->foreignKey, '=', $this->parent->getKey())
         ->orderBy($column, 'DESC')
         ->first();
   }
}
