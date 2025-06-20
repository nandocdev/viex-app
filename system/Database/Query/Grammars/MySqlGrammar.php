<?php
/**
 * @package     Database/Query
 * @subpackage  Grammars
 * @file        MySqlGrammar
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:17:42
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Database\Query\Grammars;

use Phast\System\Database\Query\Builder;

class MySqlGrammar extends Grammar {

   // Implementación de los métodos abstractos

   protected function compileColumns(Builder $builder, array $columns): string {
      return 'SELECT ' . implode(', ', array_map([$this, 'wrap'], $columns));
   }

   protected function compileFrom(Builder $builder, string $table): string {
      return 'FROM ' . $this->wrap($table);
   }

   protected function compileLimit(Builder $builder, ?int $limit): ?string {
      return $limit ? 'LIMIT ' . (int) $limit : null;
   }

   protected function compileOffset(Builder $builder, ?int $offset): ?string {
      return $offset ? 'OFFSET ' . (int) $offset : null;
   }

   // ... implementaciones para los otros componentes ...

   /**
    * Sobrescribe el método wrap para usar acentos graves (backticks) de MySQL.
    */
   public function wrap(string $value): string {
      // Maneja casos como 'users.id' o funciones como COUNT(*)
      if (str_contains($value, '.')) {
         return implode('.', array_map([$this, 'wrapValue'], explode('.', $value)));
      }

      return $this->wrapValue($value);
   }

   protected function wrapValue(string $value): string {
      // No envuelve asteriscos
      if ($value === '*') {
         return $value;
      }
      return '`' . str_replace('`', '``', $value) . '`';
   }

   // Implementa los otros métodos abstractos según sea necesario
   protected function compileJoins(Builder $builder, array $joins): ?string {
      // Implementación de joins específica para MySQL
      return null; // Placeholder, implementar según sea necesario
   }

   protected function compileWheres(Builder $builder, array $wheres): ?string {
      // Implementación de condiciones WHERE específica para MySQL
      return null; // Placeholder, implementar según sea necesario
   }

   protected function compileGroups(Builder $builder, array $groups): ?string {
      // Implementación de agrupamientos GROUP BY específica para MySQL
      return null; // Placeholder, implementar según sea necesario
   }

   protected function compileHavings(Builder $builder, array $havings): ?string {
      // Implementación de condiciones HAVING específica para MySQL
      return null; // Placeholder, implementar según sea necesario
   }

   protected function compileOrders(Builder $builder, array $orders): ?string {
      // Implementación de ordenamientos ORDER BY específica para MySQL
      return null; // Placeholder, implementar según sea necesario
   }

   protected function compileValues(array $values): string {
      // Implementación de valores para inserciones
      return implode(', ', array_map([$this, 'wrapValue'], $values));
   }
   protected function compileSet(array $set): string {
      // Implementación de SET para actualizaciones
      return implode(', ', array_map(function ($key, $value) {
         return $this->wrap($key) . ' = ' . $this->wrapValue($value);
      }, array_keys($set), $set));
   }
}