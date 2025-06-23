<?php
/**
 * @package     system/Auth
 * @subpackage  Guards
 * @file        TokenGuard
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:54:06
 * @version     1.0.0
 * @description
 */


declare(strict_types=1);

namespace Phast\System\Auth\Guards;

use Phast\System\Auth\Contracts\Guard;
use Phast\System\Auth\Authenticatable;
use Phast\System\Http\Request;
use Phast\App\Modules\Auth\Models\Entities\UserEntity; // Asumimos User como proveedor

class TokenGuard implements Guard {

   /** El usuario actualmente autenticado para esta petición. */
   protected ?Authenticatable $user = null;

   /** Indica si ya hemos intentado resolver al usuario. */
   protected bool $userResolved = false;

   /** La columna de la BD donde se almacena el token. */
   protected string $storageKey = 'api_token';

   /** El nombre del parámetro en la petición (header o input). */
   protected string $inputKey = 'api_token';

   public function __construct(
      protected Request $request,
      protected string $userModelClass = UserEntity::class
   ) {
   }

   public function user(): ?Authenticatable {
      if ($this->userResolved) {
         return $this->user;
      }

      $token = $this->getTokenForRequest();

      if (!empty($token)) {
         // Busca al usuario en la BD por el token
         $this->user = $this->userModelClass::where($this->storageKey, '=', $token)->first();
      }

      $this->userResolved = true;
      return $this->user;
   }

   /**
    * Obtiene el token de la petición actual.
    * Prioriza la cabecera 'Authorization: Bearer'.
    */
   public function getTokenForRequest(): ?string {
      // 1. Buscar en la cabecera 'Authorization'
      $token = $this->request->getHeader('Authorization');
      if (!empty($token) && preg_match('/^Bearer\s+(.*?)$/', $token, $matches)) {
         return $matches[1];
      }

      // 2. Si no, buscar en la query string o cuerpo de la petición
      return $this->request->input($this->inputKey);
   }

   /**
    * Valida credenciales. Para un TokenGuard, esto no tiene mucho sentido
    * ya que la autenticación es por token, no por contraseña.
    * Devolvemos false siempre.
    */
   public function validate(array $credentials = []): bool {
      return false; // No aplicable para autenticación por token
   }

   // --- MÉTODOS NO APLICABLES A UN GUARD STATELESS ---
   // Un TokenGuard es "stateless" (sin estado). No "inicia sesión".
   // La sesión es la existencia del token en cada petición.

   public function check(): bool {
      return !is_null($this->user());
   }

   public function guest(): bool {
      return !$this->check();
   }

   public function id() {
      return $this->user() ? $this->user()->getAuthIdentifier() : null;
   }

   /**
    * Attempt no es aplicable. Devolvemos false.
    * La generación de tokens debe manejarse por separado (ej. en un LoginController).
    */
   public function attempt(array $credentials = [], bool $remember = false): bool {
      return false;
   }

   /**
    * Login no es aplicable. No se guarda nada en sesión.
    */
   public function login(Authenticatable $user, bool $remember = false): void {
      // No hace nada. La autenticación es por token en cada petición.
   }

   /**
    * Logout no es aplicable. El cliente simplemente debe dejar de enviar el token.
    * Opcionalmente, se podría invalidar el token en la BD.
    */
   public function logout(): void {
      // En una implementación más avanzada, podrías hacer:
      // if ($this->user) {
      //     $this->user->api_token = null;
      //     $this->user->save();
      // }
      $this->user = null;
   }
}