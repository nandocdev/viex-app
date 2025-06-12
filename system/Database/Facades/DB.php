<?php
/**
 * @package     phast/system
 * @subpackage  Database/Facades
 * @file        DB
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-12
 * @version     1.0.0
 * @description Fachada estática para una interacción sencilla y elegante con la base de datos.
 */
declare(strict_types=1);

namespace Phast\System\Database\Facades;

use Phast\System\Core\Container;
use Phast\System\Database\Database;
use Phast\System\Database\QueryBuilder;
use BadMethodCallException;
use Closure;

/**
 * Proporciona una API estática para interactuar con la base de datos.
 *
 * @method static QueryBuilder table(string $table)
 * @method static array select(string $sql, array $bindings = [])
 * @method static array selectOne(string $sql, array $bindings = [])
 * @method static bool insert(string $sql, array $bindings = [])
 * @method static int update(string $sql, array $bindings = [])
 * @method static int delete(string $sql, array $bindings = [])
 * @method static void beginTransaction()
 * @method static void commit()
 * @method static void rollBack()
 * @method static mixed transaction(Closure $callback)
 * @method static string lastInsertId()
 */
class DB {
   /**
    * Inicia una nueva consulta fluida usando el Query Builder.
    * Este es el método más común para empezar una consulta.
    *
    * @param string $table El nombre de la tabla sobre la que se operará.
    * @return QueryBuilder
    */
   public static function table(string $table): QueryBuilder {
      // Pide a la instancia de Database que cree un nuevo QueryBuilder.
      return self::getDatabaseInstance()->table($table);
   }

   /**
    * Maneja las llamadas a métodos estáticos que no están definidos explícitamente en esta fachada.
    *
    * Delega la llamada a la instancia principal de la clase `Database`,
    * permitiendo el acceso a métodos como `select`, `insert`, `transaction`, etc.
    * de forma estática: `DB::select(...)`.
    *
    * @param string $method El nombre del método llamado.
    * @param array $args Los argumentos pasados al método.
    * @return mixed
    * @throws BadMethodCallException Si el método no existe en la clase Database.
    */
   public static function __callStatic(string $method, array $args) {
      $database = self::getDatabaseInstance();

      if (method_exists($database, $method)) {
         return $database->$method(...$args);
      }

      throw new BadMethodCallException("Static method DB::{$method}() does not exist.");
   }

   /**
    * Obtiene la instancia singleton de la clase Database desde el contenedor de DI.
    *
    * Este método asegura que siempre estemos trabajando con la misma instancia
    * de base de datos a lo largo de todo el ciclo de la petición.
    *
    * @return Database
    */
   protected static function getDatabaseInstance(): Database {
      return Container::getInstance()->resolve(Database::class);
   }
}