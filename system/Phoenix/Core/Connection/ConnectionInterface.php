<?php
/**
 * @package     Phoenix/Core
 * @subpackage  Connection
 * @file        ConnectionInterface
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:09:12
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Core\Connection;

use Phast\System\Phoenix\Core\Connection\Adapter\AdapterInterface;
use Phast\System\Phoenix\Core\Exceptions\ConnectionException;

/**
 * Contrato para las fábricas de conexiones de base de datos.
 *
 * Cualquier clase que implemente esta interfaz es responsable de tomar
 * una configuración y producir una instancia funcional que implemente
 * AdapterInterface.
 *
 * Esto permite abstraer el proceso de creación de la conexión, facilitando
 * la sustitución de la implementación de la fábrica (por ejemplo, por un gestor
 * de pool de conexiones) sin afectar al resto del sistema.
 */
interface ConnectionInterface {
   /**
    * Crea y devuelve la instancia del adaptador de base de datos configurado.
    *
    * Este es el método principal de la fábrica. Orquesta la creación del
    * objeto de conexión y lo devuelve listo para ser utilizado.
    *
    * @return AdapterInterface La instancia del adaptador lista para operar.
    * @throws ConnectionException Si no se puede establecer la conexión por
    *                             configuración inválida o problemas de red.
    */
   public function make(): AdapterInterface;
}
