<?php
/**
 * @package     Phoenix/Relations
 * @subpackage  Strategies
 * @file        HasManyStrategy.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 14:15:00
 * @version     1.0.0
 * @description Implementa la lógica para la relación "HasMany" (uno a muchos).
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Relations\Strategies;

use Phast\System\Phoenix\Entity\Attributes\Table;
use Phast\System\Phoenix\Entity\EntityInterface;
use Phast\System\Phoenix\Query\Builder\QueryBuilder;
use Phast\System\Phoenix\Query\Director;
use RuntimeException;

/**
 * Estrategia para cargar una relación "HasMany" (uno a muchos).
 *
 * Ejemplo: Un Usuario (User) tiene muchas Publicaciones (Post).
 * La tabla `posts` tendría una columna `user_id` (foreignKey).
 */
final class HasManyStrategy implements RelationStrategyInterface {
   /**
    * El nombre de la clase de la entidad relacionada.
    * @var class-string<EntityInterface>
    */
   private string $relatedEntity;

   /**
    * La clave foránea en la tabla del modelo relacionado.
    * @var string
    */
   private string $foreignKey;

   /**
    * La clave local (normalmente la PK) en la tabla del modelo padre.
    * @var string
    */
   private string $localKey;

   /**
    * @param class-string<EntityInterface> $relatedEntity La clase del modelo que se quiere cargar (ej: Post::class).
    * @param string $foreignKey La columna en la tabla relacionada que apunta al padre (ej: 'user_id').
    * @param string $localKey La columna en la tabla del padre a la que apunta la clave foránea (ej: 'id').
    */
   public function __construct(string $relatedEntity, string $foreignKey, string $localKey) {
      $this->relatedEntity = $relatedEntity;
      $this->foreignKey = $foreignKey;
      $this->localKey = $localKey;
   }

   /**
    * {@inheritdoc}
    *
    * @return array<int, EntityInterface> Siempre devuelve un array de entidades.
    */
   public function load(EntityInterface $parent, Director $director): array {
      // 1. Obtener el valor de la clave local del modelo padre.
      $parentAttributes = $parent->getAttributes();
      $localKeyValue = $parentAttributes[$this->localKey] ?? null;

      if (is_null($localKeyValue)) {
         // No se puede cargar la relación si la clave del padre no está definida.
         return [];
      }

      // 2. Construir la consulta para los modelos relacionados.
      $relatedTableName = $this->getTableNameForEntity($this->relatedEntity);
      $builder = new QueryBuilder($relatedTableName);

      // La condición principal: `posts.user_id = users.id`
      $builder->where($this->foreignKey, '=', $localKeyValue);

      // 3. Ejecutar la consulta a través del Director.
      return $director->get($builder, $this->relatedEntity);
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