<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Matchers
 * @file        RouteMatcher.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Compara una petición con una colección de rutas y extrae los parámetros.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Matchers;

use Phast\System\Http\Request;
use Phast\System\Routing\Collectors\RouteCollector;

class RouteMatcher {
   public function match(Request $request, RouteCollector $collector): ?array {
      $method = $request->getMethod();
      $path = $request->getPath();

      foreach ($collector->getRoutes() as $route) {
         if (!in_array($method, $route['methods'])) {
            continue;
         }

         $params = $this->matchUri($route['uri'], $path);

         if ($params !== null) {
            return [
               'route' => $route,
               'params' => $params,
            ];
         }
      }

      return null;
   }

   private function matchUri(string $routeUri, string $requestUri): ?array {
      $pattern = $this->compileRouteToRegex($routeUri);


      if (preg_match($pattern, $requestUri, $matches)) {

         return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
      }

      return null;
   }

   private function compileRouteToRegex(string $routeUri): string {
      // debug($routeUri);
      $routeUri = str_replace('/', '\/', $routeUri);
      $pattern = preg_replace_callback(
         // La regex busca {param}, {param:constraint} y {param?}
         // '/\{([a-zA-Z0-9_]+)(?::([^\}]+))?(\??)\}/',
         '/\{([a-zA-Z0-9_]+)(?::([^\}]+))?(\??)\}/',
         function ($matches) {
            // $matches[1] = nombre del parámetro (ej: 'id')
            // $matches[2] = constraint de la regex (ej: '\d+'), si existe
            // $matches[3] = '?' si el parámetro es opcional, si existe
            // debug($matches);
            $paramName = $matches[1];
            $constraint = $matches[2] ?? '[^/]+'; // Por defecto, coincide con cualquier cosa excepto una barra
            $isOptional = !empty($matches[3]);

            // Construimos un grupo con nombre: (?<nombre>patron)
            $regexPart = "(?<{$paramName}>{$constraint})";

            // Si es opcional, envolvemos el grupo en un grupo no capturador opcional
            if ($isOptional) {
               // (?:/ ... )?  -> Hace que la barra precedente y el parámetro sean opcionales
               return '(?:/' . $regexPart . ')?';
            }
            // $result = '/' . $regexPart; // Asegura que el grupo comience con una barra
   
            // debug($result);
            // return $result;
            return $regexPart; // Asegura que el grupo comience con una barra
         },
         // Escapamos la URI para que los caracteres de regex no interfieran
         // preg_quote($routeUri, '#')
         // str_replace('/', '\/', $routeUri)
         $routeUri
      );

      $regex_result = '#^' . str_replace('\/', '/', $pattern) . '$#';

      // La regex resultante necesita los delimitadores y anclas de inicio/fin.
      return $regex_result;
   }
}