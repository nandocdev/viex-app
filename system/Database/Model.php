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
use Phast\System\Database\Relations\HasOne;
use Phast\System\Database\Relations\HasMany;
use Phast\System\Database\Relations\BelongsTo;
use Phast\System\Database\Relations\BelongsToMany;
use Phast\System\Database\Validation\ModelValidator;
use Phast\System\Database\Cache\ModelCache;
use Phast\System\Database\Events\ModelCreated;
use Phast\System\Database\Events\ModelUpdated;
use Phast\System\Database\Events\ModelDeleted;

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
      $this->validator = new ModelValidator($this);
      $this->validator->setRules($this->validationRules);
      $this->validator->setMessages($this->validationMessages);
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
    * Guarda el modelo con validación y eventos
    */
   public function save(): bool {
      // Validar antes de guardar
      if (!$this->validate()) {
         return false;
      }

      $isNew = !$this->exists;

      // Implementación original del save
      $query = $this->newQuery();

      if ($this->timestamps) {
         $this->updateTimestamps();
      }

      if ($this->exists) {
         // UPDATE
         if (empty($this->getDirty())) {
            return true; // No hay nada que actualizar
         }
         $affected = $query->where($this->primaryKey, '=', $this->getKey())->update($this->getDirty());
         $result = $affected > 0; // <-- Fuerza a booleano
      } else {
         // INSERT
         $id = $query->insertGetId($this->attributes);
         if ($id) {
            $this->setAttribute($this->primaryKey, $id);
            $this->exists = true;
         }
         $result = $id !== false;
      }

      $this->syncOriginal();

      // Disparar eventos si el guardado fue exitoso
      if ($result && $this->fireEvents) {
         if ($isNew) {
            $this->fireEvent(new ModelCreated($this, $this->attributes));
         } else {
            $this->fireEvent(new ModelUpdated($this, $this->attributes, $this->original));
         }

         // Invalidar cache
         if ($this->useCache) {
            ModelCache::invalidateModel($this);
         }
      }

      return $result;
   }

   /**
    * Actualiza el modelo con nuevos atributos y lo guarda inmediatamente.
    */
   public function update(array $attributes): bool {
      $this->fill($attributes);
      return $this->save();
   }

   /**
    * Elimina el modelo con eventos
    */
   public function delete(): bool {
      if (!$this->exists) {
         return false;
      }

      $result = (bool) $this->newQuery()->where($this->primaryKey, '=', $this->getKey())->delete();

      if ($result && $this->fireEvents) {
         $this->fireEvent(new ModelDeleted($this, $this->attributes));

         // Invalidar cache
         if ($this->useCache) {
            ModelCache::invalidateModel($this);
         }
      }

      return $result;
   }

   // --- MÉTODOS ESTÁTICOS (PUNTO DE ENTRADA A CONSULTAS) ---

   /**
    * Obtiene todos los modelos con cache
    */
   public static function all() {
      $model = new static();

      if (!$model->useCache) {
         $results = $model->newQuery()->get();
         return array_map(function ($item) {
            return (new static)->newFromBuilder($item);
         }, $results);
      }

      return ModelCache::cacheQuery(
         $model,
         'all',
         [],
         function () {
            $model = new static();
            $results = $model->newQuery()->get();
            return array_map(function ($item) {
               return (new static)->newFromBuilder($item);
            }, $results);
         },
         $model->cacheTtl
      );
   }

   /**
    * Busca un modelo por ID con cache
    */
   public static function find(int|string $id): ?static {
      $model = new static();

      if (!$model->useCache) {
         $result = $model->newQuery()->find($id);
         return $result ? (new static)->newFromBuilder($result) : null;
      }

      return ModelCache::cacheQuery(
         $model,
         'find',
         [$id],
         function () use ($id) {
            $model = new static();
            $result = $model->newQuery()->find($id);
            return $result ? (new static)->newFromBuilder($result) : null;
         },
         $model->cacheTtl
      );
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
      $model = new static();
      $result = $model->newQuery()->$method(...$parameters);

      // Si el resultado es un objeto stdClass (resultado de consulta), convertirlo a modelo
      if (is_object($result) && get_class($result) === 'stdClass') {
         return (new static)->newFromBuilder($result);
      }

      // Si es un array de objetos stdClass, convertirlos a modelos
      if (is_array($result)) {
         return array_map(function ($item) {
            if (is_object($item) && get_class($item) === 'stdClass') {
               return (new static)->newFromBuilder($item);
            }
            return $item;
         }, $result);
      }

      return $result;
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

   /**
    * Obtiene el nombre de la clave primaria
    */
   protected function getKeyName(): string {
      return $this->primaryKey;
   }

   /**
    * Obtiene el valor de la clave primaria
    */
   public function getKey() {
      return $this->getAttribute($this->getKeyName());
   }

   /**
    * Obtiene un atributo del modelo
    */
   public function getAttribute(string $key) {
      $value = $this->attributes[$key] ?? null;

      // Si existe un accesor, lo usa
      if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
         $value = $this->{'get' . ucfirst($key) . 'Attribute'}($value);
      }

      return $value;
   }

   /**
    * Establece un atributo del modelo
    */
   public function setAttribute(string $key, $value): void {
      // Si existe un mutador, lo usa
      if (method_exists($this, 'set' . ucfirst($key) . 'Attribute')) {
         $value = $this->{'set' . ucfirst($key) . 'Attribute'}($value);
      }

      $this->attributes[$key] = $value;
   }

   /**
    * Obtiene todos los atributos del modelo
    */
   public function getAttributes(): array {
      $attributes = [];

      foreach ($this->attributes as $key => $value) {
         $attributes[$key] = $this->getAttribute($key);
      }

      return $attributes;
   }

   /**
    * Establece múltiples atributos
    */
   public function setAttributes(array $attributes): void {
      foreach ($attributes as $key => $value) {
         $this->setAttribute($key, $value);
      }
   }

   /**
    * Verifica si un atributo existe
    */
   public function hasAttribute(string $key): bool {
      return array_key_exists($key, $this->attributes);
   }

   /**
    * Obtiene un atributo o un valor por defecto
    */
   public function getAttributeOrDefault(string $key, $default = '') {
      return $this->hasAttribute($key) ? $this->getAttribute($key) : $default;
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

   // --- MÉTODOS DE RELACIONES ---

   /**
    * Define una relación "uno a uno"
    */
   protected function hasOne(string $related, string $foreignKey = '', string $localKey = ''): HasOne {
      $foreignKey = $foreignKey ?: $this->getForeignKey();
      $localKey = $localKey ?: $this->getKeyName();

      return new HasOne(
         $this->newQuery(),
         $this,
         $related,
         $foreignKey,
         $localKey
      );
   }

   /**
    * Define una relación "uno a muchos"
    */
   protected function hasMany(string $related, string $foreignKey = '', string $localKey = ''): HasMany {
      $foreignKey = $foreignKey ?: $this->getForeignKey();
      $localKey = $localKey ?: $this->getKeyName();

      return new HasMany(
         $this->newQuery(),
         $this,
         $related,
         $foreignKey,
         $localKey
      );
   }

   /**
    * Define una relación "pertenece a"
    */
   protected function belongsTo(string $related, string $foreignKey = '', string $ownerKey = ''): BelongsTo {
      $foreignKey = $foreignKey ?: $this->getForeignKey();
      $ownerKey = $ownerKey ?: 'id';

      return new BelongsTo(
         $this->newQuery(),
         $this,
         $related,
         $foreignKey,
         $ownerKey
      );
   }

   /**
    * Define una relación "muchos a muchos"
    */
   protected function belongsToMany(string $related, string $table = '', string $foreignPivotKey = '', string $relatedPivotKey = ''): BelongsToMany {
      $table = $table ?: $this->joiningTable($related);
      $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
      $relatedPivotKey = $relatedPivotKey ?: $this->getRelatedForeignKey($related);

      return new BelongsToMany(
         $this->newQuery(),
         $this,
         $related,
         $table,
         $foreignPivotKey,
         $relatedPivotKey
      );
   }

   /**
    * Carga anticipada de relaciones
    */
   public static function with(string|array $relations): static {
      $model = new static();
      $model->eagerLoad = is_array($relations) ? $relations : [$relations];
      return $model;
   }

   /**
    * Obtiene el nombre de la clave foránea para este modelo
    */
   protected function getForeignKey(): string {
      return strtolower($this->class_basename($this)) . '_id';
   }

   /**
    * Obtiene el nombre de la clave foránea para el modelo relacionado
    */
   protected function getRelatedForeignKey(string $related): string {
      return strtolower($this->class_basename($related)) . '_id';
   }

   /**
    * Obtiene el nombre de la tabla de unión para relaciones muchos a muchos
    */
   protected function joiningTable(string $related): string {
      $models = [
         strtolower($this->class_basename($this)),
         strtolower($this->class_basename($related))
      ];
      sort($models);
      return implode('_', $models);
   }

   /**
    * Obtiene el nombre base de una clase
    */
   private function class_basename($class): string {
      $class = is_object($class) ? get_class($class) : $class;
      return basename(str_replace('\\', '/', $class));
   }

   // --- PROPIEDADES PARA RELACIONES ---
   protected array $eagerLoad = [];
   protected array $relations = [];

   // --- PROPIEDADES PARA VALIDACIÓN ---
   protected array $validationRules = [];
   protected array $validationMessages = [];
   protected ModelValidator $validator;

   // --- PROPIEDADES PARA CACHE ---
   protected bool $useCache = true;
   protected int $cacheTtl = 3600;

   // --- PROPIEDADES PARA EVENTOS ---
   protected bool $fireEvents = true;

   /**
    * Valida el modelo antes de guardar
    */
   public function validate(): bool {
      return $this->validator->validate();
   }

   /**
    * Obtiene los errores de validación
    */
   public function getValidationErrors(): array {
      return $this->validator->getErrors();
   }

   /**
    * Verifica si el modelo tiene errores de validación
    */
   public function hasValidationErrors(): bool {
      return $this->validator->hasErrors();
   }

   /**
    * Habilita o deshabilita el cache
    */
   public function useCache(bool $use = true): self {
      $this->useCache = $use;
      return $this;
   }

   /**
    * Establece el TTL del cache
    */
   public function setCacheTtl(int $ttl): self {
      $this->cacheTtl = $ttl;
      return $this;
   }

   /**
    * Habilita o deshabilita los eventos
    */
   public function fireEvents(bool $fire = true): self {
      $this->fireEvents = $fire;
      return $this;
   }

   /**
    * Dispara un evento del modelo
    */
   protected function fireEvent($event): void {
      // Aquí se puede integrar con un sistema de eventos global
      // Por ahora solo es un placeholder
   }
}
