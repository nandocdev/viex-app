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