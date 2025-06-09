<?php

/**
 * @package     phast/system
 * @subpackage  Database
 * @file        DatabaseManager
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:53:16
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Database;

use Phast\System\Core\Application;
use Phast\System\Database\Connection;
use InvalidArgumentException;

class DatabaseManager {
   protected array $config;
   protected array $connections = [];

   /**
    * El constructor ahora recibe sus dependencias.
    * @param Application $app Para obtener la ruta base del proyecto.
    */
   public function __construct(Application $app) {
      // Carga la configuración de la base de datos.
      $configPath = $app->basePath . '/config/database.php';
      if (!file_exists($configPath)) {
         throw new \RuntimeException('Database configuration file not found.');
      }
      $this->config = require $configPath;
   }

   /**
    * Obtiene una instancia de conexión de la base de datos.
    *
    * @param string|null $name El nombre de la conexión. Si es nulo, usa la por defecto.
    * @return Connection La instancia de la conexión.
    */
   public function connection(?string $name = null): Connection {
      $name = $name ?? $this->getDefaultConnectionName();

      if (isset($this->connections[$name])) {
         return $this->connections[$name];
      }

      $config = $this->getConnectionConfig($name);

      return $this->connections[$name] = new Connection($config);
   }

   private function getConnectionConfig(string $name): array {
      if (!isset($this->config['connections'][$name])) {
         throw new InvalidArgumentException("Database connection [{$name}] is not configured.");
      }
      return $this->config['connections'][$name];
   }

   private function getDefaultConnectionName(): string {
      return $this->config['default'];
   }

   /**
    * Pasa las llamadas a la conexión por defecto.
    */
   public function __call(string $method, array $parameters) {
      return $this->connection()->$method(...$parameters);
   }
}
