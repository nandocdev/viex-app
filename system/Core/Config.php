<?php
/**
 * @package     phast/system
 * @subpackage  Core
 * @file        Config
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-19 10:43:37
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Core;

class Config {
   protected array $items = [];

   public function __construct(Application $app) {
      // Cargar todos los archivos de configuración del directorio /config
      $configPath = $app->basePath . '/config/';
      foreach (glob($configPath . '*.php') as $file) {
         $key = basename($file, '.php');
         $this->items[$key] = require $file;
      }
   }

   /**
    * Obtiene un valor de configuración usando notación de punto.
    * Ejemplo: config('app.name')
    */
   public function get(string $key, $default = null) {
      $keys = explode('.', $key);
      $value = $this->items;

      foreach ($keys as $segment) {
         if (!is_array($value) || !isset($value[$segment])) {
            return $default;
         }
         $value = $value[$segment];
      }

      return $value;
   }
}