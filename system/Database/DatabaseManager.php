<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        DatabaseManager
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08
 * @version     1.0.0
 * @description Gestiona y actúa como una fábrica para múltiples conexiones de base de datos.
 */

declare(strict_types=1);

namespace Phast\System\Database;

use Phast\System\Core\Application;
use InvalidArgumentException;
use RuntimeException;

class DatabaseManager {
   /**
    * El array de configuración cargado desde el archivo de configuración.
    * @var array
    */
   protected array $config;

   /**
    * El "pool" de conexiones activas.
    * Almacena las instancias de Connection para reutilizarlas.
    * @var Connection[]
    */
   protected array $connections = [];

   /**
    * El constructor recibe la instancia de la aplicación para poder localizar
    * el archivo de configuración de la base de datos.
    *
    * @param Application $app La instancia de la aplicación principal.
    * @throws RuntimeException Si el archivo de configuración no se encuentra.
    */
   public function __construct(Application $app) {
      $configPath = $app->basePath . '/config/database.php';

      if (!file_exists($configPath)) {
         throw new RuntimeException('Database configuration file not found at: ' . $configPath);
      }

      $config = require $configPath;

      if (!is_array($config)) {
         throw new RuntimeException('Database configuration file must return an array.');
      }

      $this->config = $config;
   }

   /**
    * Obtiene una instancia de conexión de la base de datos.
    * Si no se especifica un nombre, utiliza la conexión por defecto.
    *
    * @param string|null $name El nombre de la conexión (ej: 'mysql', 'pgsql').
    * @return Connection La instancia de la conexión.
    * @throws InvalidArgumentException si la conexión solicitada no está configurada.
    */
   public function connection(?string $name = null): Connection {
      // Si el nombre es nulo, usa el valor 'default' del archivo de configuración.
      $name = $name ?? $this->getDefaultConnectionName();

      // Si ya hemos creado esta conexión, la reutilizamos para mejorar el rendimiento.
      if (isset($this->connections[$name])) {
         return $this->connections[$name];
      }

      // Obtiene la configuración específica para esta conexión.
      $config = $this->getConnectionConfig($name);

      // Crea, almacena en el pool y devuelve la nueva instancia de Connection.
      return $this->connections[$name] = new Connection($config);
   }

   /**
    * Obtiene la configuración para una conexión con nombre específico.
    */
   private function getConnectionConfig(string $name): array {
      if (!isset($this->config['connections'][$name])) {
         throw new InvalidArgumentException("Database connection [{$name}] is not configured.");
      }
      return $this->config['connections'][$name];
   }

   /**
    * Obtiene el nombre de la conexión por defecto.
    */
   private function getDefaultConnectionName(): string {
      return $this->config['default'];
   }

   /**
    * Permite pasar llamadas a métodos directamente a la conexión por defecto.
    * Esto es un "atajo" para no tener que escribir `->connection()->...` siempre.
    * Ejemplo: `$dbManager->getPdo()` es un atajo para `$dbManager->connection()->getPdo()`.
    */
   public function __call(string $method, array $parameters) {
      return $this->connection()->$method(...$parameters);
   }
}