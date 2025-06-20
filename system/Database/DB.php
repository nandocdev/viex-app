<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        DB
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 00:53:21
 * @version     1.0.0
 * @description     Responsabilidad: Ser el único punto de entrada público y estático para interactuar con la base de datos.
 *   Métodos Clave:
 *       public static function table(string $name): Builder: Inicia una consulta en una tabla específica. Crea y devuelve una nueva instancia del Query\Builder.
 *       public static function select(string $query, array $bindings = []): array: Ejecuta una consulta SQL cruda de tipo SELECT.
 *       public static function statement(string $query, array $bindings = []): bool: Ejecuta una consulta cruda que no devuelve resultados (UPDATE, DELETE, etc.).
 *       public static function transaction(Closure $callback): Ejecuta un conjunto de operaciones dentro de una transacción de base de datos. Hace commit si todo va bien, o rollback si se lanza una excepción.
 */
declare(strict_types=1);
namespace Phast\System\Database;


use Phast\System\Core\Container;
use Phast\System\Database\Query\Builder;
use PDO;
use Closure;
use Throwable;

class DB {
   /**
    * Almacena la instancia de la conexión PDO para reutilizarla.
    * @var PDO|null
    */
   protected static ?PDO $pdo = null;

   /**
    * Obtiene la instancia de PDO desde el contenedor.
    * La hace singleton de facto dentro de esta clase para evitar
    * resolverla del contenedor en cada llamada.
    *
    * @return PDO
    */
   public static function connection(): PDO {
      if (is_null(self::$pdo)) {
         // Asume que tienes un `Connection` service en tu contenedor.
         // Phast ya lo tiene a través de `DatabaseServiceProvider`.
         $connectionService = Container::getInstance()->resolve(Connection::class);
         self::$pdo = $connectionService->getPdo();
      }
      return self::$pdo;
   }

   /**
    * Inicia una nueva consulta fluida en una tabla.
    *
    * @param string $name El nombre de la tabla.
    * @return Builder Una nueva instancia del Query Builder.
    */
   public static function table(string $name): Builder {
      $builder = new Builder(self::connection());
      return $builder->from($name);
   }

   /**
    * Ejecuta una consulta SELECT cruda y devuelve los resultados.
    *
    * @param string $query La sentencia SQL.
    * @param array $bindings Los valores para la consulta preparada.
    * @return array Un array de objetos (stdClass) con los resultados.
    */
   public static function select(string $query, array $bindings = []): array {
      $statement = self::connection()->prepare($query);
      $statement->execute($bindings);
      return $statement->fetchAll(PDO::FETCH_OBJ);
   }

   /**
    * Ejecuta una sentencia SQL cruda que no devuelve un conjunto de resultados.
    * (INSERT, UPDATE, DELETE, etc.).
    *
    * @param string $query La sentencia SQL.
    * @param array $bindings Los valores para la consulta preparada.
    * @return bool True si la ejecución fue exitosa.
    */
   public static function statement(string $query, array $bindings = []): bool {
      $statement = self::connection()->prepare($query);
      return $statement->execute($bindings);
   }

   /**
    * Ejecuta un conjunto de operaciones dentro de una transacción.
    *
    * @param Closure $callback La función que contiene las operaciones de BD.
    * @return mixed El resultado del callback.
    * @throws Throwable Si ocurre un error, la transacción se deshace y la excepción se relanza.
    */
   public static function transaction(Closure $callback) {
      $pdo = self::connection();
      $pdo->beginTransaction();

      try {
         $result = $callback();
         $pdo->commit();
         return $result;
      } catch (Throwable $e) {
         $pdo->rollBack();
         throw $e; // Relanzamos la excepción para que pueda ser manejada más arriba.
      }
   }
}