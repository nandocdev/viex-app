<?php
/**
 * @package     http/phast
 * @subpackage  config
 * @file        providers
 * @description Registra todos los proveedores de servicios para la aplicación.
 */

declare(strict_types=1);

return [
   /*
   |--------------------------------------------------------------------------
   | Proveedores de Servicios del Núcleo (System)
   |--------------------------------------------------------------------------
   |
   | Estos proveedores cargan los componentes esenciales del framework.
   | No deberían ser modificados a menos que sepas lo que haces.
   |
   */
   Phast\System\Providers\SystemServiceProvider::class,
   Phast\System\Providers\DatabaseServiceProvider::class,
   Phast\System\Providers\ViewServiceProvider::class,
   Phast\System\Providers\RoutingServiceProvider::class,

   /*
   |--------------------------------------------------------------------------
   | Proveedores de Servicios de la Aplicación (App)
   |--------------------------------------------------------------------------
   |
   | Aquí es donde registrarás los servicios específicos de tu aplicación.
   | ¡Este es el lugar para añadir tus propias clases!
   |
   */
   Phast\App\Providers\AppServiceProvider::class,

];