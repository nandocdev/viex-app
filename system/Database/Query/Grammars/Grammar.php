<?php
/**
 * @package     Database/Query
 * @subpackage  Grammars
 * @file        Grammar
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:15:41
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Database\Query\Grammars;

use Phast\System\Database\Query\Builder;

abstract class Grammar {
   /**
    * Compila una sentencia SELECT completa.
    * Este es el método principal que orquesta la compilación.
    */
   public function compileSelect(Builder $builder): string {
      $components = [
         'columns' => $this->compileColumns($builder, $builder->getColumns()),
         'from' => $this->compileFrom($builder, $builder->getFrom()),
         'joins' => $this->compileJoins($builder, $builder->getJoins()),
         'wheres' => $this->compileWheres($builder, $builder->getWheres()),
         'groups' => $this->compileGroups($builder, $builder->getGroups()),
         'havings' => $this->compileHavings($builder, $builder->getHavings()),
         'orders' => $this->compileOrders($builder, $builder->getOrders()),
         'limit' => $this->compileLimit($builder, $builder->getLimit()),
         'offset' => $this->compileOffset($builder, $builder->getOffset()),
      ];

      // Concatena solo los componentes que no están vacíos
      return implode(' ', array_filter($components));
   }

   // Cada parte de la consulta tiene su propio método de compilación
   abstract protected function compileColumns(Builder $builder, array $columns): string;
   abstract protected function compileFrom(Builder $builder, string $table): string;
   abstract protected function compileJoins(Builder $builder, array $joins): ?string;

   abstract protected function compileWheres(Builder $builder, array $wheres): ?string;
   abstract protected function compileGroups(Builder $builder, array $groups): ?string;
   abstract protected function compileHavings(Builder $builder, array $havings): ?string;
   abstract protected function compileOrders(Builder $builder, array $orders): ?string;
   abstract protected function compileLimit(Builder $builder, ?int $limit): ?string;
   abstract protected function compileOffset(Builder $builder, ?int $offset): ?string;



   /**
    * Envuelve un identificador (tabla o columna) en las comillas apropiadas.
    * Este es un método de utilidad clave.
    */
   public function wrap(string $value): string {
      // En la gramática base, podríamos no hacer nada
      return $value;
   }

   /**
    * Convierte un array de valores en una lista de placeholders para `IN (...)`.
    */
   public function parameterize(array $values): string {
      return implode(', ', array_fill(0, count($values), '?'));
   }
}