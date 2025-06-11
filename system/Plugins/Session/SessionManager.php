<?php
/**
 * @package     phast/system
 * @subpackage  Session
 * @file        SessionManager
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-11 11:00:00
 * @version     1.0.0
 * @description Manejador de sesiones para Phast Framework.
 */

declare(strict_types=1);

namespace Phast\System\Plugins\Session;

class SessionManager {
   private static bool $sessionStarted = false;

   /**
    * Inicia o reanuda la sesión PHP con configuraciones seguras.
    * Los parámetros de la cookie de sesión se obtienen de las variables de entorno.
    */
   public function start(): void {
      if (self::$sessionStarted) {
         return;
      }

      if (session_status() === PHP_SESSION_NONE) {
         // Obtener configuraciones de las variables de entorno
         // Asegúrate de que estas variables estén definidas en tu archivo .env
         $secure = (bool) ($_ENV['COOKIE_SECURE'] ?? false);
         $sameSite = $_ENV['COOKIE_SAME_SITE'] ?? 'Lax';
         // Convertir la vida útil de la sesión de minutos a segundos
         $lifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 120) * 60;
         $domain = $_ENV['APP_DOMAIN'] ?? ''; // Opcional: define APP_DOMAIN en .env si es necesario

         // Configurar los parámetros de la cookie de sesión
         // Esto debe hacerse ANTES de session_start()
         session_set_cookie_params([
            'lifetime' => $lifetime,    // Duración de la cookie
            'path' => '/',              // Disponible en todo el dominio
            'domain' => $domain,        // Dominio del que es válida la cookie
            'secure' => $secure,        // Solo enviar la cookie vía HTTPS
            'httponly' => true,         // La cookie solo es accesible vía HTTP, no JS
            'samesite' => $sameSite,    // Protección CSRF: 'Lax', 'Strict', 'None'
         ]);

         // Configurar otras directivas INI relacionadas con la sesión
         ini_set('session.cookie_httponly', '1');
         ini_set('session.use_only_cookies', '1'); // Solo usar cookies para IDs de sesión
         ini_set('session.use_strict_mode', '1');  // Prevenir ataques de fijación de sesión
         ini_set('session.gc_maxlifetime', (string) $lifetime); // Vida útil de la recolección de basura

         // Iniciar la sesión
         session_start();
      }

      self::$sessionStarted = true;
   }

   /**
    * Obtiene un valor de la sesión.
    */
   public function get(string $key, mixed $default = null): mixed {
      return $_SESSION[$key] ?? $default;
   }

   /**
    * Establece un valor en la sesión.
    */
   public function set(string $key, mixed $value): void {
      $_SESSION[$key] = $value;
   }

   /**
    * Verifica si una clave existe en la sesión.
    */
   public function has(string $key): bool {
      return isset($_SESSION[$key]);
   }

   /**
    * Elimina una clave de la sesión.
    */
   public function forget(string $key): void {
      unset($_SESSION[$key]);
   }

   /**
    * Destruye la sesión actual.
    */
   public function destroy(): void {
      if (session_status() === PHP_SESSION_ACTIVE) {
         session_unset();    // Elimina todas las variables de sesión
         session_destroy();  // Destruye los datos de la sesión en el almacenamiento
         // También destruir la cookie de sesión en el navegador
         $params = session_get_cookie_params();
         setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
         );
      }
      self::$sessionStarted = false;
   }
}