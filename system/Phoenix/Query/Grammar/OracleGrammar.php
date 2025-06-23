<?php
/**
 * @package     Phoenix/Query
 * @subpackage  Grammar
 * @file        OracleGrammar
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-23 16:31:59
 * @version     1.0.0
 * @description Gramática SQL para Oracle, compatible con QueryBuilder de Phoenix.
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Query\Grammar;

use Phast\System\Phoenix\Query\Builder\QueryBuilder;

class OracleGrammar implements GrammarInterface {
   public function compileSelect(QueryBuilder $builder): string {
      $sql = [
         $this->compileColumns($builder),
         $this->compileFrom($builder),
         $this->compileJoins($builder),
         $this->compileWheres($builder),
         $this->compileGroups($builder),
         $this->compileHavings($builder),
         $this->compileOrders($builder),
      ];

      // Oracle: LIMIT/OFFSET se maneja con FETCH/NEXT ROWS
      $limitOffset = $this->compileLimitOffset($builder);
      if ($limitOffset) {
         $sql[] = $limitOffset;
      }

      return implode(' ', array_filter($sql));
   }

   public function compileInsert(QueryBuilder $builder, array $values): string {
      $table = $this->wrapTable($builder->from);
      $columns = $this->wrapList(array_keys($values));
      $params = $this->parameterize($values);

      return "INSERT INTO {$table} ({$columns}) VALUES ({$params})";
   }

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

   public function compileDelete(QueryBuilder $builder): string {
      $table = $this->wrapTable($builder->from);
      $wheres = $this->compileWheres($builder);

      return trim("DELETE FROM {$table} {$wheres}");
   }

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

   protected function compileLimitOffset(QueryBuilder $builder): ?string {
      // Oracle 12c+: FETCH FIRST n ROWS ONLY, OFFSET n ROWS
      if ($builder->limit && $builder->offset) {
         return "OFFSET {$builder->offset} ROWS FETCH NEXT {$builder->limit} ROWS ONLY";
      } elseif ($builder->limit) {
         return "FETCH FIRST {$builder->limit} ROWS ONLY";
      } elseif ($builder->offset) {
         return "OFFSET {$builder->offset} ROWS";
      }
      return null;
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
         return ($operator === 'IN') ? '0=1' : '1=1';
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

   // --- Métodos de Utilidad Específicos de Oracle ---

   protected function wrap($value): string {
      if (str_contains($value, '.')) {
         return implode('.', array_map([$this, 'wrapValue'], explode('.', $value, 2)));
      }
      return $this->wrapValue($value);
   }

   protected function wrapValue(string $value): string {
      // En Oracle, los identificadores suelen ir en mayúsculas y entre comillas dobles
      return $value !== '*' ? '"' . strtoupper(str_replace('"', '""', $value)) . '"' : $value;
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