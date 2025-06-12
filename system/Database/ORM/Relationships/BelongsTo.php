<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM/Relationships
 * @file        BelongsTo
 * @description Implementación de la relación "pertenece a".
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Relationships;

use Phast\System\Database\ORM\Builder;
use Phast\System\Database\ORM\Model;

class BelongsTo extends Relation {
   /** La clave foránea en la tabla del modelo *padre* (el que define la relación). */
   protected string $foreignKey;

   /** La clave primaria (u otra clave) en la tabla del modelo *relacionado*. */
   protected string $ownerKey;

   public function __construct(Builder $query, Model $parent, string $foreignKey, string $ownerKey) {
      $this->foreignKey = $foreignKey;
      $this->ownerKey = $ownerKey;

      parent::__construct($query, $parent);
   }

   /**
    * Añade la cláusula WHERE para la relación BelongsTo.
    * `SELECT * FROM users WHERE id = ?` (el user_id del post padre)
    */
   protected function addConstraints(): void {
      $this->query->where($this->ownerKey, '=', $this->parent->getAttribute($this->foreignKey));
   }

   /**
    * Ejecuta la consulta y devuelve un único modelo relacionado.
    */
   public function getResults() {
      return $this->query->first();
   }
}