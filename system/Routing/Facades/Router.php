<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Facades
 * @file        Router.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Fachada estática para una interacción sencilla con el sistema de enrutamiento.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Facades;

use Phast\System\Core\Container;
use Phast\System\Routing\RouterManager;
use Phast\System\Routing\Collectors\RouteCollector;

/**
 * Proporciona una API estática y fluida para definir rutas.
 *
 * Esta clase actúa como un proxy al RouterManager y al RouteCollector
 * que están registrados en el contenedor de dependencias.
 *
 * @method static \System\Routing\Collectors\RouteCollector get(string $uri, array|string|\Closure $action)
 * @method static \System\Routing\Collectors\RouteCollector post(string $uri, array|string|\Closure $action)
 * @method static \System\Routing\Collectors\RouteCollector put(string $uri, array|string|\Closure $action)
 * @method static \System\Routing\Collectors\RouteCollector patch(string $uri, array|string|\Closure $action)
 * @method static \System\Routing\Collectors\RouteCollector delete(string $uri, array|string|\Closure $action)
 * @method static void group(array $attributes, \Closure $callback)
 * @method static string route(string $name, array $parameters = [], bool $absolute = false)
 */
class Router {
   /**
    * El nombre de la clase del gestor principal en el contenedor.
    * @var string
    */
   protected const MANAGER_ALIAS = RouterManager::class;

   /**
    * El nombre de la clase del recolector en el contenedor.
    * @var string
    */
   protected const COLLECTOR_ALIAS = RouteCollector::class;

   /**
    * Maneja las llamadas a métodos estáticos en la fachada.
    *
    * @param string $method El nombre del método llamado (e.g., 'get', 'post', 'group').
    * @param array $arguments Los argumentos pasados al método.
    * @return mixed
    */
   public static function __callStatic(string $method, array $arguments) {
      // 1. Obtener la instancia del RouterManager desde el contenedor.
      $manager = Container::getInstance()->resolve(self::MANAGER_ALIAS);

      // 2. Determinar a qué objeto delegar la llamada.

      // Los métodos de definición de rutas (get, post, group, etc.) pertenecen al colector.
      if (method_exists($manager->collector, $method)) {
         return $manager->collector->$method(...$arguments);
      }

      // El método para generar URLs ('route') pertenece al RouterManager,
      // que a su vez delega en el UrlGenerator.
      if ($method === 'route') {
         return $manager->generateUrl(...$arguments);
      }

      // Si el método no se encuentra en ninguno, lanzamos un error.
      throw new \BadMethodCallException(sprintf(
         'Static method %s::%s does not exist.', static::class, $method
      ));
   }
}