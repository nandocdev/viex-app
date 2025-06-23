<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        DatabaseServiceProvider
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-11
 * @version     1.0.0
 * @description Registra el gestor de la base de datos y la fachada de acceso a datos.
 */

declare(strict_types=1);

namespace Phast\System\Providers;

use Phast\System\Core\Application;
use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Database\Connection;

class DatabaseServiceProvider implements ServiceProviderInterface {
   /**
    * Registra los servicios de la base de datos en el contenedor.
    *
    * @param Container $container
    * @return void
    */
   public function register(Container $container): void {
      // Register the database configuration
      $container->singleton('database', function (Container $c) {
         return require $c->resolve(Application::class)->basePath . '/config/database.php';
      });

      // Register the Connection class as a singleton
      // This class manages the PDO connection and is used by the DB facade
      $container->singleton(Connection::class, function (Container $c) {
         return new Connection();
      });
   }
}