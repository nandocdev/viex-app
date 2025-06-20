<?php
/**
 * @package     phast/system
 * @file        helpers
 * @description Funciones de ayuda globales.
 */

use Phast\System\Core\Container;
use Phast\System\Plugins\Session\SessionManager;

if (!function_exists('csrf_token')) {
   /**
    * Obtiene el token CSRF actual.
    */
   function csrf_token(): string {
      return Container::getInstance()->resolve(SessionManager::class)->getToken() ?? '';
   }
}

if (!function_exists('csrf_field')) {
   /**
    * Genera un campo de formulario HTML oculto con el token CSRF.
    */
   function csrf_field(): string {
      return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
   }
}


// En un archivo de helpers que cargues en tu bootstrap o composer.json
if (!function_exists('config')) {
   function config(string $key, $default = null) {
      return Phast\System\Core\Container::getInstance()
         ->resolve(Phast\System\Core\Config::class)
         ->get($key, $default);
   }
}

// debug helper
if (!function_exists('debug')) {
   function debug(...$data): void {
      echo '<pre>';
      var_dump(...$data);
      echo '</pre>';
   }
}