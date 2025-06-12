<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Model
 * @description Trait para gestionar relaciones (hasOne, belongsTo)
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Concerns;


use Phast\System\Database\ORM\Model;
use Phast\System\Database\ORM\Relationships\BelongsTo;
use Phast\System\Database\ORM\Relationships\BelongsToMany;
use Phast\System\Database\ORM\Relationships\HasOne;
use Phast\System\Database\ORM\Relationships\HasManyThrough;


trait HasRelationships {
   /**
    * Almacén para las relaciones ya cargadas, para evitar consultas duplicadas.
    * @var array
    */
   protected array $relations = [];

   /**
    * Define una relación de uno a uno (inversa).
    * Ejemplo: Un Post pertenece a un User.
    *
    * @param string $related El nombre de la clase del modelo relacionado.
    * @param string|null $foreignKey La clave foránea en la tabla actual.
    * @param string|null $ownerKey La clave primaria en la tabla del modelo "padre".
    */
   protected function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo {
      // La lógica completa se implementará en la clase BelongsTo.
      // Aquí solo instanciamos y devolvemos el objeto de la relación.

      // Convenciones:
      // Si $foreignKey es null, asume 'related_model_name_id'. Ej: user_id
      $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($related))->getShortName()) . '_id';

      // Si $ownerKey es null, asume la primary key del modelo relacionado.
      $instance = new $related;
      $ownerKey = $ownerKey ?? $instance->getKeyName();

      return new BelongsTo($instance->newQuery(), $this, $foreignKey, $ownerKey);
   }

   /**
    * Define una relación de uno a uno.
    * Ejemplo: Un User tiene un Phone.
    */
   protected function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne {
      // La lógica completa se implementará en la clase HasOne.
      $instance = new $related;
      $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
      $localKey = $localKey ?? $this->getKeyName();

      return new HasOne($instance->newQuery(), $this, $foreignKey, $localKey);
   }

   /**
    * Obtiene el valor de una relación. Si ya está cargada, la devuelve.
    * Si no, la carga desde la base de datos.
    */
   public function getRelationValue(string $key) {
      if (array_key_exists($key, $this->relations)) {
         return $this->relations[$key];
      }

      if (method_exists($this, $key)) {
         $relation = $this->$key();
         // ¡Importante! La relación se carga aquí al llamar a getResults()
         return $this->relations[$key] = $relation->getResults();
      }

      return null;
   }

   /**
    * Establece una relación cargada en el modelo. Usado por el Eager Loader.
    */
   public function setRelation(string $relation, $value): self {
      $this->relations[$relation] = $value;
      return $this;
   }

   public function setRelations(array $relations): self {
      foreach ($relations as $relation => $value) {
         $this->setRelation($relation, $value);
      }
      return $this;
   }

   protected function belongsToMany(string $related, ?string $pivotTable = null, ?string $foreignPivotKey = null, ?string $relatedPivotKey = null): BelongsToMany {
      $relatedInstance = new $related;

      // Convenciones
      $foreignPivotKey = $foreignPivotKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
      $relatedPivotKey = $relatedPivotKey ?? strtolower((new \ReflectionClass($related))->getShortName()) . '_id';

      if (is_null($pivotTable)) {
         $models = [
            strtolower((new \ReflectionClass($this))->getShortName()),
            strtolower((new \ReflectionClass($related))->getShortName())
         ];
         sort($models);
         $pivotTable = implode('_', $models);
      }

      return new BelongsToMany($relatedInstance->newQuery(), $this, $pivotTable, $foreignPivotKey, $relatedPivotKey);
   }

   protected function hasManyThrough(string $related, string $through, ?string $firstKey = null, ?string $secondKey = null): HasManyThrough {
      $relatedInstance = new $related;
      $throughInstance = new $through;

      // Convenciones
      $firstKey = $firstKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
      $secondKey = $secondKey ?? strtolower((new \ReflectionClass($through))->getShortName()) . '_id';

      return new HasManyThrough($relatedInstance->newQuery(), $this, $throughInstance, $firstKey, $secondKey);
   }
}