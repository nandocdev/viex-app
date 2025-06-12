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
use Phast\System\Database\ORM\Relationships\Relation;

class Builder {
   /** El constructor de consultas genérico subyacente. */
   protected QueryBuilder $query;

   /** La instancia del modelo sobre la que se está construyendo la consulta. */
   protected Model $model;

   /** @var array Las relaciones a cargar de forma anticipada. */
   protected array $eagerLoad = [];

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
   // MODIFICA el método get()
   public function get(): Collection {
      $models = $this->query->get();
      $collection = $this->model->newCollection($models);

      if (!$collection->isEmpty() && !empty($this->eagerLoad)) {
         $this->loadEagerRelations($collection);
      }

      return $collection;
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

   /**
    * Establece las relaciones a cargar de forma anticipada.
    */
   public function with(string|array $relations): self {
      $this->eagerLoad = is_array($relations) ? $relations : func_get_args();
      return $this;
   }

   /**
    * Carga las relaciones de forma anticipada en una colección de modelos.
    */
   protected function loadEagerRelations(Collection $models): void {
      foreach ($this->eagerLoad as $name) {
         // Aquí iría la lógica de carga para cada relación.
         // Por ahora, asumimos que es una relación simple.
         $relation = $this->model->$name(); // Obtiene el objeto de la relación (ej: HasMany)
         $relation->addEagerConstraints($models->all());

         $results = $relation->get(); // Ejecuta la consulta para TODOS los modelos relacionados a la vez.

         // Ahora, une los resultados con sus modelos padres.
         $this->matchEagerlyLoaded($models, $results, $name, $relation);
      }
   }

   /**
    * Une los resultados del eager loading a sus modelos padres.
    */
   protected function matchEagerlyLoaded(Collection $models, Collection $results, string $relationName, Relation $relation): void {
      // Esta es la parte más compleja. Necesitamos agrupar los resultados
      // por la clave foránea para poder asignarlos eficientemente.
      $dictionary = $results->all(); // Aquí iría una lógica de agrupación más compleja.

      // Asigna los modelos relacionados a sus padres.
      // Lógica simplificada:
      foreach ($models as $model) {
         // Esto es ineficiente, una implementación real usaría un diccionario.
         $relatedItems = array_filter($dictionary, function ($related) use ($model, $relation) {
            // Lógica para comparar foreign key con local key
            return true; // Simplificado
         });

         $model->setRelation($relationName, $relation->match(new Collection($relatedItems), $model));
      }
   }
}