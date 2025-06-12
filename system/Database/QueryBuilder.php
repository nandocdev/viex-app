<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        QueryBuilder
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-12
 * @version     1.0.0
 * @description Constructor de consultas SQL fluido, seguro y programático.
 */
declare(strict_types=1);

namespace Phast\System\Database;

class QueryBuilder {
   // --- ESTADO INTERNO DE LA CONSULTA ---
   protected string $from;
   protected array $columns = ['*'];
   protected array $wheres = [];
   protected array $joins = [];
   protected array $orders = [];
   protected array $groups = [];
   protected ?int $limit = null;
   protected ?int $offset = null;

   // Almacén centralizado para todos los bindings de PDO.
   protected array $bindings = [];

   /**
    * @param Database $db La instancia de la clase Database que ejecutará la consulta.
    */
   public function __construct(protected Database $db) {
   }

   // --- MÉTODOS DE CONSTRUCCIÓN (API FLUIDA) ---

   public function table(string $table): self {
      $this->from = $table;
      return $this;
   }

   public function select(string ...$columns): self {
      $this->columns = empty($columns) ? ['*'] : $columns;
      return $this;
   }

   public function where(string $column, string $operator, $value, string $boolean = 'AND'): self {
      $this->wheres[] = compact('column', 'operator', 'value', 'boolean');
      return $this;
   }

   public function orWhere(string $column, string $operator, $value): self {
      return $this->where($column, $operator, $value, 'OR');
   }

   public function orderBy(string $column, string $direction = 'ASC'): self {
      $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
      $this->orders[] = compact('column', 'direction');
      return $this;
   }

   public function groupBy(string ...$columns): self {
      $this->groups = array_merge($this->groups, $columns);
      return $this;
   }

   public function limit(int $number): self {
      $this->limit = $number;
      return $this;
   }

   public function offset(int $number): self {
      $this->offset = $number;
      return $this;
   }

   // --- MÉTODOS TERMINADORES (EJECUCIÓN) ---

   public function get(): array {
      $sql = $this->toSql();
      return $this->db->select($sql, $this->bindings);
   }

   public function first(): ?array {
      return $this->limit(1)->get()[0] ?? null;
   }

   public function find(int|string $id, string $primaryKey = 'id'): ?array {
      return $this->where($primaryKey, '=', $id)->first();
   }

   public function insert(array $data): bool {
      if (empty($data))
         return false;

      $sql = $this->compileInsert($data);
      // Para inserciones, los bindings son simplemente los valores del array.
      return $this->db->insert($sql, array_values($data));
   }

   public function update(array $data): int {
      if (empty($data))
         return 0;

      $sql = $this->compileUpdate($data);
      // Para actualizaciones, los bindings del "SET" van primero, luego los del "WHERE".
      $this->bindings = array_merge(array_values($data), $this->bindings);

      return $this->db->update($sql, $this->bindings);
   }

   public function delete(): int {
      $sql = $this->compileDelete();
      return $this->db->delete($sql, $this->bindings);
   }

   public function count(string $column = '*'): int {
      // Crea una nueva instancia del builder para no contaminar la consulta actual.
      $builder = clone $this;
      $builder->columns = ["COUNT({$this->wrap($column)}) as aggregate"];
      $result = $builder->first();

      return (int) ($result['aggregate'] ?? 0);
   }

   // --- COMPILADORES DE SQL ---

   public function toSql(): string {
      $this->bindings = []; // Resetea los bindings antes de compilar
      return trim(
         $this->compileSelect() .
         $this->compileFrom() .
         $this->compileJoins() .
         $this->compileWheres() .
         $this->compileGroups() .
         $this->compileOrders() .
         $this->compileLimit() .
         $this->compileOffset()
      );
   }

   protected function compileSelect(): string {
      return "SELECT " . implode(', ', array_map([$this, 'wrap'], $this->columns));
   }
   protected function compileFrom(): string {
      return " FROM " . $this->wrap($this->from);
   }
   protected function compileJoins(): string { /* Lógica para joins iría aquí */
      return '';
   }
   protected function compileGroups(): string {
      return !empty($this->groups) ? " GROUP BY " . implode(', ', array_map([$this, 'wrap'], $this->groups)) : '';
   }
   protected function compileOrders(): string {
      return !empty($this->orders) ? " ORDER BY " . implode(', ', array_map(fn($o) => $this->wrap($o['column']) . ' ' . $o['direction'], $this->orders)) : '';
   }
   protected function compileLimit(): string {
      return !is_null($this->limit) ? " LIMIT " . (int) $this->limit : '';
   }
   protected function compileOffset(): string {
      return !is_null($this->offset) ? " OFFSET " . (int) $this->offset : '';
   }

   protected function compileWheres(): string {
      if (empty($this->wheres))
         return '';

      $sqlParts = [];
      foreach ($this->wheres as $i => $where) {
         $prefix = ($i === 0) ? "WHERE" : $where['boolean'];
         $sqlParts[] = "{$prefix} {$this->wrap($where['column'])} {$where['operator']} ?";
         $this->bindings[] = $where['value'];
      }
      return " " . implode(' ', $sqlParts);
   }

   protected function compileInsert(array $data): string {
      $columns = implode(', ', array_map([$this, 'wrap'], array_keys($data)));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));
      return "INSERT INTO {$this->wrap($this->from)} ({$columns}) VALUES ({$placeholders})";
   }

   protected function compileUpdate(array $data): string {
      $set = implode(', ', array_map(fn($col) => "{$this->wrap($col)} = ?", array_keys($data)));
      return trim("UPDATE {$this->wrap($this->from)} SET {$set}" . $this->compileWheres());
   }

   protected function compileDelete(): string {
      return trim("DELETE FROM {$this->wrap($this->from)}" . $this->compileWheres());
   }

   /**
    * Envuelve un identificador (tabla, columna) con comillas inversas para MySQL.
    * Una implementación más avanzada tendría una clase "Grammar" para cada tipo de BBDD.
    */
   protected function wrap(string $value): string {
      if (stripos($value, ' as ') !== false) {
         $parts = preg_split('/\\s+as\\s+/i', $value);
         return $this->wrap($parts[0]) . ' AS ' . $this->wrap($parts[1]);
      }
      if (str_contains($value, '.')) {
         return implode('.', array_map([$this, 'wrap'], explode('.', $value)));
      }
      if ($value === '*')
         return '*';
      return "`" . str_replace('`', '``', $value) . "`";
   }
}