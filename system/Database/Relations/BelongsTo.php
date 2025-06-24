<?php

/**
 * @package     phast/system
 * @subpackage  Database/Relations
 * @file        BelongsTo
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Relación "pertenece a"
 */

declare(strict_types=1);

namespace Phast\System\Database\Relations;

use Phast\System\Database\Model;

class BelongsTo extends Relation
{
   public function getResults()
   {
      $related = new $this->related();
      $table = $related->getTable();

      return $this->query
         ->from($table)
         ->where($this->localKey, '=', $this->parent->getAttribute($this->foreignKey))
         ->first();
   }

   /**
    * Asocia un modelo a esta relación
    */
   public function associate(Model $model): bool
   {
      $this->parent->setAttribute($this->foreignKey, $model->getKey());
      return $this->parent->save();
   }

   /**
    * Desasocia el modelo de esta relación
    */
   public function dissociate(): bool
   {
      $this->parent->setAttribute($this->foreignKey, null);
      return $this->parent->save();
   }

   /**
    * Obtiene el modelo relacionado o lo crea si no existe
    */
   public function firstOrCreate(array $attributes = [])
   {
      $related = new $this->related();
      $existing = $related->where($attributes)->first();

      if ($existing) {
         return $existing;
      }

      return $related->create($attributes);
   }
}
