<?php
/**
 * @package     Phoenix/Entity
 * @subpackage  
 * @file        AbstractEntity.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 15:00:00
 * @version     1.0.0
 * @description Clase base para todas las entidades, proporcionando funcionalidad común.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Entity;

use Phast\System\Phoenix\Relations\RelationManager;
use Phast\System\Phoenix\Relations\Strategies\HasManyStrategy;
use Phast\System\Phoenix\Relations\Strategies\RelationStrategyInterface;
use Phast\System\Phoenix\Query\Director;

/**
 * Clase base abstracta que implementa la funcionalidad común para todas las entidades.
 *
 * Proporciona gestión de estado (atributos, original, existe), acceso mágico a propiedades,
 * y un sistema robusto para definir y cargar relaciones de forma perezosa (lazy-loading).
 * Las entidades del usuario final deben extender esta clase.
 */
abstract class AbstractEntity implements EntityInterface {
   /**
    * Nombre de la clave primaria.
    * @var string
    */
   protected string $primaryKey = 'id';

   /**
    * Atributos actuales de la entidad.
    * @var array<string, mixed>
    */
   protected array $attributes = [];

   /**
    * Atributos originales de la entidad, tal como se cargaron de la DB.
    * @var array<string, mixed>
    */
   protected array $original = [];

   /**
    * Indica si la entidad existe en la base de datos.
    * @var bool
    */
   protected bool $exists = false;

   /**
    * Gestor de relaciones para esta instancia. Se carga de forma perezosa.
    * @var ?RelationManager
    */
   private ?RelationManager $relationManager = null;

   /**
    * El director de consultas, inyectado para poder resolver relaciones.
    * @var ?Director
    */
   private ?Director $director = null;

   /**
    * {@inheritdoc}
    */
   public static function newInstanceFromData(array $attributes, bool $isExisting = false): static {
      $entity = new static();
      $entity->attributes = $attributes;
      $entity->exists = $isExisting;

      if ($isExisting) {
         $entity->syncOriginal();
      }

      return $entity;
   }

   /**
    * Inyecta el director para que la entidad pueda resolver sus relaciones.
    * Este método es llamado por el propio Director después de hidratar una entidad.
    */
   public function setDirector(Director $director): void {
      $this->director = $director;
   }

   // --- Métodos Mágicos para Acceso a Propiedades y Relaciones ---

   public function __get(string $key) {
      // Prioridad 1: Atributos del modelo.
      if (array_key_exists($key, $this->attributes)) {
         return $this->attributes[$key];
      }

      // Prioridad 2: Relaciones definidas como métodos.
      if (method_exists($this, $key)) {
         return $this->getRelationValue($key);
      }

      return null;
   }

   public function __set(string $key, $value): void {
      $this->attributes[$key] = $value;
   }

   public function __isset(string $key): bool {
      return isset($this->attributes[$key]) || (method_exists($this, $key) && $this->getRelationValue($key) !== null);
   }

   // --- Implementación de EntityInterface ---

   public function getPrimaryKeyValue(): int|string|null {
      return $this->attributes[$this->primaryKey] ?? null;
   }

   public function getAttributes(): array {
      return $this->attributes;
   }

   public function isExisting(): bool {
      return $this->exists;
   }

   // --- Helpers para Definir Relaciones ---

   /**
    * Define una relación "HasMany" (uno a muchos).
    *
    * @param class-string<EntityInterface> $relatedEntity
    * @param string $foreignKey
    * @param ?string $localKey
    * @return RelationStrategyInterface
    */
   protected function hasMany(string $relatedEntity, string $foreignKey, ?string $localKey = null): RelationStrategyInterface {
      return new HasManyStrategy($relatedEntity, $foreignKey, $localKey ?? $this->primaryKey);
   }

   // Aquí irían otros helpers: belongsTo, hasOne, etc.

   // --- Lógica Interna ---

   /**
    * Carga y devuelve el valor de una relación.
    */
   private function getRelationValue(string $relationName) {
      // El método de la relación (ej. `posts()`) se usa para obtener la estrategia.
      $strategy = $this->{$relationName}();

      $manager = $this->getRelationManager();
      $manager->define($relationName, $strategy);

      return $manager->get($relationName);
   }

   /**
    * Obtiene (o crea perezosamente) el gestor de relaciones.
    */
   private function getRelationManager(): RelationManager {
      if ($this->relationManager === null) {
         if ($this->director === null) {
            throw new \RuntimeException('No se puede gestionar relaciones sin un Director inyectado.');
         }
         $this->relationManager = new RelationManager($this, $this->director);
      }
      return $this->relationManager;
   }

   /**
    * Sincroniza el estado 'original' con los atributos actuales.
    * Se llama después de cargar o guardar una entidad.
    */
   protected function syncOriginal(): void {
      $this->original = $this->attributes;
   }
}