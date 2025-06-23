<?php
/**
 * @package     Phoenix/Query
 * @subpackage  Builder
 * @file        QueryBuilder.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 12:00:00
 * @version     1.0.0
 * @description Proporciona una API fluida para construir consultas SQL de forma agnóstica.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Query\Builder;

/**
 * Proporciona una API fluida para construir una representación abstracta de una consulta.
 *
 * Esta clase no genera SQL directamente. En su lugar, acumula el estado de una
 * consulta (la tabla, las columnas, las condiciones, etc.) en sus propiedades
as.
 * Luego, una clase Grammar utiliza este objeto para compilar la cadena SQL final.
 */
class QueryBuilder {
   // --- Componentes de la Consulta ---
   public string $from;
   public array $columns = ['*'];
   public array $joins = [];
   public array $wheres = [];
   public array $orders = [];
   public array $groups = [];
   public array $havings = [];
   public ?int $limit = null;
   public ?int $offset = null;

   /**
    * Almacena los valores de los parámetros para las sentencias preparadas.
    * Se separan por cláusula para garantizar el orden correcto durante la compilación.
    * @var array<string, array<int, mixed>>
    */
   public array $bindings = [
      'join' => [],
      'where' => [],
      'having' => [],
   ];

   /**
    * @param string $table El nombre de la tabla principal para la consulta.
    */
   public function __construct(string $table) {
      $this->from = $table;
   }

   /**
    * Establece las columnas que se seleccionarán.
    *
    * @param string ...$columns Lista de nombres de columnas.
    * @return $this
    */
   public function select(string ...$columns): self {
      $this->columns = $columns;
      return $this;
   }

   /**
    * Añade una condición "where" a la consulta.
    *
    * @param string $column La columna.
    * @param string $operator El operador ('=', '<', 'LIKE', etc.).
    * @param mixed $value El valor a comparar.
    * @param string $boolean El conector lógico ('AND', 'OR').
    * @return $this
    */
   public function where(string $column, string $operator, mixed $value, string $boolean = 'AND'): self {
      $this->wheres[] = [
         'type' => 'Basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => strtoupper($boolean),
      ];

      $this->bindings['where'][] = $value;
      return $this;
   }

   /**
    * Añade una condición "or where" a la consulta.
    *
    * @param string $column
    * @param string $operator
    * @param mixed $value
    * @return $this
    */
   public function orWhere(string $column, string $operator, mixed $value): self {
      return $this->where($column, $operator, $value, 'OR');
   }

   /**
    * Añade una condición "where in" a la consulta.
    *
    * @param string $column La columna.
    * @param array<mixed> $values Los valores del conjunto.
    * @param string $boolean El conector lógico ('AND', 'OR').
    * @param bool $not Si la condición debe ser 'NOT IN'.
    * @return $this
    */
   public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self {
      $this->wheres[] = [
         'type' => $not ? 'NotIn' : 'In',
         'column' => $column,
         'values' => $values,
         'boolean' => strtoupper($boolean),
      ];

      $this->bindings['where'] = array_merge($this->bindings['where'], $values);
      return $this;
   }

   /**
    * Añade una cláusula "join" a la consulta.
    *
    * @param string $table La tabla a unir.
    * @param string $first La primera columna de la condición de unión.
    * @param string $operator El operador de la condición.
    * @param string $second La segunda columna de la condición.
    * @param string $type El tipo de join ('INNER', 'LEFT', 'RIGHT').
    * @return $this
    */
   public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self {
      $this->joins[] = compact('type', 'table', 'first', 'operator', 'second');
      return $this;
   }

   /**
    * Añade una cláusula "left join".
    */
   public function leftJoin(string $table, string $first, string $operator, string $second): self {
      return $this->join($table, $first, $operator, $second, 'LEFT');
   }

   /**
    * Añade una cláusula de ordenación.
    */
   public function orderBy(string $column, string $direction = 'ASC'): self {
      $this->orders[] = [
         'column' => $column,
         'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
      ];
      return $this;
   }

   /**
    * Añade una cláusula de agrupación.
    */
   public function groupBy(string ...$columns): self {
      $this->groups = array_merge($this->groups, $columns);
      return $this;
   }

   /**
    * Establece el número máximo de registros a devolver.
    */
   public function limit(int $value): self {
      $this->limit = $value > 0 ? $value : null;
      return $this;
   }

   /**
    * Establece el número de registros a omitir.
    */
   public function offset(int $value): self {
      $this->offset = $value > 0 ? $value : null;
      return $this;
   }

   /**
    * Verifica si ya existe una condición WHERE para una columna específica.
    *
    * @param string $column
    * @return bool
    */
   public function hasWhere(string $column): bool {
      foreach ($this->wheres as $where) {
         if (
            isset($where['column']) &&
            $where['column'] === $column
         ) {
            return true;
         }
      }
      return false;
   }
}