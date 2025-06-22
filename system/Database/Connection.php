<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        Connection
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 00:53:02
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Database;
use PDO;
use PDOException;
use InvalidArgumentException;
use Phast\System\Core\Container;
class Connection {
   private ?PDO $pdo = null;
   private array $config;
   private string $connectionName = 'default';
   public function __construct() {
      $this->connectionName = Container::getInstance()->resolve('database')['default'] ?? 'default';
      $this->config = Container::getInstance()->resolve('database')['connections'][$this->connectionName] ?? [];
      if (empty($this->config)) {
         throw new InvalidArgumentException("Database configuration is not set or is empty.");
      }
      $this->requireConfig($this->config, ['driver']);
      if (!in_array($this->config['driver'], ['mysql', 'pgsql', 'sqlite', 'sqlsrv'])) {
         throw new InvalidArgumentException("Unsupported database driver: [{$this->config['driver']}].");
      }
   }

   public function getPdo(): PDO {
      if ($this->pdo === null) {
         $this->pdo = $this->createPdoInstance();
      }
      return $this->pdo;
   }

   private function createPdoInstance(): PDO {
      try {
         return new PDO(
            $this->getDsn($this->config),
            $this->config['username'] ?? null,
            $this->config['password'] ?? null,
            $this->getOptions()
         );
      } catch (PDOException $e) {
         // Re-lanza la excepción con un mensaje más contextual.
         throw new PDOException(
            "Database connection failed for driver [{$this->config['driver']}]: " . $e->getMessage(),
            (int) $e->getCode(),
            $e
         );
      }
   }

   private function getDsn(array $config): string {
      $driver = $config['driver'] ?? null;

      $this->requireConfig($config, ['host', 'database']);

      switch ($driver) {
         case 'mysql':
            return sprintf(
               "mysql:host=%s;port=%s;dbname=%s;charset=%s",
               $config['host'],
               $config['port'] ?? '3306',
               $config['database'],
               $config['charset'] ?? 'utf8mb4'
            );
         case 'pgsql':
            return sprintf(
               "pgsql:host=%s;port=%s;dbname=%s",
               $config['host'],
               $config['port'] ?? '5432',
               $config['database']
            );
         case 'sqlite':
            $this->requireConfig($config, ['database']);
            return "sqlite:" . $config['database'];
         case 'sqlsrv':
            return sprintf(
               "sqlsrv:Server=%s,%s;Database=%s",
               $config['host'],
               $config['port'] ?? '1433',
               $config['database']
            );
         default:
            throw new InvalidArgumentException("Unsupported database driver [{$driver}].");
      }
   }

   protected function getOptions(): array {
      return [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false,
      ];
   }

   private function requireConfig(array $config, array $keys): void {
      foreach ($keys as $key) {
         if (!array_key_exists($key, $config)) {
            throw new InvalidArgumentException("Missing required configuration key: [{$key}].");
         }
      }
   }

   public function close(): void {
      if ($this->pdo !== null) {
         $this->pdo = null; // Destruye la instancia de PDO
      }
   }
}