<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        Model
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:05:36
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Database;


use Phast\System\Database\Query\Builder;
use Phast\System\Routing\Exceptions\RouteNotFoundException; // Usaremos una excepción más apropiada
use Exception; // Excepción genérica por ahora
use Phast\System\Database\DB;
use Phast\System\Database\Connection;

abstract class Model {
   // --- PROPIEDADES CONFIGURABLES POR EL USUARIO ---

   protected string $table;
   protected string $primaryKey = 'id';
   protected bool $timestamps = true;
   protected array $fillable = [];
   protected array $guarded = ['id']; // Por defecto, el ID no se puede asignar masivamente

   // --- PROPIEDADES INTERNAS ---

   // Almacena los atributos originales del modelo cuando se cargó
   protected array $original = [];
   // Almacena los atributos actuales (modificados o no)
   protected array $attributes = [];
   // Indica si el modelo existe en la base de datos
   protected bool $exists = false;

   // --- CONSTRUCTOR Y MÉTODOS MÁGICOS ---

   public function __construct(array $attributes = []) {
      $this->fill($attributes);
   }

   public function __get(string $key) {
      return $this->attributes[$key] ?? null;
   }

   public function __set(string $key, $value): void {
      $this->attributes[$key] = $value;
   }

   public function __isset(string $key): bool {
      return isset($this->attributes[$key]);
   }

   // --- MÉTODOS DE INSTANCIA ---

   /**
    * Rellena el modelo con un array de atributos, respetando la asignación masiva.
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
    * Guarda el modelo en la base de datos (INSERT o UPDATE).
    */
   public function save(): bool {
      $query = $this->newQuery();

      if ($this->timestamps) {
         $this->updateTimestamps();
      }

      if ($this->exists) {
         // UPDATE
         if (empty($this->getDirty())) {
            return true; // No hay nada que actualizar
         }
         $query->where($this->primaryKey, '=', $this->getKey())->update($this->getDirty());
      } else {
         // INSERT
         $id = $query->insertGetId($this->attributes);
         if ($id) {
            $this->setAttribute($this->primaryKey, $id);
            $this->exists = true;
         }
      }

      $this->syncOriginal();
      return true;
   }

   /**
    * Actualiza el modelo con nuevos atributos y lo guarda inmediatamente.
    */
   public function update(array $attributes): bool {
      $this->fill($attributes);
      return $this->save();
   }

   /**
    * Elimina el modelo de la base de datos.
    */
   public function delete(): bool {
      if (!$this->exists) {
         return false;
      }
      return (bool) $this->newQuery()->where($this->primaryKey, '=', $this->getKey())->delete();
   }

   // --- MÉTODOS ESTÁTICOS (PUNTO DE ENTRADA A CONSULTAS) ---

   public static function all(): array {
      return static::newQuery()->get()->map(function ($item) {
         return (new static)->newFromBuilder($item);
      })->all(); // Asumiendo que `get()` devolverá una Colección en el futuro.
      // Por ahora, asumimos que devuelve un array.
   }

   public static function find(int|string $id): ?static {
      $result = static::newQuery()->find($id);
      return $result ? (new static)->newFromBuilder($result) : null;
   }

   public static function findOrFail(int|string $id): static {
      $model = static::find($id);
      if (!$model) {
         // Podrías crear una excepción ModelNotFoundException
         throw new Exception('Model not found.');
      }
      return $model;
   }

   public static function create(array $attributes): static {
      $model = new static();
      $model->fill($attributes);
      $model->save();
      return $model;
   }

   // --- MÉTODOS MÁGICOS ESTÁTICOS PARA EL BUILDER ---

   public static function __callStatic(string $method, array $parameters) {
      return (new static)->newQuery()->$method(...$parameters);
   }

   // --- MÉTODOS AUXILIARES ---

   /**
    * Crea una nueva instancia del Query Builder para este modelo.
    */
   public function newQuery(): Builder {
      return (new Builder(DB::connection()))->from($this->getTable());
   }

   /**
    * Crea una nueva instancia del modelo a partir de datos crudos del Builder.
    */
   public function newFromBuilder(object $attributes): static {
      $model = new static;
      $model->attributes = (array) $attributes;
      $model->original = (array) $attributes;
      $model->exists = true;
      return $model;
   }

   public function getTable(): string {
      if (isset($this->table)) {
         return $this->table;
      }
      // Inf_iere el nombre de la tabla del nombre de la clase (User -> users)
      $className = substr(strrchr(get_class($this), '\\'), 1);
      return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
   }

   public function getKey() {
      return $this->attributes[$this->primaryKey] ?? null;
   }

   protected function setAttribute(string $key, $value): void {
      $this->attributes[$key] = $value;
   }

   protected function isFillable(string $key): bool {
      if (!empty($this->fillable)) {
         return in_array($key, $this->fillable);
      }
      if (!empty($this->guarded)) {
         return !in_array($key, $this->guarded);
      }
      return true;
   }

   protected function updateTimestamps(): void {
      $time = date('Y-m-d H:i:s');
      if (!$this->exists) {
         $this->setAttribute('created_at', $time);
      }
      $this->setAttribute('updated_at', $time);
   }

   protected function getDirty(): array {
      $dirty = [];
      foreach ($this->attributes as $key => $value) {
         if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
            $dirty[$key] = $value;
         }
      }
      return $dirty;
   }

   protected function syncOriginal(): void {
      $this->original = $this->attributes;
   }
}