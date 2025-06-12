<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        Builder
 * @description Query Builder específico del ORM para trabajar con Modelos.
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM;

use Phast\System\Database\QueryBuilder;
use Phast\System\Database\Facades\DB;
use Phast\System\Database\ORM\Exceptions\ModelNotFoundException;

class Builder {
   /** El constructor de consultas genérico subyacente. */
   protected QueryBuilder $query;

   /** La instancia del modelo sobre la que se está construyendo la consulta. */
   protected Model $model;

   public function __construct(Model $model) {
      $this->model = $model;
      // Inicia el QueryBuilder genérico apuntando a la tabla del modelo.
      $this->query = DB::table($this->model->getTable());
   }

   /**
    * Pasa las llamadas a métodos que no existen en esta clase
    * directamente al QueryBuilder genérico subyacente.
    */
   public function __call(string $method, array $args) {
      $result = $this->query->$method(...$args);

      // Si el método devuelve el QueryBuilder genérico, devolvemos `$this`
      // para mantener la fluidez en el contexto del ORM Builder.
      if ($result instanceof QueryBuilder) {
         return $this;
      }

      return $result;
   }

   /**
    * Ejecuta la consulta y devuelve una colección de modelos.
    */
   public function get(): Collection {
      $results = $this->query->get();
      return $this->model->newCollection($results);
   }

   /**
    * Encuentra un modelo por su clave primaria.
    * @throws ModelNotFoundException si el modelo no se encuentra.
    */
   public function findOrFail(int|string $id): Model {
      $model = $this->find($id);
      if (!$model) {
         throw new ModelNotFoundException("No query results for model [" . get_class($this->model) . "] {$id}");
      }
      return $model;
   }

   /**
    * Ejecuta la consulta y devuelve la primera fila como un modelo.
    */
   public function first(): ?Model {
      $result = $this->query->first();
      return $result ? $this->model->newInstance($result, true) : null;
   }

   /**
    * Inserta un nuevo registro y devuelve el ID.
    */
   public function insertGetId(array $values): int|string {
      if ($this->query->insert($values)) {
         return DB::lastInsertId();
      }
      return 0;
   }

   /**
    * Hidrata un array de resultados en una colección de modelos.
    *
    * @param array $items Array de arrays asociativos de la BBDD.
    * @return Collection
    */
   public function hydrate(array $items): Collection {
      $instances = array_map(function ($item) {
         return $this->model->newInstance($item, true);
      }, $items);

      return new Collection($instances);
   }

   // --- Métodos de acceso al modelo y al query ---
   public function getModel(): Model {
      return $this->model;
   }

   public function getQuery(): QueryBuilder {
      return $this->query;
   }
}