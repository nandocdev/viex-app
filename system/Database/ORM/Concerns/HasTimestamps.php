<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Model
 * @description Trait para gestionar created_at y updated_at
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Concerns;
trait HasTimestamps {
   /**
    * El nombre de la columna 'created at'.
    */
   public const CREATED_AT = 'created_at';

   /**
    * El nombre de la columna 'updated at'.
    */
   public const UPDATED_AT = 'updated_at';

   /**
    * Actualiza los timestamps del modelo antes de guardar.
    * Este método será llamado por `performInsert` y `performUpdate` en la clase Model.
    */
   protected function updateTimestamps(): void {
      $time = $this->freshTimestamp();

      // Si el modelo aún no existe, establecemos el created_at.
      if (!$this->exists && !isset($this->attributes[self::CREATED_AT])) {
         $this->setAttribute(self::CREATED_AT, $time);
      }

      // Siempre establecemos el updated_at al guardar.
      if (!isset($this->attributes[self::UPDATED_AT])) {
         $this->setAttribute(self::UPDATED_AT, $time);
      }
   }

   /**
    * Obtiene un nuevo string de timestamp.
    */
   protected function freshTimestamp(): string {
      return date('Y-m-d H:i:s');
   }
}