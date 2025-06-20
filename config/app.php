<?php
/**
 * @package     Phast
 * @subpackage  Config
 * @file        app.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-10
 * @version     1.1.0
 * @description Configuración principal de la aplicación Phast.
 *              Este archivo lee las variables de entorno (.env) y proporciona
 *              valores por defecto.
 */

declare(strict_types=1);

return [

   /*
   |--------------------------------------------------------------------------
   | Nombre de la Aplicación
   |--------------------------------------------------------------------------
   */
   'name' => $_ENV['APP_NAME'] ?? 'Phast',

   /*
   |--------------------------------------------------------------------------
   | Versión de la Aplicación
   |--------------------------------------------------------------------------
   | Este valor es opcional y se puede usar para mostrar en la UI o en cabeceras.
   */
   'version' => '1.0.0',

   /*
   |--------------------------------------------------------------------------
   | Entorno de la Aplicación
   |--------------------------------------------------------------------------
   | Determina el entorno en el que se ejecuta la aplicación.
   | Puede ser 'local', 'staging', 'production'.
   */
   'env' => $_ENV['APP_ENV'] ?? 'production',

   /*
   |--------------------------------------------------------------------------
   | Modo de Depuración
   |--------------------------------------------------------------------------
   | Cuando está habilitado, se mostrarán errores detallados.
   | ¡NUNCA debe estar habilitado en producción!
   */
   'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),

   /*
   |--------------------------------------------------------------------------
   | URL de la Aplicación
   |--------------------------------------------------------------------------
   | Esta URL es utilizada por la consola y para generar URLs absolutas.
   | Asegúrate de que no tenga una barra al final.
   */
   'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/'),

   /*
   |--------------------------------------------------------------------------
   | Clave de Encriptación de la Aplicación
   |--------------------------------------------------------------------------
   | Esta clave se utiliza para la encriptación y debe ser una cadena
   | aleatoria de 32 caracteres. ¡Mantenla segura!
   */
   'key' => $_ENV['APP_KEY'] ?? 'SET_YOUR_APP_KEY_IN_ENV_FILE',

   /*
   |--------------------------------------------------------------------------
   | Zona Horaria y Localización
   |--------------------------------------------------------------------------
   */
   'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
   'locale' => $_ENV['APP_LOCALE'] ?? 'en',

   /*
   |--------------------------------------------------------------------------
   | Configuración de Caché de Rutas
   |--------------------------------------------------------------------------
   | Habilitar esto en producción mejorará drásticamente el rendimiento.
   */
   'route_cache_enabled' => filter_var($_ENV['ROUTE_CACHE_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),

   /*
   |--------------------------------------------------------------------------
   | Proveedores de Servicios
   |--------------------------------------------------------------------------
   |
   | Los proveedores de servicios son el lugar central para registrar
   | todas las dependencias de tu aplicación en el contenedor.
   | (Esta es una mejora futura sugerida para organizar los 'bindings').
   |
   */
   'providers' => [
      // App\Providers\DatabaseServiceProvider::class,
      // App\Providers\AuthServiceProvider::class,
   ],



];