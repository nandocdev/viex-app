<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        Database
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09
 * @version     2.0.0
 * @description Fachada principal para la ejecución de consultas SQL.
 */

declare(strict_types=1);

namespace Phast\System\Database;

use PDO;
use Closure;
use Throwable;

class Database {
   /** La instancia del gestor de conexiones. */
   protected DatabaseManager $manager;

   /** La conexión PDO activa para esta instancia. */
   protected ?PDO $pdo = null;

   /**
    * @param DatabaseManager $manager El gestor de conexiones.
    */
   public function __construct(DatabaseManager $manager) {
      $this->manager = $manager;
   }

   /**
    * Obtiene la instancia de PDO activa de la conexión por defecto.
    * Utiliza "lazy loading" para obtenerla solo cuando se necesita.
    */
   public function getPdo(): PDO {
      if ($this->pdo === null) {
         // Obtiene la instancia de Connection y luego la instancia de PDO.
         $this->pdo = $this->manager->connection()->getPdo();
      }
      return $this->pdo;
   }

   // --- Métodos de Ejecución Directa de Consultas (Raw SQL) ---

   /**
    * Ejecuta una consulta SQL y devuelve el PDOStatement.
    * Es la base para todas las demás operaciones de consulta.
    */
   public function query(string $sql, array $bindings = []): \PDOStatement {
      $stmt = $this->getPdo()->prepare($sql);
      $stmt->execute($bindings);
      return $stmt;
   }

   /**
    * Ejecuta una consulta SELECT y devuelve un array de resultados.
    */
   public function select(string $sql, array $bindings = []): array {
      return $this->query($sql, $bindings)->fetchAll();
   }

   /**
    * Ejecuta una consulta SELECT y devuelve la primera fila.
    */
   public function selectOne(string $sql, array $bindings = []): ?array {
      $result = $this->query($sql, $bindings)->fetch();
      return $result === false ? null : $result;
   }

   /**
    * Ejecuta una sentencia INSERT. Devuelve true si la inserción fue exitosa.
    */
   public function insert(string $sql, array $bindings = []): bool {
      return $this->query($sql, $bindings)->rowCount() > 0;
   }

   /**
    * Ejecuta una sentencia UPDATE. Devuelve el número de filas afectadas.
    */
   public function update(string $sql, array $bindings = []): int {
      return $this->query($sql, $bindings)->rowCount();
   }

   /**
    * Ejecuta una sentencia DELETE. Devuelve el número de filas afectadas.
    */
   public function delete(string $sql, array $bindings = []): int {
      return $this->query($sql, $bindings)->rowCount();
   }

   /**
    * Obtiene el ID del último registro insertado.
    */
   public function lastInsertId(): string {
      return $this->getPdo()->lastInsertId();
   }

   // --- Métodos de Transacción ---

   public function beginTransaction(): void {
      $this->getPdo()->beginTransaction();
   }

   public function commit(): void {
      $this->getPdo()->commit();
   }

   public function rollBack(): void {
      $this->getPdo()->rollBack();
   }

   /**
    * Ejecuta una operación dentro de una transacción de forma segura.
    * Si el callback lanza una excepción, hace rollback automáticamente.
    * Si el callback termina exitosamente, hace commit.
    *
    * @param Closure $callback La lógica a ejecutar.
    * @return mixed El resultado del callback.
    * @throws Throwable
    */
   public function transaction(Closure $callback) {
      $this->beginTransaction();

      try {
         $result = $callback($this);
         $this->commit();
         return $result;
      } catch (Throwable $e) {
         $this->rollBack();
         throw $e; // Re-lanza la excepción después de hacer rollback.
      }
   }

   // --- Integración con Query Builder ---

   /**
    * Inicia una nueva consulta fluida usando el Query Builder.
    * Este es el punto de entrada para construir consultas de forma programática.
    *
    * @param string $table El nombre de la tabla.
    * @return QueryBuilder
    */
   public function table(string $table): QueryBuilder {
      return (new QueryBuilder($this))->table($table);
   }
}