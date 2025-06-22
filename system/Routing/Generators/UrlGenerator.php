<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Generators
 * @file        UrlGenerator.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Genera URLs para rutas con nombre.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Generators;

use Phast\System\Routing\Exceptions\InvalidRouteException;

class UrlGenerator {
   /**
    * El array de rutas con nombre.
    * @var array
    */
   protected array $namedRoutes;

   /**
    * El host base para generar URLs absolutas (ej: http://localhost).
    * @var string
    */
   protected string $baseHost;

   /**
    * Constructor.
    *
    * @param array $namedRoutes Un array de rutas con nombre, pasado por el RouterManager.
    * @param string $baseHost El host base de la aplicación.
    */
   public function __construct(array &$namedRoutes, string $baseHost = '') {
      $this->namedRoutes = &$namedRoutes; // Pasado por referencia para eficiencia
      $this->baseHost = rtrim($baseHost, '/');
   }

   /**
    * Genera una URL para una ruta con nombre.
    *
    * @param string $name El nombre de la ruta.
    * @param array $parameters Los parámetros para la ruta.
    * @param bool $absolute Si se debe generar una URL absoluta.
    * @return string La URL generada.
    * @throws InvalidRouteException Si la ruta no existe o faltan parámetros.
    */
   public function route(string $name, array $parameters = [], bool $absolute = false): string {
      if (!isset($this->namedRoutes[$name])) {
         throw new InvalidRouteException("Route with name [{$name}] not defined.");
      }

      $uri = $this->namedRoutes[$name]['uri'];

      // Reemplaza los parámetros en la URI
      foreach ($parameters as $key => $value) {
         $uri = str_replace('{' . $key . '}', (string) $value, $uri);
      }

      // Elimina los parámetros opcionales que no se proporcionaron
      $uri = preg_replace('/\/\{[a-zA-Z0-9_]+\?\}/', '', $uri);

      // Verifica que no queden parámetros requeridos sin reemplazar
      if (str_contains($uri, '{')) {
         throw new InvalidRouteException("Missing required parameters for route [{$name}].");
      }

      return $absolute ? $this->baseHost . $uri : $uri;
   }
}