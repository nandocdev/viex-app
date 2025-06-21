<?php
/**
 * @package     Phoenix/Services
 * @subpackage  Transaction
 * @file        Transaction.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 13:45:00
 * @version     1.0.0
 * @description Implementa la gestión de transacciones de base de datos.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Services\Transaction;

use Phast\System\Phoenix\Core\Connection\Adapter\AdapterInterface;
use Throwable;

/**
 * Gestiona unidades de trabajo atómicas a través de transacciones de base de datos.
 *
 * Esta implementación utiliza el AdapterInterface para controlar el ciclo de vida
 * de una transacción, proporcionando una abstracción segura y fácil de usar sobre
 * beginTransaction, commit y rollBack.
 */
final class Transaction implements TransactionInterface {
   /**
    * El adaptador de la base de datos para controlar la transacción.
    * @var AdapterInterface
    */
   private AdapterInterface $adapter;

   /**
    * @param AdapterInterface $adapter La conexión a la base de datos sobre la que se ejecutará la transacción.
    */
   public function __construct(AdapterInterface $adapter) {
      $this->adapter = $adapter;
   }

   /**
    * {@inheritdoc}
    */
   public function run(callable $callback) {
      $this->adapter->beginTransaction();

      try {
         // Ejecutamos la lógica de negocio del usuario.
         $result = $callback();

         // Si todo fue bien, confirmamos los cambios.
         $this->adapter->commit();

         return $result;
      } catch (Throwable $e) {
         // Si algo salió mal, revertimos todos los cambios.
         $this->adapter->rollBack();

         // Y relanzamos la excepción para que pueda ser manejada por la aplicación.
         throw $e;
      }
   }
}