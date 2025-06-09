<?php

/**
 * @package     phast/system
 * @subpackage  Database
 * @file        Database
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 00:00:05
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Database;

use Phast\System\Core\Container;
use Phast\System\Database\DatabaseManager;
use Closure;
use Throwable;

class Database {
   private ?\PDO $pdo = null;
   private DatabaseManager $manager;
   protected string $sql;

   public function __construct(DatabaseManager $manager) {
      $this->manager = $manager;
   }

   public function getPdo(): \PDO {
      if ($this->pdo === null) {
         $this->pdo = $this->manager->getConnection();
      }
      return $this->pdo;
   }



   public function query(string $sql, array $bindings = []): \PDOStatement {
      $stmt = $this->getPdo()->prepare($sql);
      $stmt->execute($bindings);
      return $stmt;
   }

   public function select(string $sql, array $bindings = []): array {
      return $this->query($sql, $bindings)->fetchAll();
   }

   public function selectOne(string $sql, array $bindings = []): ?array {
      $result = $this->query($sql, $bindings)->fetch();
      return $result === false ? null : $result;
   }

   public function insert(string $sql, array $bindings = []): bool {
      return $this->query($sql, $bindings)->rowCount() > 0;
   }

   public function update(string $sql, array $bindings = []): int {
      return $this->query($sql, $bindings)->rowCount();
   }

   public function delete(string $sql, array $bindings = []): int {
      return $this->query($sql, $bindings)->rowCount();
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
    * Ejecuta una operación dentro de una transacción.
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
         throw $e; // Re-lanza la excepción después de hacer rollback
      }
   }

   public function lastInsertId(): string {
      return $this->getPdo()->lastInsertId();
   }


   // metodo que cuenta todos los registros que puede retornar una consulta, reemplaza todos los campos de la consulta por un COUNT(*)
   private function count(string $sql, array $bindings = []): int {
      $countSql = preg_replace('/SELECT\s+(.*)\s+FROM\s+(.*)/i', 'SELECT COUNT(*) FROM $2', $sql);
      return (int) $this->query($countSql, $bindings)->fetchColumn();
   }

   // con el datos de cantidad de registros, se puede paginar los resultados
   public function paginate(string $sql, array $bindings = [], int $perPage = 15, int $page = 1): array {
      $total = $this->count($sql, $bindings);
      $offset = ($page - 1) * $perPage;

      // Modifica la consulta original para incluir LIMIT y OFFSET
      $paginatedSql = $sql . " LIMIT :limit OFFSET :offset";
      $bindings[':limit'] = $perPage;
      $bindings[':offset'] = $offset;

      // Ejecuta la consulta paginada
      $results = $this->select($paginatedSql, $bindings);

      return [
         'data' => $results,
         'total' => $total,
         'per_page' => $perPage,
         'current_page' => $page,
         'last_page' => (int) ceil($total / $perPage),
      ];
   }
}
