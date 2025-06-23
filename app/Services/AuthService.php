<?php
/**
 * @package     viex.com/app
 * @subpackage  Services
 * @file        AuthService
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-22 16:24:50
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Services;
use Phast\App\Modules\Auth\Models\Entities\AuthEntity;
use Phast\System\Services\FlashService; // Asumiendo que existe FlashService para mensajes
use Phast\System\Database\Model; // Asumiendo que Model es la base para las entidades

class AuthService {
   protected const SESSION_USER_ID_KEY = 'user_id';
   protected ?AuthEntity $currentAuthUser = null;

   public function __construct() {
      $this->loadAuthFromSession();
   }

   public function loadAuthFromSession(): void {
      // Cargar el usuario autenticado desde la sesión
      $userId = $_SESSION[self::SESSION_USER_ID_KEY] ?? null;

      if ($userId) {
         // Asumimos que AuthEntity es un modelo que representa al usuario autenticado
         $this->currentAuthUser = AuthEntity::find($userId);
      } else {
         $this->currentAuthUser = null;
      }
   }

}