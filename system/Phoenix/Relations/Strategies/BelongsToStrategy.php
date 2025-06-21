<?php
/**
 * @package     Phoenix/Relations
 * @subpackage  Strategies
 * @file        BelongsToStrategy.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 15:30:00
 * @version     1.0.0
 * @description Implementa la lógica para la relación "BelongsTo" (pertenece a).
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Relations\Strategies;

use Phast\System\Phoenix\Entity\Attributes\Table;
use Phast\System\Phoenix\Entity\EntityInterface;
use Phast\System\Phoenix\Query\Builder\QueryBuilder;
use Phast\System\Phoenix\Query\Director;
use RuntimeException;

/**
 * Estrategia para cargar una relación "BelongsTo" (pertenece a).
 *
 * Ejemplo: Una Publicación (Post) pertenece a un Usuario (User).
 * La tabla `posts` (modelo padre) tendría una columna `user_id` (foreignKey).
 */
final class BelongsToStrategy implements RelationStrategyInterface {
   /**
    * El nombre de la clase de la entidad relacionada.
    * @var class-string<EntityInterface>
    */
   private string $relatedEntity;

   /**
    * La clave foránea en la tabla del modelo actual (padre).
    * @var string
    */
   private string $foreignKey;

   /**
    * La clave en la tabla del modelo propietario (relacionado).
    * @var string
    */
   private string $ownerKey;

   /**
    * @param class-string<EntityInterface> $relatedEntity La clase del modelo propietario que se quiere cargar (ej: User::class).
    * @param string $foreignKey La columna en la tabla del modelo actual que contiene el ID del propietario (ej: 'user_id').
    * @param string $ownerKey La columna en la tabla del propietario a la que apunta la clave foránea (ej: 'id').
    */
   public function __construct(string $relatedEntity, string $foreignKey, string $ownerKey) {
      $this->relatedEntity = $relatedEntity;
      $this->foreignKey = $foreignKey;
      $this->ownerKey = $ownerKey;
   }

   /**
    * {@inheritdoc}
    *
    * @return EntityInterface|null Devuelve una única entidad o null si no se encuentra.
    */
   public function load(EntityInterface $parent, Director $director): ?EntityInterface {
      // 1. Obtener el valor de la clave foránea del modelo padre.
      $parentAttributes = $parent->getAttributes();
      $foreignKeyValue = $parentAttributes[$this->foreignKey] ?? null;

      if (is_null($foreignKeyValue)) {
         // Si la clave foránea es nula, no hay relación que cargar.
         return null;
      }

      // 2. Construir la consulta para el modelo propietario.
      $relatedTableName = $this->getTableNameForEntity($this->relatedEntity);
      $builder = new QueryBuilder($relatedTableName);

      // La condición principal: `users.id = posts.user_id`
      $builder->where($this->ownerKey, '=', $foreignKeyValue);

      // 3. Ejecutar la consulta a través del Director usando `first`.
      return $director->first($builder, $this->relatedEntity);
   }

   /**
    * Obtiene el nombre de la tabla para una clase de entidad dada.
    *
    * @param class-string<EntityInterface> $entityClass
    * @return string
    * @throws RuntimeException si la entidad no tiene el atributo #[Table].
    */
   private function getTableNameForEntity(string $entityClass): string {
      $reflection = new \ReflectionClass($entityClass);
      $attributes = $reflection->getAttributes(Table::class);

      if (empty($attributes)) {
         throw new RuntimeException("La entidad relacionada '{$entityClass}' debe tener el atributo #[Table].");
      }

      /** @var Table $tableAttribute */
      $tableAttribute = $attributes[0]->newInstance();
      return $tableAttribute->name;
   }
}