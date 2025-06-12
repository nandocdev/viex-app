<?php
/**
 * @package     phast/system
 * @subpackage  Providers
 * @file        LogServiceProvider
 * @description Registra el servicio de logging (Monolog) en el contenedor.
 */
declare(strict_types=1);

namespace Phast\System\Providers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Phast\System\Core\Container;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Psr\Log\LoggerInterface;

class LogServiceProvider implements ServiceProviderInterface {
   public function register(Container $container): void {
      $container->singleton(LoggerInterface::class, function () {
         // Lee la configuración del entorno.
         $channel = $_ENV['LOG_CHANNEL'] ?? 'phast';
         $logLevel = $this->getLogLevel($_ENV['LOG_LEVEL'] ?? 'debug');
         $logPath = PHAST_BASE_PATH . '/storage/logs/phast.log';

         $logger = new Logger($channel);

         // Asegúrate de que el directorio de logs exista.
         $logDir = dirname($logPath);
         if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
         }

         // Añade un manejador que escribirá los logs en un archivo.
         $logger->pushHandler(new StreamHandler($logPath, $logLevel));

         return $logger;
      });
   }

   /**
    * Convierte el nivel de log de string a un entero de Monolog.
    */
   private function getLogLevel(string $level): int {
      return match (strtolower($level)) {
         'debug' => Logger::DEBUG,
         'info' => Logger::INFO,
         'notice' => Logger::NOTICE,
         'warning' => Logger::WARNING,
         'error' => Logger::ERROR,
         'critical' => Logger::CRITICAL,
         'alert' => Logger::ALERT,
         'emergency' => Logger::EMERGENCY,
         default => Logger::DEBUG,
      };
   }
}