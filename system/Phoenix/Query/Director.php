<?php
/**
 * @package     Phoenix/Query
 * @subpackage  
 * @file        Director.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 13:00:00
 * @version     1.0.0
 * @description Orquesta el proceso completo de ejecución de consultas.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Query;

use Phast\System\Phoenix\Core\Connection\Adapter\AdapterInterface;
use Phast\System\Phoenix\Entity\EntityInterface;
use Phast\System\Phoenix\Entity\Hydrator\HydratorInterface;
use Phast\System\Phoenix\Query\Builder\QueryBuilder;
use Phast\System\Phoenix\Query\Grammar\GrammarInterface;

/**
 * Orquesta el ciclo de vida completo de una consulta de base de datos.
 *
 * El Director actúa como un punto de entrada de alto nivel para ejecutar
 * operaciones. Coordina los componentes de bajo nivel (Builder, Grammar,
 * Adapter, Hydrator) para realizar una tarea completa, como buscar un
 * registro y devolver una entidad hidratada.
 */
final class Director {
   private AdapterInterface $adapter;
   private GrammarInterface $grammar;
   private HydratorInterface $hydrator;

   /**
    * @param AdapterInterface $adapter El adaptador para comunicarse con la DB.
    * @param GrammarInterface $grammar El compilador para el dialecto SQL específico.
    * @param HydratorInterface $hydrator El servicio para mapear datos a objetos.
    */
   public function __construct(
      AdapterInterface $adapter,
      GrammarInterface $grammar,
      HydratorInterface $hydrator
   ) {
      $this->adapter = $adapter;
      $this->grammar = $grammar;
      $this->hydrator = $hydrator;
   }

   /**
    * Busca y devuelve una colección de entidades que coinciden con la consulta.
    *
    * @template T of EntityInterface
    * @param QueryBuilder $builder El plano de la consulta.
    * @param class-string<T> $entityClass La clase de la entidad a hidratar.
    * @return array<T> Un array de objetos de entidad.
    */
   public function get(QueryBuilder $builder, string $entityClass): array {
      $sql = $this->grammar->compileSelect($builder);
      $bindings = $this->grammar->getSelectBindings($builder);

      $results = $this->adapter->query($sql, $bindings);

      $entities = [];
      foreach ($results as $row) {
         $entities[] = $this->hydrator->hydrate($row, $entityClass);
      }

      return $entities;
   }

   /**
    * Busca y devuelve la primera entidad que coincide con la consulta.
    *
    * @template T of EntityInterface
    * @param QueryBuilder $builder El plano de la consulta.
    * @param class-string<T> $entityClass La clase de la entidad a hidratar.
    * @return ?T La entidad encontrada o null si no hay resultados.
    */
   public function first(QueryBuilder $builder, string $entityClass): ?EntityInterface {
      $builder->limit(1);

      $results = $this->get($builder, $entityClass);

      return $results[0] ?? null;
   }

   /**
    * Ejecuta una consulta SELECT nativa y devuelve una colección de entidades hidratadas.
    *
    * @template T of EntityInterface
    * @param string $sql La consulta SQL nativa.
    * @param array<int|string, mixed> $bindings Los bindings para la consulta.
    * @param class-string<T> $entityClass La clase de la entidad a hidratar.
    * @return array<T> Un array de objetos de entidad.
    */
   public function selectRaw(string $sql, array $bindings, string $entityClass): array {
      $results = $this->adapter->rawQuery($sql, $bindings);

      $entities = [];
      foreach ($results as $row) {
         // Reutilizamos el hydrator, ¡aquí está la magia!
         $entities[] = $this->hydrator->hydrate($row, $entityClass);
      }

      return $entities;
   }

   /**
    * Ejecuta una consulta SELECT nativa y devuelve la primera entidad hidratada.
    *
    * @template T of EntityInterface
    * @param string $sql La consulta SQL nativa.
    * @param array<int|string, mixed> $bindings Los bindings para la consulta.
    * @param class-string<T> $entityClass La clase de la entidad a hidratar.
    * @return ?T La entidad encontrada o null.
    */
   public function firstRaw(string $sql, array $bindings, string $entityClass): ?EntityInterface {
      // Podemos añadir "LIMIT 1" si no está presente para optimizar,
      // pero por simplicidad, lo dejamos así por ahora.
      $results = $this->selectRaw($sql, $bindings, $entityClass);

      return $results[0] ?? null;
   }


