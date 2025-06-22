<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Collectors
 * @file        RouteCollector.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Recopila y gestiona las definiciones de rutas de la aplicación.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Collectors;

use Closure;
use Phast\System\Routing\Collectors\RouteGroup;
use Phast\System\Routing\Exceptions\InvalidRouteException;
/**
 * Clase que recopila y gestiona las rutas de la aplicación.
 *
 * Permite definir rutas con diferentes verbos HTTP, agrupar rutas con atributos
 * compartidos (como prefijos y middleware), y asignar nombres a las rutas.
 */

class RouteCollector {
   /**
    * Array que contiene todas las rutas registradas.
    * @var array
    */
   protected array $routes = [];

   /**
    * Array para acceso rápido a rutas por su nombre.
    * La clave es el nombre de la ruta, el valor es una referencia a la ruta en $routes.
    * @var array
    */
   protected array $namedRoutes = [];

   /**
    * Pila de grupos de rutas activos para manejar anidamiento.
    * @var RouteGroup[]
    */
   protected array $groupStack = [];

   // --- Métodos de Verbos HTTP ---

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

   // --- Métodos de Gestión de Rutas ---

   /**
    * Define un grupo de rutas con atributos compartidos.
    *
    * @param array $attributes Atributos del grupo (ej: ['prefix' => '/api', 'middleware' => ['Auth']]).
    * @param Closure $callback Una función que define las rutas dentro del grupo.
    */
   public function group(array $attributes, Closure $callback): void {
      // Añade el nuevo grupo a la pila
      $this->groupStack[] = new RouteGroup(
         $attributes['prefix'] ?? '',
         (array) ($attributes['middleware'] ?? [])
      );

      // Ejecuta el callback para registrar las rutas del grupo
      $callback($this);

      // Elimina el grupo de la pila una vez que sus rutas han sido registradas
      array_pop($this->groupStack);
   }

   /**
    * Asigna un nombre a la última ruta registrada.
    *
    * @param string $name El nombre de la ruta.
    * @return self
    */
   public function name(string $name): self {
      $lastKey = array_key_last($this->routes);
      if ($lastKey !== null) {
         $this->routes[$lastKey]['name'] = $name;
         // Guardamos una referencia a la ruta para una búsqueda rápida
         $this->namedRoutes[$name] = &$this->routes[$lastKey];
      }
      return $this;
   }

   /**
    * Asigna middleware a la última ruta registrada.
    *
    * @param array|string $middleware El nombre de la clase del middleware o un array de nombres.
    * @return self
    */
   public function middleware(array|string $middleware): self {
      $lastKey = array_key_last($this->routes);
      if ($lastKey !== null) {
         $this->routes[$lastKey]['middleware'] = array_merge(
            $this->routes[$lastKey]['middleware'],
            (array) $middleware
         );
      }
      return $this;
   }

   // --- Lógica Interna y Ayudantes ---

   /**
    * Método central para añadir una nueva ruta a la colección.
    *
    * @param array $methods Array de métodos HTTP.
    * @param string $uri La URI de la ruta.
    * @param mixed $action La acción a ejecutar.
    * @return self
    */
   protected function addRoute(array $methods, string $uri, mixed $action): self {
      $prefix = $this->getCurrentPrefix(); // sin formatUri aún
      $uri = $this->joinUri($prefix, $uri); // une limpiamente
      $uri = $this->normalizeUri($uri);     // si quieres dejarlo

      $this->routes[] = [
         'methods' => $methods,
         'uri' => $uri,
         'action' => $action,
         'middleware' => $this->getCurrentMiddlewareStack(),
         'name' => null,
      ];

      return $this;
   }

   private function joinUri(string $prefix, string $uri): string {
      return '/' . trim(rtrim($prefix, '/') . '/' . ltrim($uri, '/'), '/');
   }




   /**
    * Normaliza una URI eliminando barras duplicadas y la barra final (excepto para '/').
    */
   private function normalizeUri(string $uri): string {
      // Reemplaza múltiples slashes internos
      $uri = preg_replace('#/+#', '/', $uri);
      $uri = '/' . trim($uri, '/');
      return $uri === '/' ? $uri : rtrim($uri, '/');
   }
   /**
    * Obtiene el prefijo acumulado de todos los grupos en la pila.
    */
   private function getCurrentPrefix(): string {
      $prefix = '';
      foreach ($this->groupStack as $group) {
         $prefix .= '/' . trim($group->prefix, '/');
      }
      return $prefix;
   }

   /**
    * Obtiene todo el middleware acumulado de los grupos en la pila.
    */
   private function getCurrentMiddlewareStack(): array {
      $middleware = [];
      foreach ($this->groupStack as $group) {
         $middleware = array_merge($middleware, $group->middleware);
      }
      return $middleware;
   }

   // --- Getters para otros componentes del router ---

   public function getRoutes(): array {
      return $this->routes;
   }

   public function getNamedRoutes(): array {
      return $this->namedRoutes;
   }

   /**
    * Establece todas las rutas a la vez (usado por el sistema de caché).
    */
   public function setAllRoutes(array $routes, array $namedRoutes): void {
      $this->routes = $routes;
      $this->namedRoutes = $namedRoutes;
   }
}