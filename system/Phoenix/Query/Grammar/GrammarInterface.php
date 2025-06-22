<?php
/**
 * @package     Phoenix/Query
 * @subpackage  Grammar
 * @file        GrammarInterface.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 11:30:00
 * @version     1.0.0
 * @description Define el contrato para los compiladores de dialectos SQL.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Query\Grammar;

use Phast\System\Phoenix\Query\Builder\QueryBuilder;

/**
 * Contrato para los compiladores de dialectos SQL (Gramáticas).
 *
 * Una gramática es responsable de tomar un objeto QueryBuilder, que es una
 * representación agnóstica de una consulta, y traducirlo a una cadena SQL
 * válida para un motor de base de datos específico (MySQL, PostgreSQL, etc.).
 *
 * Esta abstracción es fundamental para la portabilidad del ORM entre diferentes
 * sistemas de bases de datos.
 */
interface GrammarInterface {
   /**
    * Compila una consulta SELECT completa a partir de un QueryBuilder.
    *
    * @param QueryBuilder $builder El constructor de consultas a compilar.
    * @return string La sentencia SQL compilada.
    */
   public function compileSelect(QueryBuilder $builder): string;

   /**
    * Compila una sentencia INSERT a partir de un QueryBuilder y un array de valores.
    *
    * @param QueryBuilder $builder El constructor que especifica la tabla.
    * @param array<string, mixed> $values Los datos a insertar (columna => valor).
    * @return string La sentencia SQL de inserción compilada.
    */
   public function compileInsert(QueryBuilder $builder, array $values): string;

   /**
    * Compila una sentencia UPDATE a partir de un QueryBuilder y un array de valores.
    *
    * @param QueryBuilder $builder El constructor que especifica la tabla y las condiciones (WHERE).
    * @param array<string, mixed> $values Los datos a actualizar (columna => valor).
    * @return string La sentencia SQL de actualización compilada.
    */
   public function compileUpdate(QueryBuilder $builder, array $values): string;

   /**
    * Compila una sentencia DELETE a partir de un QueryBuilder.
    *
    * @param QueryBuilder $builder El constructor que especifica la tabla y las condiciones (WHERE).
    * @return string La sentencia SQL de eliminación compilada.
    */
   public function compileDelete(QueryBuilder $builder): string;

   /**
    * Obtiene y ordena todos los bindings de un QueryBuilder para una consulta SELECT.
    *
    * Es crucial que los bindings se recojan en el mismo orden en que sus
    * placeholders (?) aparecen en la sentencia SQL compilada (JOIN, WHERE, HAVING).
    *
    * @param QueryBuilder $builder El constructor de consultas.
    * @return array<int, mixed> Un array plano con todos los valores para la sentencia preparada.
    */
   public function getSelectBindings(QueryBuilder $builder): array;
}