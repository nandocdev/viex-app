<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        ConfigServiceProvider
 * @description Carga y registra todos los archivos de configuración en el contenedor.
 */
declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Core\Config;
use RuntimeException;

class ConfigServiceProvider implements ServiceProviderInterface {
   public function register(Container $container): void {
      // Get the base path from the Application instance that's already registered
      $app = $container->resolve(\Phast\System\Core\Application::class);
      $configPath = $app->basePath . '/config';

      if (!is_dir($configPath)) {
         throw new RuntimeException("Configuration directory not found: {$configPath}");
      }

      // Registra todo el array de configuración como un singleton en el contenedor.
      // Usamos 'bind' en lugar de 'singleton' porque un array no es una clase,
      // pero el efecto es el mismo: se registra un valor único.
      $container->bind('config', fn() => new Config($app));
   }
}