<?php
/**
 * @package     Phoenix/Core
 * @subpackage  Connection
 * @file        Connection
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:06:39
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Core\Connection;

use Phast\System\Phoenix\Core\Connection\Adapter\AdapterInterface;
use Phast\System\Phoenix\Core\Connection\Adapter\PdoAdapter;
use Phast\System\Phoenix\Core\Exceptions\ConnectionException;
use Phast\System\Phoenix\Core\Connection\ConnectionInterface;
use PDO;
use PDOException;
use InvalidArgumentException;

/**
 * Fábrica de Conexiones a la Base de Datos.
 *
 * Responsable de leer la configuración y crear la instancia del adaptador
 * de base de datos correspondiente. Actúa como un punto centralizado para
 * gestionar la creación de conexiones, promoviendo la extensibilidad.
 */
class Connection implements ConnectionInterface {

   private ?AdapterInterface $adapter = null;

   /**
    * Array de configuración para la conexión.
    * @var array<string, mixed>
    */
   private array $config;

   private array $drivers = [];

   /**
    * @param array<string, mixed> $config La configuración de la base de datos.
    */
   public function __construct(array $config) {
      if (empty($config)) {
         throw new InvalidArgumentException("El array de configuración de la base de datos no puede estar vacío.");
      }
      $this->config = $config;
      $this->drivers = [
         'pdo' => [$this, 'createPdoAdapter'],
         // 'mysqli' => [$this, 'createMySqliAdapter'],
      ];

   }

   /**
    * Crea y devuelve la instancia del adaptador configurado.
    *
    * @return AdapterInterface
    * @throws ConnectionException Si el driver no es soportado o la conexión falla.
    */
   // public function make(): AdapterInterface {

   //    $driver = $this->config['driver'] ?? null;
   //    if (!isset($this->drivers[$driver])) {
   //       throw new ConnectionException("Driver no soportado: [{$driver}].");
   //    }

   //    if ($this->adapter !== null) {
   //       return $this->adapter;
   //    }

   //    switch ($driver) {
   //       case 'pdo':
   //          $this->adapter = $this->createPdoAdapter();
   //          return $this->adapter;
   //       // Futuros drivers irían aquí:
   //       // case 'mysqli':
   //       //     return $this->createMySqliAdapter();
   //       default:
   //          throw new ConnectionException("Driver de base de datos no soportado: [{$driver}].");
   //    }
   // }

   public function make(): AdapterInterface {
      $driver = $this->config['driver'] ?? null;
      if (!isset($this->drivers[$driver])) {
         throw new ConnectionException("Driver no soportado: [{$driver}].");
      }
      return call_user_func($this->drivers[$driver]);
   }

   /**
    * Crea una instancia del adaptador PDO.
    *
    * @return PdoAdapter
    */
   private function createPdoAdapter(): PdoAdapter {
      try {
         $pdo = new PDO(
            $this->buildPdoDsn(),
            $this->config['username'] ?? null,
            $this->config['password'] ?? null,
            $this->getMergedPdoOptions()
         );

         return new PdoAdapter($pdo);
      } catch (PDOException $e) {
         throw new ConnectionException("Fallo al crear la conexión PDO: " . $e->getMessage(), (int) $e->getCode(), $e);
      }
   }

   /**
    * Construye la cadena DSN para PDO basada en la configuración.
    *
    * @return string
    */
   private function buildPdoDsn(): string {
      $dbType = $this->config['db_type'] ?? null;
      if (!$dbType) {
         throw new InvalidArgumentException("La clave de configuración 'db_type' (mysql, pgsql, sqlite) es requerida para el driver PDO.");
      }

      switch ($dbType) {
         case 'mysql':
            $this->requireConfigKeys(['host', 'database']);
            return sprintf(
               "mysql:host=%s;port=%s;dbname=%s;charset=%s",
               $this->config['host'],
               $this->config['port'] ?? 3306,
               $this->config['database'],
               $this->config['charset'] ?? 'utf8mb4'
            );

         case 'pgsql':
            $this->requireConfigKeys(['host', 'database']);
            return sprintf(
               "pgsql:host=%s;port=%s;dbname=%s",
               $this->config['host'],
               $this->config['port'] ?? 5432,
               $this->config['database']
            );

         case 'sqlite':
            $this->requireConfigKeys(['database']);
            return "sqlite:" . $this->config['database'];

         default:
            throw new InvalidArgumentException("Tipo de base de datos PDO no soportado: [{$dbType}].");
      }
   }

   /**
    * Combina las opciones de PDO por defecto con las proporcionadas por el usuario.
    *
    * @return array<int, mixed>
    */
   private function getMergedPdoOptions(): array {
      $defaults = [
         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false,
         PDO::ATTR_PERSISTENT => $this->config['persistent'] ?? false,
      ];

      return ($this->config['options'] ?? []) + $defaults;
   }

   /**
    * Valida que las claves de configuración requeridas existan.
    *
    * @param array<int, string> $keys
    * @return void
    */
   private function requireConfigKeys(array $keys): void {
      foreach ($keys as $key) {
         if (!array_key_exists($key, $this->config)) {
            throw new InvalidArgumentException("Falta la clave de configuración requerida: [{$key}].");
         }
      }
   }
}