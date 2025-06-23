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
   protected const TOKEN_KEY = '_token';
   private const FLASH_KEY = '_flash';
   private const FLASH_NEW = '_flash_new';
   private const FLASH_OLD = '_flash_old';
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
         $secure = (bool) config('session.secure', false);
         $sameSite = config('session.same_site', 'Lax');
         // Convertir la vida útil de la sesión de minutos a segundos
         $lifetime = (int) config('session.lifetime', 120) * 60;
         $domain = config('session.domain', config('app.domain', null));

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

      $this->ageFlashData(); // Llama a este método después de session_start()
      self::$sessionStarted = true;
   }

   /**
    * regenera la sesión actual, destruye la sesión anterior
    */
   public function regenerate(): void {
      if (session_status() === PHP_SESSION_ACTIVE) {
         // Regenera la sesión actual
         session_regenerate_id(true); // true para eliminar la sesión anterior
         $this->ageFlashData(); // Actualiza los datos "flashed" después de regenerar
      } else {
         $this->start(); // Asegúrate de que la sesión esté iniciada
      }
   }

   /**
    * Guarda un dato en la sesión solo para la siguiente petición.
    */
   public function flash(string $key, mixed $value): void {
      $_SESSION[self::FLASH_NEW][$key] = $value;
   }

   /**
    * Obtiene un dato "flashed" de la petición anterior.
    */
   public function getFlashed(string $key, mixed $default = null): mixed {
      return $_SESSION[self::FLASH_OLD][$key] ?? $default;
   }

   protected function ageFlashData(): void {
      // Borra los datos que ya tienen una petición de antigüedad
      unset($_SESSION[self::FLASH_OLD]);

      // Mueve los nuevos datos al bucket de "viejos" para que se puedan leer
      if (isset($_SESSION[self::FLASH_NEW])) {
         $_SESSION[self::FLASH_OLD] = $_SESSION[self::FLASH_NEW];
         unset($_SESSION[self::FLASH_NEW]);
      }
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

   /**
    * Obtiene el token CSRF actual de la sesión.
    */
   public function getToken(): ?string {
      return $this->get(self::TOKEN_KEY);
   }

   /**
    * Genera un nuevo token CSRF, lo guarda en la sesión y lo devuelve.
    * Es crucial para asegurar que cada sesión tenga su propio token.
    */
   public function regenerateToken(): string {
      // bin2hex(random_bytes(32)) genera un token criptográficamente seguro.
      $token = bin2hex(random_bytes(32));
      $this->set(self::TOKEN_KEY, $token);
      return $token;
   }

   /**
    * Compara un token dado con el que está en la sesión de forma segura
    * para prevenir ataques de temporización (timing attacks).
    */
   public function validateToken(?string $token): bool {
      $sessionToken = $this->getToken();

      if (!$sessionToken || !$token) {
         return false;
      }

      // ¡Usa hash_equals para una comparación segura!
      return hash_equals($sessionToken, $token);
   }

}