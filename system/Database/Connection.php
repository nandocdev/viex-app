<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        Connection
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08
 * @version     1.0.0
 * @description Gestiona una única conexión de base de datos a través de PDO.
 */

declare(strict_types=1);

namespace Phast\System\Database;

use PDO;
use PDOException;
use InvalidArgumentException;

class Connection {
   /**
    * La instancia de PDO activa. Es null hasta que se solicita la conexión.
    * @var PDO|null
    */
   private ?PDO $pdo = null;

   /**
    * La configuración para esta conexión específica.
    * @var array
    */
   private array $config;

   /**
    * @param array $config El array de configuración para la conexión (host, db, user, etc.).
    */
   public function __construct(array $config) {
      $this->config = $config;
   }

   /**
    * Obtiene la instancia de PDO.
    * Implementa "lazy loading": la conexión solo se crea cuando se necesita por primera vez.
    */
   public function getPdo(): PDO {
      if ($this->pdo === null) {
         $this->pdo = $this->createPdoInstance();
      }
      return $this->pdo;
   }

   /**
    * Crea y configura una nueva instancia de PDO.
    * @throws PDOException si la conexión falla.
    */
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

   /**
    * Construye el DSN (Data Source Name) string basado en la configuración del driver.
    * @throws InvalidArgumentException si el driver no está soportado.
    */
   private function getDsn(array $config): string {
      $driver = $config['driver'] ?? null;

      // El uso de 'required' asegura que no intentemos construir un DSN sin datos clave.
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

   /**
    * Obtiene las opciones por defecto para la conexión PDO.
    */
   protected function getOptions(): array {
      return [
            // Esencial para un manejo de errores robusto.
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Devuelve arrays asociativos, más intuitivo para la mayoría de casos.
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // ¡Crucial para la seguridad! Usa preparados nativos en lugar de emulados.
         PDO::ATTR_EMULATE_PREPARES => false,
      ];
   }

   /**
    * Pequeño helper para verificar que existen claves de configuración requeridas.
    */
   private function requireConfig(array $config, array $keys): void {
      foreach ($keys as $key) {
         if (!array_key_exists($key, $config)) {
            throw new InvalidArgumentException("Missing required configuration key: [{$key}].");
         }
      }
   }
}