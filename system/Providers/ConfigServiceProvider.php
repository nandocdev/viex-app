<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        ConfigServiceProvider
 * @description Carga y registra todos los archivos de configuración en el contenedor.
 */
declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Application;
use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use RuntimeException;

class ConfigServiceProvider implements ServiceProviderInterface {
   public function register(Container $container): void {
      $config = [];

      $app = $container->resolve(Application::class);
      $configPath = $app->basePath . '/config';

      if (!is_dir($configPath)) {
         throw new RuntimeException("Configuration directory not found: {$configPath}");
      }

      // Itera sobre todos los archivos .php en el directorio de configuración
      foreach (glob($configPath . '/*.php') as $file) {
         // Usa el nombre del archivo (sin .php) como clave en el array de configuración
         $key = pathinfo($file, PATHINFO_FILENAME);
         $config[$key] = require $file;
      }

      // Registra todo el array de configuración como un singleton en el contenedor.
      // Usamos 'bind' en lugar de 'singleton' porque un array no es una clase,
      // pero el efecto es el mismo: se registra un valor único.
      $container->bind('config', fn() => $config);
   }
}