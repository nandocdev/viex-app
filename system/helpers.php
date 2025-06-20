<?php
/**
 * @package     phast/system
 * @file        helpers
 * @description Funciones de ayuda globales.
 */

use Phast\System\Core\Container;
use Phast\System\Plugins\Session\SessionManager;
use Phast\System\Rendering\Components\AvatarComponent as Avatar;
use Phast\System\Rendering\Components\AcctionButtonsComponent as AcctionButtons;

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

if (!function_exists('avatar')) {
   function avatar(string $name, string $surname): Avatar {
      return new Avatar($name, $surname);
   }
}

if (!function_exists('action_buttons')) {
   function action_buttons(array $buttons = [], array $params = []): string {
      // El método estático se adapta bien aquí
      return AcctionButtons::init($buttons, $params);
   }
}

// camel_case 
if (!function_exists('camel_case')) {
   /**
    * Convierte un string a formato CamelCase.
    */
   function camel_case(string $string, bool $uppercase = false): string {
      $result = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
      return $uppercase ? ucfirst($result) : $result;
   }
}

if (!function_exists('snake_case')) {
   /**
    * Convierte un string a formato snake_case.
    */
   function snake_case(string $string): string {
      return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($string)));
   }
}
if (!function_exists('assets')) {
   /**
    * Genera una URL para un recurso estático.
    */
   function assets(string $path): string {
      return config('app.assets_url', '') . '/' . ltrim($path, '/');
   }
}