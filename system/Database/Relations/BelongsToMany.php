<?php

/**
 * @package     phast/system
 * @subpackage  Database/Relations
 * @file        BelongsToMany
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Relación "muchos a muchos"
 */

declare(strict_types=1);

namespace Phast\System\Database\Relations;

use Phast\System\Database\Model;

class BelongsToMany extends Relation
{
   protected string $table;
   protected string $foreignPivotKey;
   protected string $relatedPivotKey;

   public function __construct($query, Model $parent, string $related, string $table, string $foreignPivotKey, string $relatedPivotKey)
   {
      parent::__construct($query, $parent, $related, $foreignPivotKey, $relatedPivotKey);
      $this->table = $table;
      $this->foreignPivotKey = $foreignPivotKey;
      $this->relatedPivotKey = $relatedPivotKey;
   }

   public function getResults()
   {
      $related = new $this->related();
      $relatedTable = $related->getTable();

      return $this->query
         ->select($relatedTable . '.*')
         ->from($relatedTable)
         ->join($this->table, $relatedTable . '.id', '=', $this->table . '.' . $this->relatedPivotKey)
         ->where($this->table . '.' . $this->foreignPivotKey, '=', $this->parent->getKey())
         ->get();
   }

   /**
    * Adjunta modelos a la relación
    */
   public function attach($ids, array $attributes = []): void
   {
      $ids = is_array($ids) ? $ids : [$ids];

      foreach ($ids as $id) {
         $pivotData = array_merge($attributes, [
            $this->foreignPivotKey => $this->parent->getKey(),
            $this->relatedPivotKey => $id
         ]);

         $this->query->table($this->table)->insert($pivotData);
      }
   }

   /**
    * Desadjunta modelos de la relación
    */
   public function detach($ids = null): int
   {
      $query = $this->query->table($this->table)
         ->where($this->foreignPivotKey, '=', $this->parent->getKey());

      if ($ids !== null) {
         $ids = is_array($ids) ? $ids : [$ids];
         $query->whereIn($this->relatedPivotKey, $ids);
      }

      return $query->delete();
   }

   /**
    * Sincroniza los modelos adjuntos
    */
   public function sync($ids, bool $detaching = true): array
   {
      $ids = is_array($ids) ? $ids : [$ids];

      $current = $this->query->table($this->table)
         ->where($this->foreignPivotKey, '=', $this->parent->getKey())
         ->pluck($this->relatedPivotKey)
         ->toArray();

      $detached = [];
      $attached = [];
      $updated = [];

      if ($detaching) {
         $detached = array_diff($current, $ids);
         $this->detach($detached);
      }

      $attached = array_diff($ids, $current);
      $this->attach($attached);

      return [
         'attached' => $attached,
         'detached' => $detached,
         'updated' => $updated
      ];
   }
}
