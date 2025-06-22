<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        SystemServiceProvider
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-11
 * @version     1.0.0
 * @description Registra los servicios más fundamentales del framework, como Request y Response.
 */

declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Http\Request;
use Phast\System\Http\Response;

class SystemServiceProvider implements ServiceProviderInterface {
   /**
    * Registra los servicios del sistema en el contenedor.
    *
    * Este provider se encarga de los componentes HTTP básicos que son la
    * columna vertebral de cualquier interacción web.
    *
    * Nota: La Application y el propio Container se registran en el constructor
    * de la clase Application, ya que son necesarios antes de que cualquier
    * provider pueda ser siquiera instanciado.
    *
    * @param Container $container
    * @return void
    */
   public function register(Container $container): void {
      // 1. Registrar el objeto Request como un singleton.
      // Solo debe haber una instancia del objeto Request por cada ciclo de
      // vida de la petición. Usamos el método estático `createFromGlobals`
      // para encapsular la interacción con las variables superglobales de PHP,
      // permitiendo que la clase Request sea testeable.
      $container->singleton(Request::class, function () {
         // Asumiendo que has refactorizado Request como discutimos
         // para que no dependa directamente de superglobales en su constructor.
         return Request::createFromGlobals();
      });

      // 2. Registrar el objeto Response como un singleton.
      // Aunque se pueden crear nuevas instancias de Response, el objeto principal
      // que se pasará a través de la aplicación debe ser único para ir
      // acumulando el estado (cuerpo, headers, status code).
      $container->singleton(Response::class, function () {
         return new Response();
      });
   }
}