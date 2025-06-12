<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Model
 * @description Trait para gestionar atributos (getters, setters, dirty)
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Concerns;

trait HasAttributes {
   /**
    * Almacén de los atributos originales del modelo, tal como existen en la BBDD.
    * @var array
    */
   protected array $original = [];

   /**
    * Almacén de los atributos actuales del modelo (incluye cambios).
    * @var array
    */
   protected array $attributes = [];

   /**
    * Indica si el modelo existe en la base de datos (fue cargado o guardado).
    * @var bool
    */
   public bool $exists = false;

   /**
    * Rellena el modelo con un array de atributos.
    * Respeta la propiedad `$fillable` para proteger contra la asignación masiva.
    */
   public function fill(array $attributes): self {
      foreach ($attributes as $key => $value) {
         if ($this->isFillable($key)) {
            $this->setAttribute($key, $value);
         }
      }
      return $this;
   }

   /**
    * Determina si un atributo se puede asignar masivamente.
    */
   protected function isFillable(string $key): bool {
      // Si $fillable está vacío, se asume que nada es fillable por seguridad.
      return in_array($key, $this->fillable);
   }

   /**
    * Obtiene un atributo del modelo.
    */
   public function getAttribute(string $key) {
      return $this->attributes[$key] ?? null;
   }

   /**
    * Establece el valor de un atributo en el modelo.
    */
   public function setAttribute(string $key, $value): self {
      $this->attributes[$key] = $value;
      return $this;
   }

   /**
    * Obtiene todos los atributos actuales del modelo.
    */
   public function getAttributes(): array {
      return $this->attributes;
   }

   /**
    * Obtiene los atributos que han cambiado desde la última sincronización.
    * Esto es crucial para las operaciones de UPDATE.
    */
   public function getDirty(): array {
      $dirty = [];
      foreach ($this->attributes as $key => $value) {
         if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
            $dirty[$key] = $value;
         }
      }
      return $dirty;
   }

   /**
    * Sincroniza los atributos actuales con los originales.
    * Se llama después de una operación de guardado exitosa.
    */
   public function syncOriginal(): self {
      $this->original = $this->attributes;
      return $this;
   }

   // --- MÉTODOS MÁGICOS PARA UNA API ELEGANTE ---

   /**
    * Permite acceder a los atributos como si fueran propiedades públicas.
    * Ejemplo: $user->name;
    */
   public function __get(string $key) {
      return $this->getAttribute($key);
   }

   /**
    * Permite establecer atributos como si fueran propiedades públicas.
    * Ejemplo: $user->name = 'Fernando';
    */
   public function __set(string $key, $value): void {
      $this->setAttribute($key, $value);
   }

   /**
    * Permite comprobar si un atributo está definido usando isset().
    * Ejemplo: isset($user->name);
    */
   public function __isset(string $key): bool {
      return isset($this->attributes[$key]);
   }
}