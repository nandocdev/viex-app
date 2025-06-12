<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM/Relationships
 * @file        HasOne
 * @description Implementación de la relación "uno a uno".
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Relationships;

use Phast\System\Database\ORM\Builder;
use Phast\System\Database\ORM\Model;

class HasOne extends Relation {
   /** La clave foránea en la tabla del modelo relacionado. */
   protected string $foreignKey;

   /** La clave local (generalmente primaria) en la tabla del modelo padre. */
   protected string $localKey;

   public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey) {
      $this->foreignKey = $foreignKey;
      $this->localKey = $localKey;

      parent::__construct($query, $parent);
   }

   /**
    * Añade la cláusula WHERE para la relación HasOne.
    * `SELECT * FROM phones WHERE user_id = ?` (el ID del usuario padre)
    */
   protected function addConstraints(): void {
      $this->query->where($this->foreignKey, '=', $this->parent->getAttribute($this->localKey));
   }

   /**
    * Ejecuta la consulta y devuelve un único modelo relacionado.
    */
   public function getResults() {
      return $this->query->first();
   }
}