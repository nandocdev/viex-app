<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        AuthServiceProvider
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:58:04
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);


namespace Phast\System\Providers;

use Phast\System\Auth\AuthManager;
use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface {
   public function register(Container $container): void {
      // Registramos el AuthManager como un singleton.
      // Solo queremos una instancia de este gestor por cada petición.
      $container->singleton(AuthManager::class, function ($c) {
         return new AuthManager($c);
      });
   }
}