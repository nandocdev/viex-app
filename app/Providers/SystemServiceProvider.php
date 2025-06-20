<?php
/**
 * @package     phast/app
 * @subpackage  Providers
 * @file        SystemServiceProvider
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-19 10:02:24
 * @version     1.0.0
 * @description
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

   Phast\System\Providers\LogServiceProvider::class,
   Phast\System\Providers\SystemServiceProvider::class,
   Phast\System\Providers\DatabaseServiceProvider::class,
   Phast\System\Providers\ViewServiceProvider::class,
   Phast\System\Providers\RoutingServiceProvider::class,
   Phast\System\Providers\SessionServiceProvider::class,

   // lee las configuraciones de la aplicación
   Phast\System\Providers\ConfigServiceProvider::class,

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