<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        RoutingServiceProvider
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-11
 * @version     1.0.0
 * @description Registra el gestor de enrutamiento y todos sus componentes asociados.
 */

declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Application;
use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Routing\RouterManager;

class RoutingServiceProvider implements ServiceProviderInterface {
   /**
    * Registra los servicios de enrutamiento en el contenedor.
    *
    * @param Container $container
    * @return void
    */
   public function register(Container $container): void {
      // 1. Registrar el RouterManager como un singleton.
      // El RouterManager es el orquestador principal del sistema de enrutamiento.
      // Gestiona la carga de rutas, el matching, la resolución de controladores
      // y el despacho de middleware. Solo debe existir una instancia de este gestor
      // por ciclo de aplicación.
      //
      // Depende del Contenedor (para resolver dependencias de controladores y middleware)
      // y de la Aplicación (para obtener rutas base y configuraciones).
      $container->singleton(RouterManager::class, function (Container $c) {
         return new RouterManager(
            $c, // Pasa el propio contenedor
            $c->resolve(Application::class)
         );
      });

      // NOTA: No es necesario registrar individualmente RouteCollector, RouteMatcher,
      // HandlerResolver, etc., en el contenedor. El RouterManager los instancia
      // y los gestiona internamente. Esto es un buen ejemplo del patrón de Fachada
      // o Fábrica, donde el RouterManager simplifica la creación y el acceso
      // a un subsistema complejo.
   }
}