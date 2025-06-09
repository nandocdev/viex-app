<?php

/**
 * @package     phast/system
 * @subpackage  Routing
 * @file        Router
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:34:48
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Routing;


use Closure;
use Phast\System\Core\Container;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\Exceptions\InvalidRouteException;
use Phast\System\Routing\Exceptions\RouteNotFoundException;

/**
 * El enrutador avanzado de Phast.
 * Soporta verbos HTTP, grupos, middleware, rutas con nombre,
 * generación de URL y caché de rutas.
 */
class Router {
   protected array $routes = [];
   protected array $namedRoutes = [];
   protected array $groupStack = [];
   protected string $cachePath;
   protected bool $cacheEnabled = false;
   protected bool $routesLoaded = false;

   public function __construct(protected Container $container) {
      $basePath = $this->container->resolve(\Phast\System\Core\Application::class)->basePath;
      // Crea un directorio de caché si no existe.
      $this->cachePath = $basePath . '/storage/cache/routes.php';
   }

   // --- Definición de Rutas (API Pública) ---

   public function get(string $uri, array|string|Closure $action): self {
      return $this->addRoute(['GET', 'HEAD'], $uri, $action);
   }

   public function post(string $uri, array|string|Closure $action): self {
      return $this->addRoute(['POST'], $uri, $action);
   }

   public function put(string $uri, array|string|Closure $action): self {
      return $this->addRoute(['PUT'], $uri, $action);
   }

   public function patch(string $uri, array|string|Closure $action): self {
      return $this->addRoute(['PATCH'], $uri, $action);
   }

   public function delete(string $uri, array|string|Closure $action): self {
      return $this->addRoute(['DELETE'], $uri, $action);
   }

   public function any(string $uri, array|string|Closure $action): self {
      return $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
   }

   public function group(array $attributes, Closure $callback): void {
      $this->groupStack[] = $attributes;
      $callback($this);
      array_pop($this->groupStack);
   }

   public function name(string $name): self {
      $lastKey = array_key_last($this->routes);
      if ($lastKey !== null) {
         $this->routes[$lastKey]['name'] = $name;
         $this->namedRoutes[$name] = &$this->routes[$lastKey];
      }
      return $this;
   }

   public function middleware(array|string $middleware): self {
      $lastKey = array_key_last($this->routes);
      if ($lastKey !== null) {
         $middleware = is_string($middleware) ? [$middleware] : $middleware;
         $this->routes[$lastKey]['middleware'] = array_merge(
            $this->routes[$lastKey]['middleware'],
            $middleware
         );
      }
      return $this;
   }

   // --- Lógica Interna y Resolución ---

   protected function addRoute(array $methods, string $uri, mixed $action): self {
      $prefix = $this->getCurrentGroupAttribute('prefix', '');
      $uri = rtrim($prefix, '/') . '/' . ltrim($uri, '/');
      $uri = '/' . ltrim($uri, '/');
      if ($uri === '//') $uri = '/';

      $route = [
         'methods' => $methods,
         'uri' => $uri,
         'action' => $action,
         'middleware' => $this->getCurrentGroupAttribute('middleware', []),
         'name' => null
      ];

      $this->routes[] = $route;
      return $this;
   }

   public function resolve(): Response {
      $this->loadRoutes();

      $request = $this->container->resolve(Request::class);
      $method = $request->getMethod();
      $path = $request->getPath();

      foreach ($this->routes as $route) {
         if (in_array($method, $route['methods']) && $params = $this->matchRoute($route, $path)) {
            return $this->dispatch($route, $params);
         }
      }

      throw new RouteNotFoundException("Route not found for {$method} {$path}");
   }

   protected function matchRoute(array $route, string $path): array|false {
      $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $route['uri']);
      $pattern = "#^" . $pattern . "$#";

      if (preg_match($pattern, $path, $matches)) {
         return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
      }
      return false;
   }

   protected function dispatch(array $route, array $params): Response {
      $request = $this->container->resolve(Request::class);
      $finalHandler = $this->resolveHandler($route['action'], $params);
      $middleware = $route['middleware'];

      // Implementación del Middleware Pipeline (Patrón Cebolla)
      $runner = array_reduce(
         array_reverse($middleware),
         $this->createMiddlewareLayer(),
         $finalHandler
      );

      return $runner($request);
   }

   private function createMiddlewareLayer(): Closure {
      return function ($next, $pipe) {
         return function (Request $request) use ($next, $pipe) {
            // Usamos el contenedor para instanciar el middleware
            $middlewareInstance = $this->container->resolve("App\\Middleware\\{$pipe}");
            return $middlewareInstance->handle($request, $next);
         };
      };
   }

   protected function resolveHandler(mixed $action, array $params): Closure {
      return function (Request $request) use ($action, $params) {
         if ($action instanceof Closure) {
            return call_user_func_array($action, array_merge([$request], $params));
         }

         if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);
            $controller = "App\\Controllers\\{$controller}";

            // ¡Magia de DI! El contenedor resuelve el controlador y sus dependencias.
            $controllerInstance = $this->container->resolve($controller);

            // Los parámetros de la ruta se pasan al método del controlador.
            return call_user_func_array([$controllerInstance, $method], $params);
         }
         throw new InvalidRouteException("Invalid route action.");
      };
   }

   // --- Generación de URL ---

   public function route(string $name, array $parameters = []): string {
      $this->loadRoutes();

      if (!isset($this->namedRoutes[$name])) {
         throw new InvalidRouteException("Route [{$name}] not defined.");
      }

      $uri = $this->namedRoutes[$name]['uri'];
      foreach ($parameters as $key => $value) {
         $uri = str_replace("{{$key}}", (string)$value, $uri);
      }

      if (str_contains($uri, '{')) {
         throw new InvalidRouteException("Missing parameters for route [{$name}].");
      }
      return $uri;
   }

   // --- Carga y Caché de Rutas ---

   public function enableCache(bool $enabled = true): void {
      $this->cacheEnabled = $enabled;
   }

   protected function loadRoutes(): void {
      if ($this->routesLoaded) {
         return;
      }

      if ($this->cacheEnabled && file_exists($this->cachePath)) {
         $cached = require $this->cachePath;
         $this->routes = $cached['routes'];
         $this->namedRoutes = $cached['namedRoutes'];
      } else {
         // Carga los archivos de rutas de la aplicación.
         require $this->container->resolve(\Phast\System\Core\Application::class)->basePath . '/routes/web.php';

         if ($this->cacheEnabled) {
            $this->cacheRoutes();
         }
      }
      $this->routesLoaded = true;
   }

   public function cacheRoutes(): void {
      $dir = dirname($this->cachePath);
      if (!is_dir($dir)) mkdir($dir, 0755, true);

      $data = [
         'routes' => $this->routes,
         'namedRoutes' => $this->namedRoutes
      ];
      // Usamos var_export para generar un archivo PHP, que es más rápido que unserialize.
      file_put_contents($this->cachePath, '<?php return ' . var_export($data, true) . ';');
   }

   public function clearCache(): bool {
      if (file_exists($this->cachePath)) {
         return unlink($this->cachePath);
      }
      return false;
   }

   // --- Funciones de Ayuda ---

   protected function getCurrentGroupAttribute(string $key, $default) {
      if (empty($this->groupStack)) {
         return $default;
      }

      $merged = [];
      if ($key === 'prefix') {
         return implode('', array_column($this->groupStack, 'prefix'));
      }

      if ($key === 'middleware') {
         return array_merge([], ...array_column($this->groupStack, 'middleware'));
      }

      return $default;
   }
}
