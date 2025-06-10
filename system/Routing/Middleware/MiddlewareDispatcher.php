<?php

/**
 * @package     Phast/System
 * @subpackage  Routing/Middleware
 * @file        MiddlewareDispatcher.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Ejecuta una pila de middleware usando el patrón de pipeline (cebolla).
 */

declare(strict_types=1);

namespace Phast\System\Routing\Middleware;

use Closure;
use Phast\System\Core\Container;
use Phast\System\Http\Request;
use Phast\System\Http\Response;

class MiddlewareDispatcher {
   /**
    * El namespace base para todos los middlewares.
    * @var string
    */
   protected string $middlewareNamespace = 'App\\Middleware\\';

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
    * Despacha una petición a través de una pila de middleware.
    *
    * @param array $middlewareStack La pila de nombres de clases de middleware.
    * @param Request $request La petición HTTP.
    * @param Closure $finalHandler El handler final (el controlador) a ejecutar al final de la pila.
    * @return Response La respuesta generada.
    */
   public function dispatch(array $middlewareStack, Request $request, Closure $finalHandler): mixed {
      // El "corazón de la cebolla" es el handler del controlador.
      // Si no hay middleware, este se ejecutará directamente.
      $runner = $finalHandler;

      // Construimos la cadena de Closures de adentro hacia afuera,
      // envolviendo el runner con cada capa de middleware.
      foreach (array_reverse($middlewareStack) as $middlewareClass) {
         $runner = $this->createLayer($middlewareClass, $runner);
      }

      // Ejecutamos la cadena completa, comenzando por la capa más externa.
      return $runner($request, new Response());
   }



   /**
    * Crea una "capa" de middleware como una Closure.
    *
    * @param string $middlewareClass El nombre de la clase del middleware.
    * @param Closure $next La siguiente capa (la que está más "adentro" en la cebolla).
    * @return Closure La nueva capa externa.
    */
   protected function createLayer(string $middlewareClass, Closure $next): Closure {
      return function (Request $request) use ($middlewareClass, $next) {
         // 1. Resolver el middleware usando el contenedor de DI.
         $className = $this->middlewareNamespace . $middlewareClass;
         $middlewareInstance = $this->container->resolve($className);

         // 2. Llamar al método `handle` del middleware, pasándole la petición
         //    y la siguiente capa (`$next`) para que la ejecute cuando quiera.
         return $middlewareInstance->handle($request, $next);
      };
   }
}
