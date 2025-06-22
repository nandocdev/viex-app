<?php
/**
 * @package     system/Database
 * @subpackage  Query
 * @file        Builder
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:01:53
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);

namespace Phast\System\Database\Query;

use PDO;

class Builder {
   // --- PROPIEDADES INTERNAS ---
   protected PDO $pdo;
   protected string $from;
   protected array $columns = ['*'];
   protected array $joins = [];
   protected array $wheres = [];
   protected array $groups = [];
   protected array $havings = [];
   protected array $orders = [];
   protected ?int $limit = null;
   protected ?int $offset = null;

   // Almacenamos los bindings en arrays separados para facilitar la compilación
   protected array $bindings = [
      'select' => [],
      'join' => [],
      'where' => [],
      'having' => [],
   ];

   // --- CONSTRUCTOR ---
   public function __construct(PDO $pdo) {
      $this->pdo = $pdo;
   }

   // --- MÉTODOS FLUIDOS ---

   public function from(string $table): self {
      $this->from = $table;
      return $this;
   }

   public function select(string ...$columns): self {
      $this->columns = $columns;
      return $this;
   }

   public function addSelect(string $column): self {
      // Si las columnas actuales son '*', las reemplazamos. Si no, añadimos.
      if ($this->columns === ['*']) {
         $this->columns = [];
      }
      $this->columns[] = $column;
      return $this;
   }

   public function selectRaw(string $expression, array $bindings = []): self {
      $this->addSelect($expression);
      $this->bindings['select'] = array_merge($this->bindings['select'], $bindings);
      return $this;
   }

   public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self {
      $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
      return $this;
   }

   public function leftJoin(string $table, string $first, string $operator, string $second): self {
      return $this->join($table, $first, $operator, $second, 'LEFT');
   }

   public function rightJoin(string $table, string $first, string $operator, string $second): self {
      return $this->join($table, $first, $operator, $second, 'RIGHT');
   }

   public function where(string $column, string $operator, mixed $value, string $boolean = 'AND'): self {
      $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
      // Solo añadimos el binding si el valor no es un nombre de columna (para joins)
      // Por ahora, lo añadimos siempre. Se puede refinar más tarde.
      $this->bindings['where'][] = $value;
      return $this;
   }

   public function orWhere(string $column, string $operator, mixed $value): self {
      return $this->where($column, $operator, $value, 'OR');
   }

   public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self {
      $type = $not ? 'NotIn' : 'In';
      $this->wheres[] = compact('column', 'type', 'values', 'boolean');
      $this->bindings['where'] = array_merge($this->bindings['where'], $values);
      return $this;
   }

   public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self {
      $type = $not ? 'NotNull' : 'Null';
      $this->wheres[] = compact('column', 'type', 'boolean');
      return $this;
   }

   public function groupBy(string ...$columns): self {
      $this->groups = array_merge($this->groups, $columns);
      return $this;
   }

   public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): self {
      $this->havings[] = compact('column', 'operator', 'value', 'boolean');
      $this->bindings['having'][] = $value;
      return $this;
   }

   public function orderBy(string $column, string $direction = 'ASC'): self {
      $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
      $this->orders[] = compact('column', 'direction');
      return $this;
   }

   public function latest(string $column = 'created_at'): self {
      return $this->orderBy($column, 'DESC');
   }

   public function oldest(string $column = 'created_at'): self {
      return $this->orderBy($column, 'ASC');
   }

   public function limit(int $value): self {
      $this->limit = $value;
      return $this;
   }

   public function offset(int $value): self {
      $this->offset = $value;
      return $this;
   }

   // --- MÉTODOS TERMINALES (GETTERS) ---

   public function get(): array|object {
      $sql = $this->toSql();
      $statement = $this->pdo->prepare($sql);
      $statement->execute($this->getBindings());
      return $statement->fetchAll(PDO::FETCH_OBJ);
   }

   public function first(): ?object {
      return $this->limit(1)->get()[0] ?? null;
   }

   public function find(int|string $id, string $primaryKey = 'id'): ?object {
      return $this->where($primaryKey, '=', $id)->first();
   }

   public function value(string $column) {
      $this->columns = [$column];
      $result = (array) $this->first();
      return $result[$column] ?? null;
   }

   public function exists(): bool {
      $this->columns = ['1'];
      return (bool) $this->first();
   }

   // --- MÉTODOS TERMINALES (AGREGADOS) ---

   protected function aggregate(string $function, string $column = '*'): mixed {
      $this->columns = [sprintf('%s(%s) as aggregate', $function, $column)];
      $result = (array) $this->first();
      // Limpiamos las columnas para no afectar futuras consultas con el mismo builder
      $this->columns = ['*'];
      return $result['aggregate'] ?? 0;
   }

   public function count(string $column = '*'): int {
      return (int) $this->aggregate('COUNT', $column);
   }

   public function sum(string $column): float {
      return (float) $this->aggregate('SUM', $column);
   }

   // ... (avg, min, max seguirían el mismo patrón) ...

   // --- MÉTODOS TERMINALES (MODIFICADORES) ---

   public function insert(array $data): bool {
      // VULNERABILIDAD: array_keys($data) se concatena directamente.
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      $sql = "INSERT INTO {$this->from} ({$columns}) VALUES ({$placeholders})";

      $statement = $this->pdo->prepare($sql);
      return $statement->execute(array_values($data));
   }

   public function insertGetId(array $data): int|string {
      if ($this->insert($data)) {
         return $this->pdo->lastInsertId();
      }
      return 0;
   }

   public function update(array $data): int {
      $setClauses = [];
      foreach (array_keys($data) as $column) {
         // VULNERABILIDAD: $column se concatena directamente.
         $setClauses[] = "{$column} = ?";
      }
      $setClause = implode(', ', $setClauses);

      $sql = "UPDATE {$this->from} SET {$setClause}" . $this->compileWheres();
      $bindings = array_merge(array_values($data), $this->bindings['where']);

      $statement = $this->pdo->prepare($sql);
      $statement->execute($bindings);
      return $statement->rowCount();
   }

   public function delete(): int {
      $sql = "DELETE FROM {$this->from}" . $this->compileWheres();
      $statement = $this->pdo->prepare($sql);
      $statement->execute($this->bindings['where']);
      return $statement->rowCount();
   }

   public function paginate(int $perPage = 15, int $page = 1): array {
      $page = max(1, $page);
      $total = (clone $this)->count();
      $results = $this->limit($perPage)->offset(($page - 1) * $perPage)->get();

      return [
         'total' => $total,
         'per_page' => $perPage,
         'current_page' => $page,
         'last_page' => (int) ceil($total / $perPage),
         'data' => $results,
      ];
   }

   // --- LÓGICA DE COMPILACIÓN DE SQL ---

   public function toSql(): string {
      $sql = "SELECT " . implode(', ', $this->columns) . " FROM " . $this->from;

      $sql .= $this->compileJoins();
      $sql .= $this->compileWheres();
      $sql .= $this->compileGroups();
      $sql .= $this->compileHavings();
      $sql .= $this->compileOrders();

      if ($this->limit) {
         $sql .= " LIMIT " . $this->limit;
      }
      if ($this->offset) {
         $sql .= " OFFSET " . $this->offset;
      }

      return $sql;
   }

   public function getBindings(): array {
      // Flatten y retorna todos los bindings en el orden correcto
      return array_merge(
         $this->bindings['select'],
         $this->bindings['join'],
         $this->bindings['where'],
         $this->bindings['having']
      );
   }

   protected function compileJoins(): string {
      $sql = '';
      foreach ($this->joins as $join) {
         $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
      }
      return $sql;
   }

   protected function compileWheres(): string {
      if (empty($this->wheres))
         return '';

      $sql = " WHERE ";
      $first = true;
      foreach ($this->wheres as $where) {
         if (!$first) {
            $sql .= " {$where['boolean']} ";
         }
         $first = false;

         if (isset($where['type'])) {
            // Para whereIn, whereNull, etc.
            switch ($where['type']) {
               case 'In':
                  $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                  $sql .= "{$where['column']} IN ({$placeholders})";
                  break;
               case 'NotIn':
                  $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                  $sql .= "{$where['column']} NOT IN ({$placeholders})";
                  break;
               case 'Null':
                  $sql .= "{$where['column']} IS NULL";
                  break;
               case 'NotNull':
                  $sql .= "{$where['column']} IS NOT NULL";
                  break;
            }
         } else {
            // Para where simple
            $sql .= "{$where['column']} {$where['operator']} ?";
         }
      }
      return $sql;
   }

   protected function compileGroups(): string {
      if (empty($this->groups))
         return '';
      return " GROUP BY " . implode(', ', $this->groups);
   }

   protected function compileHavings(): string {
      if (empty($this->havings))
         return '';
      // Esta es una implementación simplificada. La lógica real sería muy similar a compileWheres.
      $sql = " HAVING ";
      foreach ($this->havings as $i => $having) {
         $sql .= ($i > 0 ? " {$having['boolean']} " : "") . "{$having['column']} {$having['operator']} ?";
      }
      return $sql;
   }

   protected function compileOrders(): string {
      if (empty($this->orders))
         return '';
      $sql = " ORDER BY ";
      $parts = [];
      foreach ($this->orders as $order) {
         $parts[] = "{$order['column']} {$order['direction']}";
      }
      return $sql . implode(', ', $parts);
   }
}