   /**
    * Ejecuta una sentencia SQL nativa (INSERT, UPDATE, DELETE).
    *
    * @param string $sql La sentencia SQL nativa.
    * @param array<int|string, mixed> $bindings Los bindings para la sentencia.
    * @return int El número de filas afectadas.
    */
   public function statement(string $sql, array $bindings = []): int {
      return $this->adapter->rawExecute($sql, $bindings);
   }

   /**
    * Inserta un nuevo registro en la base de datos a partir de una entidad.
    *
    * @param EntityInterface $entity La entidad a insertar.
    * @return int|string El ID del nuevo registro insertado.
    */
   public function insert(EntityInterface $entity): int|string {
      $builder = $this->createBuilderForEntity($entity);
      $values = $this->hydrator->dehydrate($entity);

      // Excluir la PK si es nula (para auto-incrementales)
      $primaryKeyColumn = $this->getPrimaryKeyColumnName($entity); // Implementar lógica de metadatos
      if (array_key_exists($primaryKeyColumn, $values) && is_null($values[$primaryKeyColumn])) {
         unset($values[$primaryKeyColumn]);
      }

      $sql = $this->grammar->compileInsert($builder, $values);
      $this->adapter->execute($sql, array_values($values));

      return $this->adapter->getLastInsertId();
   }

   /**
    * Actualiza un registro en la base de datos a partir de una entidad existente.
    *
    * @param EntityInterface $entity La entidad con los datos actualizados.
    * @return int El número de filas afectadas.
    */
   public function update(EntityInterface $entity): int {
      $builder = $this->createBuilderForEntity($entity);
      $values = $this->hydrator->dehydrate($entity);

      // Suponemos que el builder ya tiene los WHERE necesarios (por PK)
      $primaryKeyColumn = $this->getPrimaryKeyColumnName($entity);
      $primaryKeyValue = $entity->getPrimaryKeyValue();

      // Añadimos el WHERE por PK si no existe
      if (!$builder->hasWhere($primaryKeyColumn)) {
         $builder->where($primaryKeyColumn, '=', $primaryKeyValue);
      }

      $sql = $this->grammar->compileUpdate($builder, $values);

      // Combina los bindings del SET y del WHERE
      $whereBindings = $this->grammar->getSelectBindings($builder);
      $updateBindings = array_merge(array_values($values), $whereBindings);

      return $this->adapter->execute($sql, $updateBindings);
   }

   // Aquí irían los métodos delete(), etc. que siguen un patrón similar.

   /**
    * Crea un QueryBuilder básico para una entidad dada.
    *
    * @param EntityInterface $entity
    * @return QueryBuilder
    */
   private function createBuilderForEntity(EntityInterface $entity): QueryBuilder {
      // Esta lógica necesitará acceso a los metadatos de la entidad
      // (nombre de la tabla) que podríamos obtener a través del Hydrator o un
      // servicio de metadatos dedicado. Por ahora, se simplifica.
      $reflection = new \ReflectionClass($entity);
      $tableAttribute = $reflection->getAttributes(\Phast\System\Phoenix\Entity\Attributes\Table::class)[0] ?? null;

      if (!$tableAttribute) {
         throw new \RuntimeException("La entidad " . get_class($entity) . " no tiene el atributo #[Table].");
      }

      $tableName = $tableAttribute->newInstance()->name;

      return new QueryBuilder($tableName);
   }

   /**
    * Obtiene el nombre de la columna de la clave primaria para una entidad.
    *
    * @param EntityInterface $entity
    * @return string
    */
   private function getPrimaryKeyColumnName(EntityInterface $entity): string {
      // Lógica similar a la anterior para obtener metadatos de la PK.
      // Se omite por brevedad. Asumimos 'id' por ahora.
      return 'id';
   }
}