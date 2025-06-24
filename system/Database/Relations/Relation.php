<?php

/**
 * @package     phast/system
 * @subpackage  Database/Relations
 * @file        Relation
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Clase base para todas las relaciones
 */

declare(strict_types=1);

namespace Phast\System\Database\Relations;

use Phast\System\Database\Model;
use Phast\System\Database\Query\Builder;

abstract class Relation
{
   protected Builder $query;
   protected Model $parent;
   protected string $related;
   protected string $foreignKey;
   protected string $localKey;

   public function __construct(Builder $query, Model $parent, string $related, string $foreignKey, string $localKey)
   {
      $this->query = $query;
      $this->parent = $parent;
      $this->related = $related;
      $this->foreignKey = $foreignKey;
      $this->localKey = $localKey;
   }

   /**
    * Obtiene los resultados de la relación
    */
   abstract public function getResults();

   /**
    * Obtiene el Query Builder de la relación
    */
   public function getQuery(): Builder
   {
      return $this->query;
   }

   /**
    * Obtiene el modelo padre
    */
   public function getParent(): Model
   {
      return $this->parent;
   }

   /**
    * Obtiene la clase del modelo relacionado
    */
   public function getRelated(): string
   {
      return $this->related;
   }

   /**
    * Obtiene la clave foránea
    */
   public function getForeignKey(): string
   {
      return $this->foreignKey;
   }

   /**
    * Obtiene la clave local
    */
   public function getLocalKey(): string
   {
      return $this->localKey;
   }
}
