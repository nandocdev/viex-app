<?php

/**
 * @package     phast/system
 * @subpackage  Core
 * @file        Application
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 22:59:01
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Core;

use Dotenv\Dotenv;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Routing\RouterManager;

// --- NUEVAS IMPORTACIONES NECESARIAS ---
use Phast\System\Rendering\Contracts\ViewEngine;
use Phast\System\Rendering\Engines\PhpEngine; // ¡IMPORTANTE: Corregido de PhpEnginer a PhpEngine!
use Phast\System\Rendering\Core\DataHandler;
use Phast\System\Rendering\Core\TemplateLoader;
use Phast\System\Rendering\Render; // La clase principal Render
use Phast\System\Plugins\Session\SessionManager; // Si necesitas manejar sesiones, asegúrate de que SessionManager esté importado
// ---------------------------------------
use Phast\System\Core\Contracts\ServiceProviderInterface; // ¡Añadir esta!

use Throwable;

class Application {
   public Container $container;

   public function __construct(public string $basePath) {
      $this->container = Container::getInstance();
      $this->container->singleton(Application::class, fn() => $this);

      $this->loadEnvironment();
      $this->registerServices();
      $this->loadRoutes();
   }

   protected function loadEnvironment(): void {
      $dotenv = Dotenv::createImmutable($this->basePath);
      $dotenv->load();

      // Falla rápido si faltan variables críticas
      $dotenv->required([
         'APP_ENV',
         'DB_HOST',
         'DB_DATABASE',
         'DB_USERNAME'
      ])->notEmpty();
   }

   // ¡MÉTODO registerServices REFACTORIZADO!
   protected function registerServices(): void {
      // Registrar los servicios básicos que siempre se necesitan
      $this->container->singleton(Request::class, fn() => new Request()); // Asumiendo la refactorización de Request
      $this->container->singleton(Response::class, fn() => new Response());

      // Cargar y registrar todos los proveedores de servicios
      $providers = require $this->basePath . '/config/providers.php';

      foreach ($providers as $providerClass) {
         if (!class_exists($providerClass)) {
            // Falla rápido si un provider no existe
            throw new \Exception("Service Provider class not found: {$providerClass}");
         }

         $providerInstance = new $providerClass();

         if (!$providerInstance instanceof ServiceProviderInterface) {
            throw new \Exception("Class {$providerClass} must implement ServiceProviderInterface.");
         }

         // Llamar al método register de cada provider
         $providerInstance->register($this->container);
      }
   }



   protected function loadRoutes(): void {
      require_once $this->basePath . '/routes/web.php';
   }

   public function run(): void {
      // --- INICIAR LA SESIÓN AL PRINCIPIO DEL CICLO DE VIDA DE LA APLICACIÓN ---
      $sessionManager = $this->container->resolve(SessionManager::class);
      $sessionManager->start();
      // --------------------------------------------------------------------------

      try {
         // El RouterManager se encarga de todo.
         $routerManager = $this->container->resolve(RouterManager::class);
         $request = $this->container->resolve(Request::class);

         $response = $routerManager->resolve($request);

         $this->sendResponse($response);
      } catch (Throwable $e) {
         $this->handleException($e);
      }
   }



   protected function sendResponse(Response $response): void {
      http_response_code($response->getStatusCode());
      foreach ($response->getHeaders() as $name => $value) {
         header("{$name}: {$value}");
      }
      echo $response->getBody();
   }

   protected function handleException(Throwable $e): void {
      // Ahora podemos usar el getStatusCode() de nuestras excepciones personalizadas.
      $statusCode = method_exists($e, 'getCode') ? $e->getCode() : 500;

      $message = $_ENV['APP_DEBUG'] === 'true'
         ? $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
         : 'Server Error';

      $response = new Response($message, $statusCode);
      $this->sendResponse($response);
   }
}
