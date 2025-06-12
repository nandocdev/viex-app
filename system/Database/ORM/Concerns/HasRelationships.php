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
use Phast\System\Database\ORM\Relationships\HasOne;

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

   // Aquí irían hasMany, belongsToMany, etc.
}