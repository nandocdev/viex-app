<?php
/**
 * @package     Core/Connection
 * @subpackage  Adapter
 * @file        PdoAdapter
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:03:28
 * @version     1.0.0
 * @description
 */


declare(strict_types=1);

namespace Phast\System\Phoenix\Core\Connection\Adapter;

use PDO;
use PDOException;
use Phast\System\Phoenix\Core\Exceptions\QueryException;

/**
 * Adaptador de base de datos que utiliza la extensión PDO de PHP.
 *
 * Esta clase implementa la AdapterInterface, actuando como un puente entre
 * las operaciones abstractas del ORM y la implementación concreta de PDO.
 * Se encarga de preparar y ejecutar sentencias, manejar transacciones y
 * envolver las excepciones de PDO en excepciones más específicas del ORM.
 */
class PdoAdapter implements AdapterInterface {
   /**
    * La instancia de la conexión PDO.
    * @var PDO
    */
   private PDO $pdo;

   /**
    * @param PDO $pdo Una instancia activa de la conexión PDO.
    */
   public function __construct(PDO $pdo) {
      $this->pdo = $pdo;
   }

   /**
    * {@inheritdoc}
    */
   public function query(string $sql, array $bindings = []): array {
      try {
         $statement = $this->pdo->prepare($sql);
         $statement->execute($bindings);

         // Devolvemos un array de arrays asociativos.
         return $statement->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
         // Envolvemos la excepción de PDO en nuestra excepción personalizada.
         throw new QueryException($sql, $bindings, $e);
      }
   }

   /**
    * {@inheritdoc}
    */
   public function execute(string $sql, array $bindings = []): int {
      try {
         $statement = $this->pdo->prepare($sql);
         $statement->execute($bindings);

         // Devolvemos el número de filas afectadas.
         return $statement->rowCount();
      } catch (PDOException $e) {
         throw new QueryException($sql, $bindings, $e);
      }
   }

   /**
    * {@inheritdoc}
    */
   public function getLastInsertId(): string|int|false {
      return $this->pdo->lastInsertId();
   }

   /**
    * {@inheritdoc}
    */
   public function beginTransaction(): bool {
      return $this->pdo->beginTransaction();
   }

   /**
    * {@inheritdoc}
    */
   public function commit(): bool {
      return $this->pdo->commit();
   }

   /**
    * {@inheritdoc}
    */
   public function rollBack(): bool {
      return $this->pdo->rollBack();
   }
}