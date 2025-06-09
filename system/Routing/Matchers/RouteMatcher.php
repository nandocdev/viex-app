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
   /**
    * Busca en la colección de rutas una que coincida con la petición actual.
    *
    * @param Request $request La petición HTTP actual.
    * @param RouteCollector $collector El colector que contiene todas las rutas.
    * @return array|null Un array con la ruta encontrada y sus parámetros, o null si no hay coincidencia.
    */
   public function match(Request $request, RouteCollector $collector): ?array {
      $method = $request->getMethod();
      $path = $request->getPath();

      foreach ($collector->getRoutes() as $route) {
         // 1. Comprobar si el método HTTP coincide.
         if (!in_array($method, $route['methods'])) {
            continue;
         }

         // 2. Comprobar si la URI coincide y extraer parámetros.
         $params = $this->matchUri($route['uri'], $path);

         if ($params !== null) {
            // ¡Coincidencia! Devolvemos la ruta y los parámetros extraídos.
            return [
               'route' => $route,
               'params' => $params,
            ];
         }
      }

      // No se encontró ninguna ruta.
      return null;
   }

   /**
    * Compara una URI con un patrón de ruta y extrae los parámetros si coincide.
    *
    * @param string $routeUri El patrón de la ruta (ej: /users/{id:\d+}).
    * @param string $requestUri La URI de la petición actual (ej: /users/123).
    * @return array|null Un array con los parámetros o null si no coincide.
    */
   private function matchUri(string $routeUri, string $requestUri): ?array {
      $pattern = $this->compileRouteToRegex($routeUri);

      if (preg_match($pattern, $requestUri, $matches)) {
         // Filtra los resultados para quedarnos solo con los grupos con nombre.
         // Esto elimina las claves numéricas del array de coincidencias.
         return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
      }

      return null;
   }

   /**
    * Compila un patrón de ruta en una expresión regular completa.
    * Soporta:
    * - Parámetros simples: {id}
    * - Parámetros con constraints: {id:\d+}
    * - Parámetros opcionales: {slug?} (No recomendado para claridad, pero posible)
    *
    * @param string $routeUri El patrón de la ruta.
    * @return string La expresión regular compilada.
    */
   private function compileRouteToRegex(string $routeUri): string {
      $pattern = preg_replace_callback(
         // La regex busca {param}, {param:constraint} y {param?}
         '/\{([a-zA-Z0-9_]+)(?::([^\}]+))?(\??)\}/',
         function ($matches) {
            // $matches[1] = nombre del parámetro (ej: 'id')
            // $matches[2] = constraint de la regex (ej: '\d+'), si existe
            // $matches[3] = '?' si el parámetro es opcional, si existe
   
            $paramName = $matches[1];
            $constraint = $matches[2] ?? '[^/]+'; // Por defecto, coincide con cualquier cosa excepto una barra
            $isOptional = !empty($matches[3]);

            // Construimos un grupo con nombre: (?<nombre>patron)
            $regexPart = '(?<' . $paramName . '>' . $constraint . ')';

            // Si es opcional, envolvemos el grupo en un grupo no capturador opcional
            if ($isOptional) {
               // (?:/ ... )?  -> Hace que la barra precedente y el parámetro sean opcionales
               return '(?:/' . $regexPart . ')?';
            }

            return '/' . $regexPart;
         },
         // Escapamos la URI para que los caracteres de regex no interfieran
         preg_quote($routeUri, '#')
      );

      // La regex resultante necesita los delimitadores y anclas de inicio/fin.
      return '#^' . str_replace('\/', '/', $pattern) . '$#';
   }
}