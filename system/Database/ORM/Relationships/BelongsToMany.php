<?php
declare(strict_types=1);

namespace Phast\System\Database\ORM\Relationships;

use Phast\System\Database\ORM\Builder;
use Phast\System\Database\ORM\Model;

class BelongsToMany extends Relation {
   protected string $relatedTable;
   protected string $pivotTable;
   protected string $foreignPivotKey; // Clave del modelo padre en la tabla pivote (ej: user_id)
   protected string $relatedPivotKey; // Clave del modelo relacionado en la tabla pivote (ej: role_id)

   public function __construct(Builder $query, Model $parent, string $pivotTable, string $foreignPivotKey, string $relatedPivotKey) {
      $this->pivotTable = $pivotTable;
      $this->foreignPivotKey = $foreignPivotKey;
      $this->relatedPivotKey = $relatedPivotKey;
      $this->relatedTable = $query->getModel()->getTable();

      parent::__construct($query, $parent);
   }

   protected function addConstraints(): void {
      $this->query->getQuery()->join(
         $this->pivotTable,
         $this->relatedTable . '.' . $this->query->getModel()->getKeyName(),
         '=',
         $this->pivotTable . '.' . $this->relatedPivotKey
      );

      $this->query->getQuery()->where(
         $this->pivotTable . '.' . $this->foreignPivotKey,
         '=',
         $this->parent->getKey()
      );
   }

   public function getResults() {
      return $this->query->get();
   }

   // --- Métodos de manipulación de la tabla pivote ---

   public function attach($id): void {
      $this->query->getQuery()->db->insert(
         "INSERT INTO {$this->pivotTable} ({$this->foreignPivotKey}, {$this->relatedPivotKey}) VALUES (?, ?)",
         [$this->parent->getKey(), $id]
      );
   }

   public function detach($id): void {
      $this->query->getQuery()->db->delete(
         "DELETE FROM {$this->pivotTable} WHERE {$this->foreignPivotKey} = ? AND {$this->relatedPivotKey} = ?",
         [$this->parent->getKey(), $id]
      );
   }

   public function sync(array $ids): array {
      // Lógica más compleja:
      // 1. Obtener los IDs actuales.
      // 2. Calcular qué IDs añadir (attach) y qué IDs quitar (detach).
      // 3. Ejecutar las operaciones.
      // Por ahora, lo dejamos como un stub.
      return ['attached' => [], 'detached' => []];
   }
}