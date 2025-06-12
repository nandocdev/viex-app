<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Model
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-12
 * @version     1.0.0
 * @description Clase base abstracta para todos los modelos del ORM de Phast.
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM;

use JsonSerializable;
use Phast\System\Database\ORM\Concerns\HasAttributes;
use Phast\System\Database\ORM\Concerns\HasRelationships;
use Phast\System\Database\ORM\Concerns\HasTimestamps;
use Phast\System\Database\ORM\Concerns\HidesAttributes;

/**
 * @method static Builder where(string $column, string $operator, $value, string $boolean = 'AND')
 * @method static Model|null find(int|string $id)
 * @method static Model findOrFail(int|string $id)
 * @method static Collection get()
 * @method static Model|null first()
 * @method static int count()
 * @method static bool exists()
 */
abstract class Model implements JsonSerializable {
   // --- USO DE TRAITS PARA ORGANIZAR EL CÓDIGO ---
   use HasAttributes,
      HasRelationships,
      HasTimestamps,
      HidesAttributes;

   // --- PROPIEDADES DE CONFIGURACIÓN DEL MODELO ---

   /**
    * La tabla de la base de datos asociada con el modelo.
    * Si no se define, se inferirá a partir del nombre de la clase (ej: User -> users).
    * @var string
    */
   protected string $table;

   /**
    * La clave primaria de la tabla.
    * @var string
    */
   protected string $primaryKey = 'id';

   /**
    * Indica si el modelo tiene timestamps (created_at, updated_at) automáticos.
    * @var bool
    */
   public bool $timestamps = true;

   /**
    * Los atributos que se pueden asignar masivamente usando `fill()` o `create()`.
    * Es una medida de seguridad crucial.
    * @var array
    */
   protected array $fillable = [];


   // --- MÉTODOS PÚBLICOS (API DEL ORM) ---

   public function __construct(array $attributes = []) {
      $this->boot();
      $this->fill($attributes);
   }

   /**
    * Guarda el modelo en la base de datos (inserta si es nuevo, actualiza si existe).
    */
   public function save(): bool {
      $query = $this->newQuery();

      if ($this->exists) {
         // Lógica de actualización
         if (empty($this->getDirty())) {
            return true; // No hay nada que actualizar.
         }
         if ($this->performUpdate($query)) {
            $this->syncOriginal();
            return true;
         }
      } else {
         // Lógica de inserción
         if ($this->performInsert($query)) {
            $this->exists = true;
            $this->syncOriginal();
            return true;
         }
      }

      return false;
   }

   /**
    * Elimina el modelo de la base de datos.
    */
   public function delete(): bool {
      if (!$this->exists) {
         return false;
      }
      $query = $this->newQuery();
      return $query->where($this->primaryKey, '=', $this->getKey())->delete() > 0;
   }

   // --- MÉTODOS ESTÁTICOS PARA CONSULTAS ---

   /**
    * Inicia una nueva consulta para el modelo. Punto de partida para el Query Builder.
    */
   public static function query(): Builder {
      return (new static())->newQuery();
   }

   /**
    * Crea un nuevo modelo y lo guarda en la base de datos.
    */
   public static function create(array $attributes): static {
      $model = new static($attributes);
      $model->save();
      return $model;
   }

   /**
    * Permite llamar a métodos del Builder de forma estática, proporcionando una API elegante.
    * Ejemplo: User::where('active', 1)->get();
    */
   public static function __callStatic(string $method, array $args) {
      return self::query()->$method(...$args);
   }

   // --- MÉTODOS INTERNOS Y DE FÁBRICA ---

   /**
    * Inicializa el modelo, infiriendo el nombre de la tabla por convención si no está definido.
    */
   protected function boot(): void {
      if (!isset($this->table)) {
         $className = (new \ReflectionClass($this))->getShortName();
         // Convención: User -> users, PostCategory -> post_categories
         $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
      }
   }

   /**
    * Crea una nueva instancia del Query Builder del ORM para este modelo.
    */
   public function newQuery(): Builder {
      return new Builder($this);
   }

   /**
    * Ejecuta una operación de inserción en la base de datos.
    */
   protected function performInsert(Builder $query): bool {
      if ($this->timestamps) {
         $this->updateTimestamps();
      }

      $attributes = $this->getAttributesForInsert();
      $id = $query->insertGetId($attributes);

      if ($id) {
         $this->setAttribute($this->primaryKey, $id);
         return true;
      }
      return false;
   }

   /**
    * Ejecuta una operación de actualización en la base de datos.
    */
   protected function performUpdate(Builder $query): bool {
      if ($this->timestamps) {
         $this->updateTimestamps();
      }

      $dirty = $this->getDirty();
      if (empty($dirty)) {
         return true;
      }

      return $query->where($this->primaryKey, '=', $this->getKey())->update($dirty) > 0;
   }

   /**
    * Crea una nueva instancia del modelo a partir de un array de atributos crudos de la BBDD.
    * El modelo se marca como "existente" y se sincronizan los atributos originales.
    */
   public function newInstance(array $attributes = [], bool $exists = false): static {
      $model = new static;
      $model->attributes = $attributes;
      $model->exists = $exists;

      if ($exists) {
         $model->syncOriginal();
      }

      return $model;
   }

   /**
    * Crea una nueva instancia de Collection para este modelo.
    */
   public function newCollection(array $items = []): Collection {
      return new Collection($items);
   }

   /**
    * Obtiene los atributos que se insertarán en la base de datos.
    * Esto es útil para que en el futuro se puedan procesar antes de insertar.
    */
   protected function getAttributesForInsert(): array {
      return $this->attributes;
   }

   // --- Getters para el Builder y las Relaciones ---

   public function getTable(): string {
      return $this->table;
   }
   public function getKeyName(): string {
      return $this->primaryKey;
   }
   public function getKey() {
      return $this->getAttribute($this->primaryKey);
   }

   /**
    * Especifica cómo debe serializarse el objeto a JSON.
    * Es requerido por la interfaz `JsonSerializable`.
    */
   public function jsonSerialize(): array {
      return $this->toArray();
   }
}