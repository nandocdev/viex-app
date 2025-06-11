<?php
/**
 * @package     phast/system
 * @subpackage  Core/Contracts
 * @file        ServiceProviderInterface
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-11
 * @version     1.0.0
 * @description Contrato para los proveedores de servicios de la aplicación.
 */

declare(strict_types=1);

namespace Phast\System\Core\Contracts;

use Phast\System\Core\Container;

interface ServiceProviderInterface {
   /**
    * Registra los servicios en el contenedor de dependencias.
    *
    * @param Container $container
    * @return void
    */
   public function register(Container $container): void;
}