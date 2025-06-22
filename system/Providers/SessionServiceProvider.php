<?php
declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Plugins\Session\SessionManager; // ¡Usa el namespace correcto!

class SessionServiceProvider implements ServiceProviderInterface {
   public function register(Container $container): void {
      $container->singleton(SessionManager::class, function () {
         $session = new SessionManager();
         // El método start() se asegura de que la sesión se inicie de forma segura
         // tan pronto como se necesite el servicio por primera vez.
         $session->start();
         return $session;
      });
   }
}