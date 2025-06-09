<?php

/**
 * @package     phast/system
 * @subpackage  Database
 * @file        Connection
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:56:00
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Database;

use PDO;
use PDOException;
use InvalidArgumentException;

class Connection {
   private ?PDO $pdo = null;
   private array $config;

   public function __construct(array $config) {
      $this->config = $config;
   }

   public function getPdo(): PDO {
      if ($this->pdo === null) {
         $this->pdo = $this->createPdoInstance();
      }
      return $this->pdo;
   }

   private function createPdoInstance(): PDO {
      $dsn = $this->getDsn($this->config);

      try {
         return new PDO(
            $dsn,
            $this->config['username'] ?? null,
            $this->config['password'] ?? null,
            [
               PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
               PDO::ATTR_EMULATE_PREPARES => false,
            ]
         );
      } catch (PDOException $e) {
         throw new PDOException("Database connection failed for driver [{$this->config['driver']}]: " . $e->getMessage(), (int)$e->getCode(), $e);
      }
   }

   private function getDsn(array $config): string {
      extract($config); // Extrae 'driver', 'host', etc., a variables locales

      switch ($driver) {
         case 'mysql':
            return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
         case 'pgsql':
            return "pgsql:host={$host};port={$port};dbname={$database}";
         case 'sqlite':
            return "sqlite:{$database}";
         default:
            throw new InvalidArgumentException("Unsupported database driver [{$driver}].");
      }
   }
}
