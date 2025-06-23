<?php
/**
 * @package     system/Auth
 * @subpackage  Guards
 * @file        SessionGuard
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:51:10
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Auth\Guards;


use Phast\System\Auth\Contracts\Guard;
use Phast\System\Auth\Authenticatable;
use Phast\System\Plugins\Session\SessionManager;
use Phast\App\Modules\Auth\Models\Entities\UserEntity; // Asumimos que nuestro proveedor de usuarios es el modelo User

class SessionGuard implements Guard {

   /** El usuario actualmente autenticado. Se cachea para evitar consultas repetidas. */
   protected ?Authenticatable $user = null;

   /** Indica si ya hemos intentado cargar el usuario desde la sesión. */
   protected bool $userResolved = false;

   public function __construct(
      protected SessionManager $session,
      // En un futuro, podrías inyectar un "UserProvider" en lugar del modelo directamente
      // para mayor flexibilidad. Por ahora, esto es simple y efectivo.
      protected string $userModelClass = UserEntity::class
   ) {
   }

   public function check(): bool {
      return !is_null($this->user());
   }

   public function guest(): bool {
      return !$this->check();
   }

   public function user(): ?Authenticatable {
      // Si ya hemos resuelto el usuario en esta petición, lo devolvemos directamente.
      if ($this->userResolved) {
         return $this->user;
      }

      $id = $this->session->get($this->getSessionKey());

      if (!is_null($id)) {
         // Busca al usuario en la base de datos
         $this->user = $this->userModelClass::find($id);
      }

      $this->userResolved = true;
      return $this->user;
   }

   public function id() {
      // Devuelve el ID de la sesión directamente si está disponible,
      // o del objeto de usuario si ya ha sido cargado.
      return $this->session->get($this->getSessionKey()) ?? ($this->user() ? $this->user()->getAuthIdentifier() : null);
   }

   public function validate(array $credentials = []): bool {
      if (empty($credentials['email']) || empty($credentials['password'])) {
         return false;
      }

      $user = $this->userModelClass::where('email', '=', $credentials['email'])->first();

      if ($user && password_verify($credentials['password'], $user->getAuthPassword())) {
         return true;
      }

      return false;
   }

   public function attempt(array $credentials = [], bool $remember = false): bool {
      $user = $this->userModelClass::where('email', '=', $credentials['email'])->first();
      if ($user && !($user instanceof Authenticatable) && is_object($user)) {
         $user = new $this->userModelClass((array) $user);
      }

      if ($user && password_verify($credentials['password'], $user->getAuthPassword())) {
         $this->login($user, $remember);
         return true;
      }

      return false;
   }

   public function login(Authenticatable $user, bool $remember = false): void {
      // Regenera el ID de sesión para prevenir ataques de fijación de sesión.
      $this->session->regenerate();

      $this->session->set($this->getSessionKey(), $user->getAuthIdentifier());

      // Actualizamos la instancia de usuario cacheada en el guard.
      $this->user = $user;
      $this->userResolved = true;
   }

   public function logout(): void {
      // Eliminamos los datos del usuario de la sesión y la caché del guard.
      $this->session->forget($this->getSessionKey());
      $this->user = null;
      $this->userResolved = true; // Marcamos como resuelto (a null)

      // Opcional: Destruir la sesión completamente
      // $this->session->destroy();
   }

   /**
    * Devuelve la clave que se usará para almacenar el ID en la sesión.
    */
   protected function getSessionKey(): string {
      return 'login_web_' . sha1(static::class);
   }
}