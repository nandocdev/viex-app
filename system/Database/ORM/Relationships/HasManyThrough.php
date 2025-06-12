<?php
declare(strict_types=1);

namespace Phast\System\Database\ORM\Relationships;

use Phast\System\Database\ORM\Model;
use Phast\System\Database\ORM\Builder;
use Phast\System\Database\ORM\Relationships\Relation;

class HasManyThrough extends Relation {
   protected Model $through; // El modelo intermedio (ej: User)
   protected string $firstKey; // Clave en el modelo intermedio (ej: country_id en users)
   protected string $secondKey; // Clave en el modelo final (ej: user_id en posts)

   public function __construct(Builder $query, Model $parent, Model $through, string $firstKey, string $secondKey) {
      $this->through = $through;
      $this->firstKey = $firstKey;
      $this->secondKey = $secondKey;

      parent::__construct($query, $parent);
   }

   protected function addConstraints(): void {
      $throughTable = $this->through->getTable();
      $finalTable = $this->query->getModel()->getTable();

      $this->query->getQuery()->join(
         $throughTable,
         $throughTable . '.' . $this->through->getKeyName(),
         '=',
         $finalTable . '.' . $this->secondKey
      );

      $this->query->getQuery()->where(
         $throughTable . '.' . $this->firstKey,
         '=',
         $this->parent->getKey()
      );
   }

   public function getResults() {
      return $this->query->get();
   }
}