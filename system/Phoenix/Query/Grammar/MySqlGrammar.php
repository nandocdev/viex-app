<?php
/**
 * @package     Phoenix/Query
 * @subpackage  Grammar
 * @file        MySqlGrammar.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 12:30:00
 * @version     1.0.0
 * @description Implementa la gramática para compilar consultas en dialecto MySQL.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Query\Grammar;

use Phast\System\Phoenix\Query\Builder\QueryBuilder;

/**
 * Compila objetos QueryBuilder al dialecto SQL de MySQL.
 *
 * Responsable de la sintaxis específica de MySQL, como el entrecomillado de
 * identificadores con acentos graves (backticks).
 */
class MySqlGrammar implements GrammarInterface {
   /**
    * Componentes de una consulta SELECT en el orden de compilación.
    * @var string[]
    */
   protected array $selectComponents = [
      'columns', 'from', 'joins', 'wheres', 'groups', 'havings', 'orders', 'limit', 'offset',
   ];

   /**
    * {@inheritdoc}
    */
   public function compileSelect(QueryBuilder $builder): string {
      $sql = [];
      foreach ($this->selectComponents as $component) {
         if (!is_null($builder->{$component})) {
            $method = 'compile' . ucfirst($component);
            $sql[$component] = $this->{$method}($builder);
         }
      }
      return trim(implode(' ', array_filter($sql)));
   }

   /**
    * {@inheritdoc}
    */
   public function compileInsert(QueryBuilder $builder, array $values): string {
      $table = $this->wrapTable($builder->from);
      $columns = $this->wrapList(array_keys($values));
      $placeholders = $this->parameterize(array_values($values));
      return "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
   }

   /**
    * {@inheritdoc}
    */
   public function compileUpdate(QueryBuilder $builder, array $values): string {
      $table = $this->wrapTable($builder->from);
      $setClauses = [];
      foreach (array_keys($values) as $column) {
         $setClauses[] = $this->wrap($column) . ' = ?';
      }
      $set = implode(', ', $setClauses);
      $wheres = $this->compileWheres($builder);
      return trim("UPDATE {$table} SET {$set} {$wheres}");
   }

   /**
    * {@inheritdoc}
    */
   public function compileDelete(QueryBuilder $builder): string {
      $table = $this->wrapTable($builder->from);
      $wheres = $this->compileWheres($builder);
      return trim("DELETE FROM {$table} {$wheres}");
   }

   /**
    * {@inheritdoc}
    */
   public function getSelectBindings(QueryBuilder $builder): array {
      return array_merge(
         $builder->bindings['join'],
         $builder->bindings['where'],
         $builder->bindings['having']
      );
   }

   // --- Métodos de Compilación de Componentes ---

   protected function compileColumns(QueryBuilder $builder): string {
      return 'SELECT ' . $this->wrapList($builder->columns);
   }
   protected function compileFrom(QueryBuilder $builder): string {
      return 'FROM ' . $this->wrapTable($builder->from);
   }
   protected function compileLimit(QueryBuilder $builder): ?string {
      return $builder->limit ? 'LIMIT ' . $builder->limit : null;
   }
   protected function compileOffset(QueryBuilder $builder): ?string {
      return $builder->offset ? 'OFFSET ' . $builder->offset : null;
   }
   protected function compileGroups(QueryBuilder $builder): ?string {
      return !empty($builder->groups) ? 'GROUP BY ' . $this->wrapList($builder->groups) : null;
   }

   protected function compileJoins(QueryBuilder $builder): ?string {
      if (empty($builder->joins))
         return null;
      $sql = [];
      foreach ($builder->joins as $join) {
         $table = $this->wrapTable($join['table']);
         $sql[] = "{$join['type']} JOIN {$table} ON {$this->wrap($join['first'])} {$join['operator']} {$this->wrap($join['second'])}";
      }
      return implode(' ', $sql);
   }

   protected function compileWheres(QueryBuilder $builder): ?string {
      if (empty($builder->wheres))
         return null;
      $sql = [];
      foreach ($builder->wheres as $index => $where) {
         $prefix = ($index === 0) ? '' : ($where['boolean'] . ' ');
         $sql[] = $prefix . match ($where['type']) {
            'Basic' => $this->wrap($where['column']) . ' ' . $where['operator'] . ' ?',
            'In', 'NotIn' => $this->compileWhereIn($where),
            default => ''
         };
      }
      return 'WHERE ' . implode(' ', $sql);
   }

   private function compileWhereIn(array $where): string {
      $operator = $where['type'] === 'In' ? 'IN' : 'NOT IN';
      if (empty($where['values'])) {
         return ($operator === 'IN') ? '0=1' : '1=1'; // Lógica segura para IN ()
      }
      return $this->wrap($where['column']) . ' ' . $operator . ' (' . $this->parameterize($where['values']) . ')';
   }

   protected function compileOrders(QueryBuilder $builder): ?string {
      if (empty($builder->orders))
         return null;
      $sql = [];
      foreach ($builder->orders as $order) {
         $sql[] = $this->wrap($order['column']) . ' ' . $order['direction'];
      }
      return 'ORDER BY ' . implode(', ', $sql);
   }

   protected function compileHavings(QueryBuilder $builder): ?string {
      if (empty($builder->havings)) {
         return null;
      }
      $sql = [];
      foreach ($builder->havings as $index => $having) {
         $prefix = ($index === 0) ? '' : ($having['boolean'] . ' ');
         $sql[] = $prefix . match ($having['type']) {
            'Basic' => $this->wrap($having['column']) . ' ' . $having['operator'] . ' ?',
            'In', 'NotIn' => $this->wrap($having['column']) .
            ($having['type'] === 'In' ? ' IN ' : ' NOT IN ') .
            '(' . $this->parameterize($having['values']) . ')',
            default => ''
         };
      }
      return 'HAVING ' . implode(' ', $sql);
   }

   // --- Métodos de Utilidad Específicos de MySQL ---

   protected function wrap($value): string {
      if (str_contains($value, '.')) {
         return implode('.', array_map([$this, 'wrapValue'], explode('.', $value, 2)));
      }
      return $this->wrapValue($value);
   }

   protected function wrapValue(string $value): string {
      return $value !== '*' ? '`' . str_replace('`', '``', $value) . '`' : $value;
   }
   protected function wrapTable(string $table): string {
      return $this->wrap($table);
   }
   protected function wrapList(array $values): string {
      return implode(', ', array_map([$this, 'wrap'], $values));
   }
   protected function parameterize(array $values): string {
      return implode(', ', array_fill(0, count($values), '?'));
   }
}