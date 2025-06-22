<?php
/**
 * @package     Phoenix/Services
 * @subpackage  Transaction
 * @file        TransactionInterface.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 13:30:00
 * @version     1.0.0
 * @description Define el contrato para la gestión de transacciones de base de datos.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Services\Transaction;

use Throwable;

/**
 * Contrato para los gestores de transacciones de base de datos.
 *
 * Define una forma estandarizada de ejecutar un conjunto de operaciones (una unidad de trabajo)
 * dentro de una transacción. La implementación se encargará automáticamente de iniciar,
 * confirmar (commit) y revertir (rollback) la transacción, simplificando el código
 * de la aplicación y aumentando su robustez.
 */
interface TransactionInterface {
   /**
    * Ejecuta una función (callback) dentro de una transacción de base de datos.
    *
    * Si el callback se ejecuta sin lanzar ninguna excepción, la transacción se
    * confirma (commit). Si el callback lanza cualquier tipo de Throwable, la
    * transacción se revierte (rollback) y la excepción original es relanzada.
    *
    * @param callable(): mixed $callback La función que contiene las operaciones de base de datos.
    * @return mixed El valor devuelto por la función callback.
    * @throws Throwable Si ocurre un error, la excepción es relanzada después del rollback.
    */
   public function run(callable $callback);
}