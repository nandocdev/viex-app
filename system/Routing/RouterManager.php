<?php

/**
 * @package     Phast/System
 * @subpackage  Routing
 * @file        RouterManager.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Orquesta el proceso completo de enrutamiento: carga, matching y despacho.
 */

declare(strict_types=1);

namespace Phast\System\Routing;

use Phast\System\Core\Application;
use Phast\System\Core\Container;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\Cache\RouteCache;
use Phast\System\Routing\Collectors\RouteCollector;
use Phast\System\Routing\Exceptions\RouteNotFoundException;
use Phast\System\Routing\Generators\UrlGenerator;
use Phast\System\Routing\Matchers\RouteMatcher;
use Phast\System\Routing\Middleware\MiddlewareDispatcher;
use Phast\System\Routing\Resolvers\HandlerResolver;

class RouterManager {
   protected bool $routesLoaded = false;
   protected bool $cacheEnabled = false;

   // Los componentes del sistema de enrutamiento
   public readonly RouteCollector $collector;
   protected RouteMatcher $matcher;
   protected HandlerResolver $resolver;
   protected MiddlewareDispatcher $dispatcher;
   protected UrlGenerator $urlGenerator;
   protected RouteCache $cache;

   /**
    * Constructor. Recibe todas sus dependencias a través de inyección.
    */
   public function __construct(
      protected Container $container,
      protected Application $app
   ) {
      // Instanciamos todos los componentes necesarios del enrutador.
      // El RouterManager actúa como una fábrica y un orquestador.
      $this->collector = new RouteCollector();
      $this->matcher = new RouteMatcher();
      $this->resolver = new HandlerResolver($this->container);
      $this->dispatcher = new MiddlewareDispatcher($this->container);

      // La caché necesita saber dónde guardar los archivos.
      $cachePath = $this->app->basePath . '/storage/cache/routes.php';
      $this->cache = new RouteCache($cachePath);

      // El generador de URL necesita acceso a las rutas con nombre y la URL base de la app.
      // Pasamos el array por referencia para que cualquier cambio se refleje.
      $baseHost = $_ENV['APP_URL'] ?? '';
      
      // Pass the collector instance to the UrlGenerator
      $this->urlGenerator = new UrlGenerator($this->collector, $baseHost);
   }

   /**
    * Habilita o deshabilita el uso de la caché de rutas.
    */
   public function enableCache(bool $enabled = true): void {
      $this->cacheEnabled = $enabled;
   }

   /**
    * Resuelve la petición actual, encontrando y despachando la ruta correspondiente.
    * Este es el método principal que inicia todo el proceso.
    *
    * @param Request $request La petición HTTP.
    * @return Response La respuesta generada.
    * @throws RouteNotFoundException Si no se encuentra ninguna ruta.
    */
   public function resolve(Request $request): mixed {
      $this->loadRoutesFromFiles();

      $matched = $this->matcher->match($request, $this->collector);

      // print_r($matched);
      if (is_null($matched)) {
         throw new RouteNotFoundException("No route found for {$request->getMethod()} {$request->getPath()} with match parameters: " . json_encode($matched));
      }

      $route = $matched['route'];
      $params = $matched['params'];


      // --- LÍNEA CLAVE A AÑADIR ---
      // Le pasamos los parámetros de la ruta al objeto Request.
      $request->setRouteParams($params);
      // ----------------------------

      // Prepara la acción del controlador
      $handler = $this->resolver->resolve($route['action']);

      // Crea la closure final que ejecuta el controlador con sus parámetros
      $finalHandler = function (Request $request, Response $response) use ($handler, $params) {
         $request->input('input', $params); // Inyecta los parámetros en la petición
         return call_user_func_array($handler, array_merge([$request, $response]));
      };

      // Despacha la petición a través de la pila de middleware
      return $this->dispatcher->dispatch($route['middleware'], $request, $finalHandler);
   }

   /**
    * Genera una URL para una ruta con nombre. Delega en el UrlGenerator.
    */
   public function generateUrl(string $name, array $parameters = [], bool $absolute = false): string {
      $this->loadRoutesFromFiles(); // Asegura que las rutas con nombre estén cargadas.
      return $this->urlGenerator->route($name, $parameters, $absolute);
   }

   /**
    * Carga las definiciones de rutas desde los archivos, usando la caché si está habilitada.
    */
   protected function loadRoutesFromFiles(): void {
      if ($this->routesLoaded) {
         return;
      }

      if ($this->cacheEnabled && $this->cache->exists()) {
         $cached = $this->cache->load();
         $this->collector->setAllRoutes($cached['routes'], $cached['namedRoutes']);
      } else {
         // Carga el archivo principal de rutas. Aquí es donde el usuario define las rutas.
         $routesFile = $this->app->basePath . '/routes/web.php';
         if (file_exists($routesFile)) {
            // Hacemos que la fachada del Router esté disponible dentro del archivo de rutas.
            $router = $this->container->resolve(\Phast\System\Routing\Facades\Router::class);
            require $routesFile;
         }

         if ($this->cacheEnabled) {
            $this->cache->save($this->collector->getRoutes(), $this->collector->getNamedRoutes());
         }
      }

      $this->routesLoaded = true;
   }

   /**
    * Borra la caché de rutas.
    */
   public function clearCache(): bool {
      return $this->cache->clear();
   }
}
