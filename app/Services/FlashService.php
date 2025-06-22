<?php
/**
 * @package     phast/app
 * @subpackage  Services
 * @file        FlashService
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 00:31:58
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);
namespace Phast\App\Services;
use Phast\System\Plugins\Session\SessionManager;

class FlashService {
   private static array $iconFlash = [/* ... tus iconos ... */];

   public function __construct(private SessionManager $session) {
   }

   public function add(string $type, string $message, ?string $title = null): void {
      $notifications = $this->session->getFlashed('notifications', []);
      $notifications[] = [
         'type' => $type,
         'message' => $message,
         'title' => $title,
         'icon' => self::$iconFlash[$type] ?? 'fa fa-info',
      ];
      $this->session->flash('notifications', $notifications);
   }
}