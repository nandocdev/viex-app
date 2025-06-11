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
            $this->getDsn($this->config),
            $this->config['username'] ?? null,
            $this->config['password'] ?? null,
            [
               PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
               PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
               PDO::ATTR_EMULATE_PREPARES => false,
            ]
         );
      } catch (PDOException $e) {
         throw new PDOException("Database connection failed for driver [{$this->config['driver']}]: " . $e->getMessage(), (int) $e->getCode(), $e);
      }
   }

   private function getDsn(array $config): string {
      $driver = $config['driver'] ?? null;
      switch ($driver) {
         case 'mysql':
            return sprintf(
               "mysql:host=%s;port=%s;dbname=%s;charset=%s",
               $config['host'],
               $config['port'],
               $config['database'],
               $config['charset']
            );
         case 'pgsql':
            return sprintf(
               "pgsql:host=%s;port=%s;dbname=%s",
               $config['host'],
               $config['port'],
               $config['database']
            );
         case 'sqlite':
            return sprintf(
               "sqlite:%s",
               $config['database']
            );
         case 'sqlsrv':
            return sprintf(
               "sqlsrv:Server=%s,%s;Database=%s",
               $config['host'],
               $config['port'],
               $config['database']
            );
         default:
            throw new InvalidArgumentException("Unsupported database driver [{$driver}].");
      }
   }
}
