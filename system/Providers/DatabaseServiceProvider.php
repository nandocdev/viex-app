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
use Phast\System\Database\Database;
use Phast\System\Database\DatabaseManager;

class DatabaseServiceProvider implements ServiceProviderInterface {
   /**
    * Registra los servicios de la base de datos en el contenedor.
    *
    * @param Container $container
    * @return void
    */
   public function register(Container $container): void {
      // 1. Registrar el DatabaseManager como un singleton.
      // Este gestor es responsable de manejar las configuraciones de conexión
      // y de crear instancias de conexión. Solo necesitamos uno por aplicación.
      // Depende de `Application` para encontrar el archivo de configuración.
      $container->singleton(DatabaseManager::class, function (Container $c) {
         return new DatabaseManager(
            $c->resolve(Application::class)
         );
      });

      // 2. Registrar la clase Database principal como un singleton.
      // Esta clase actúa como la fachada principal para ejecutar consultas (SELECT, INSERT, etc.).
      // Al ser un singleton, se asegura de que reutiliza la misma conexión PDO
      // obtenida del DatabaseManager a lo largo de todo el ciclo de la petición.
      // Depende del DatabaseManager para obtener la conexión.
      $container->singleton(Database::class, function (Container $c) {
         return new Database(
            $c->resolve(DatabaseManager::class)
         );
      });
   }
}