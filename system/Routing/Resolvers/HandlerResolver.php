<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Resolvers
 * @file        HandlerResolver.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Resuelve la acción de una ruta a un callable ejecutable.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Resolvers;

use Closure;
use Phast\System\Core\Container;
use Phast\System\Routing\Exceptions\InvalidRouteException;

class HandlerResolver {
   /**
    * El namespace base para todos los controladores.
    * @var string
    */
   protected string $controllerNamespace = 'App\\Controllers\\';

   /**
    * El contenedor de inyección de dependencias.
    * @var Container
    */
   protected Container $container;

   /**
    * Constructor.
    *
    * @param Container $container La instancia del contenedor de DI.
    */
   public function __construct(Container $container) {
      $this->container = $container;
   }

   /**
    * Resuelve el handler de una ruta y lo convierte en un callable.
    *
    * @param mixed $handler El handler a resolver (Closure o string 'Controller@method').
    * @return callable El handler listo para ser ejecutado.
    * @throws InvalidRouteException Si el handler no es válido.
    */
   public function resolve(mixed $handler): callable {
      // Caso 1: El handler ya es una Closure. No hay nada que hacer.
      if ($handler instanceof Closure) {
         return $handler;
      }

      // Caso 2: El handler es un string con el formato 'Controller@method'.
      if (is_string($handler) && str_contains($handler, '@')) {
         return $this->resolveStringHandler($handler);
      }

      // Si no es ninguno de los anteriores, es un formato inválido.
      throw new InvalidRouteException('Invalid route handler. Must be a Closure or a "Controller@method" string.');
   }

   /**
    * Resuelve un handler en formato string 'Controller@method'.
    *
    * @param string $handlerString El string del handler.
    * @return callable Un array con la instancia del controlador y el nombre del método.
    * @throws InvalidRouteException Si la clase o el método no existen.
    */
   protected function resolveStringHandler(string $handlerString): callable {
      // 1. Separar la clase del método.
      [$class, $method] = explode('@', $handlerString, 2);

      // 2. Construir el nombre de la clase completamente cualificado.
      $className = $this->controllerNamespace . $class;

      // 3. Verificar que la clase del controlador exista.
      if (!class_exists($className)) {
         throw new InvalidRouteException("Controller class [{$className}] not found.");
      }

      // 4. Usar el contenedor de DI para instanciar el controlador.
      // ¡Esta es la magia! El contenedor resolverá las dependencias del constructor.
      try {
         $controllerInstance = $this->container->resolve($className);
      } catch (\Exception $e) {
         // Captura errores de resolución del contenedor para dar un mensaje más claro.
         throw new InvalidRouteException("Could not resolve controller [{$className}]. Error: " . $e->getMessage(), 0, $e);
      }

      // 5. Verificar que el método exista en la instancia del controlador.
      if (!method_exists($controllerInstance, $method)) {
         throw new InvalidRouteException("Method [{$method}] does not exist on controller [{$className}].");
      }

      // 6. Devolver el callable en formato de array: [objeto, 'nombreMetodo']
      return [$controllerInstance, $method];
   }
}