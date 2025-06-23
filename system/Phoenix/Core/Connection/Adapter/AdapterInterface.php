<?php
/**
 * @package     Core/Connection
 * @subpackage  Adapter
 * @file        AdapterInterface
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 17:59:21
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Phoenix\Core\Connection\Adapter;
use Phast\System\Phoenix\Core\Exceptions\QueryException;

/**
 * Contrato para los Adaptadores de Base de Datos.
 *
 * Define las operaciones fundamentales que cualquier sistema de almacenamiento subyacente
 * debe implementar para ser compatible con el ORM. Esto garantiza que el núcleo
 * del ORM pueda operar con diferentes motores de bases de datos (PDO, MySQLi, etc.)
 * sin cambiar su código.
 */
interface AdapterInterface {
   /**
    * Ejecuta una consulta que devuelve un conjunto de resultados (ej. SELECT).
    *
    * @param string $sql La sentencia SQL compilada.
    * @param array<int|string, mixed> $bindings Los valores para los parámetros de la consulta.
    * @return array<int, array<string, mixed>> Un array de filas, donde cada fila es un array asociativo.
    * @throws QueryException Si la consulta falla durante la ejecución.
    */
   public function query(string $sql, array $bindings = []): array;

   /**
    * Ejecuta una sentencia que no devuelve un conjunto de resultados (ej. INSERT, UPDATE, DELETE).
    *
    * @param string $sql La sentencia SQL compilada.
    * @param array<int|string, mixed> $bindings Los valores para los parámetros de la sentencia.
    * @return int El número de filas afectadas por la operación.
    * @throws QueryException Si la sentencia falla durante la ejecución.
    */
   public function execute(string $sql, array $bindings = []): int;

   /**
    * Obtiene el ID del último registro insertado.
    *
    * @return string|int|false El ID del último registro o false si no se puede obtener.
    */
   public function getLastInsertId(): string|int|false;

   /**
    * Inicia una transacción.
    *
    * @return bool True si la transacción se inició correctamente, false en caso contrario.
    */
   public function beginTransaction(): bool;

   /**
    * Confirma la transacción actual.
    *
    * @return bool True si la confirmación fue exitosa, false en caso contrario.
    */
   public function commit(): bool;

   /**
    * Revierte la transacción actual.
    *
    * @return bool True si la reversión fue exitosa, false en caso contrario.
    */
   public function rollBack(): bool;

   /**
    * Ejecuta una consulta SQL nativa que devuelve un conjunto de resultados.
    *
    * @param string $sql La sentencia SQL nativa.
    * @param array<int|string, mixed> $bindings Los valores para los parámetros de la consulta.
    * @return array<int, array<string, mixed>> Un array de filas.
    * @throws QueryException Si la consulta falla.
    */
   public function rawQuery(string $sql, array $bindings = []): array;

   /**
    * Ejecuta una sentencia SQL nativa que no devuelve un conjunto de resultados.
    *
    * @param string $sql La sentencia SQL nativa.
    * @param array<int|string, mixed> $bindings Los valores para los parámetros de la sentencia.
    * @return int El número de filas afectadas.
    * @throws QueryException Si la sentencia falla.
    */
   public function rawExecute(string $sql, array $bindings = []): int;
}