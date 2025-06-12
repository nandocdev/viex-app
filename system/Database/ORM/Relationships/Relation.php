<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM/Relationships
 * @file        Relation
 * @description Clase base abstracta para todos los tipos de relaciones del ORM.
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Relationships;

use Phast\System\Database\ORM\Builder;
use Phast\System\Database\ORM\Model;

abstract class Relation {
   /** El constructor de consultas para el modelo relacionado. */
   protected Builder $query;

   /** La instancia del modelo padre. */
   protected Model $parent;

   /**
    * @param Builder $query El constructor para el modelo relacionado.
    * @param Model $parent El modelo desde el que se origina la relación.
    */
   public function __construct(Builder $query, Model $parent) {
      $this->query = $query;
      $this->parent = $parent;

      $this->addConstraints();
   }

   /**
    * Añade las restricciones de la relación a la consulta subyacente.
    * Cada tipo de relación implementará esto de forma diferente.
    */
   abstract protected function addConstraints(): void;

   /**
    * Obtiene los resultados de la relación.
    */
   abstract public function getResults();

   /**
    * Permite encadenar métodos del Query Builder a la relación.
    * Ejemplo: $user->posts()->where('active', 1)->orderBy('created_at')->get();
    * El `where()` y `orderBy()` se pasan dinámicamente al Query Builder interno.
    */
   public function __call(string $method, array $parameters) {
      $result = $this->query->$method(...$parameters);

      // Si el método devuelve el propio builder, devolvemos `$this` (la relación)
      // para mantener la fluidez de la API.
      if ($result instanceof Builder) {
         return $this;
      }

      // De lo contrario, devolvemos el resultado de la operación (ej: count(), exists()).
      return $result;
   }
}