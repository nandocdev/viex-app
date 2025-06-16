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
use Phast\System\Http\Exceptions\TokenMismatchException; // Asegúrate de importar la excepción de token CSRF
use Psr\Log\LoggerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as Whoops;


use Phast\System\Plugins\Session\SessionManager;
use Phast\System\Core\Contracts\ServiceProviderInterface;
use Phast\System\Plugins\Validation\ValidationException;

use Throwable;

class Application {
   public Container $container;

   public function __construct(public string $basePath) {
      $this->container = Container::getInstance();
      $this->container->singleton(Application::class, fn() => $this);

      $this->loadEnvironment();
      $this->registerServices();
      $this->loadRoutes();
      $this->loadAppConfig();
      $this->loadDatabaseConfig();

   }

   protected function loadEnvironment(): void {
      $dotenv = Dotenv::createImmutable($this->basePath);
      $dotenv->load();

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


   protected function loadAppConfig(): void {
      $appConfig = require $this->basePath . "/config/app.php";
      $this->container->singleton('config', fn() => $appConfig);
   }

   protected function loadDatabaseConfig(): void {
      $dbConfig = require $this->basePath . "/config/database.php";
      $this->container->singleton('database', fn() => $dbConfig);
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

   // protected function handleException(Throwable $e): void {
   //    // Ahora podemos usar el getStatusCode() de nuestras excepciones personalizadas.
   //    $statusCode = method_exists($e, 'getCode') ? $e->getCode() : 500;

   //    $message = $_ENV['APP_DEBUG'] === 'true'
   //       ? $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
   //       : 'Server Error';

   //    $response = new Response($message, $statusCode);
   //    $this->sendResponse($response);
   // }

   protected function handleException(Throwable $e): void {
      // --- NUEVO BLOQUE PARA MANEJAR ERRORES DE VALIDACIÓN ---
      if ($e instanceof ValidationException) {
         // Para esto, necesitarás un sistema de sesión que soporte "flashing"
         // (datos que solo duran una petición). Por ahora, asumiremos que
         // $_SESSION puede usarse directamente.

         // ¡Advertencia! Esto requiere que la sesión ya esté iniciada.
         // Lo ideal es tener un SessionManager que se encargue de esto.
         if (session_status() === PHP_SESSION_NONE) {
            session_start();
         }

         $_SESSION['_flash'] = [
            'errors' => $e->getErrors(),
            'old' => $e->getOldInput(),
         ];

         $previousUrl = $_SERVER['HTTP_REFERER'] ?? '/';
         $response = (new Response())->redirect($previousUrl);
         $this->sendResponse($response);
         return; // Detiene la ejecución aquí
      }
      // --- FIN DEL NUEVO BLOQUE ---

      // --- NUEVO BLOQUE PARA MANEJAR CSRF ---
      if ($e instanceof TokenMismatchException) {
         // Simplemente mostramos un error 419.
         // Podrías crear una vista bonita para esto.
         $response = new Response('Page Expired', 419);
         $this->sendResponse($response);
         return;
      }
      // --- FIN DEL NUEVO BLOQUE ---

      // Código existente para manejar otras excepciones
      $statusCode = method_exists($e, 'getCode') && $e->getCode() >= 400 ? $e->getCode() : 500;

      // --- MANEJO DE ERRORES GENERALES ---
      $isDevelopment = ($_ENV['APP_ENV'] ?? 'production') === 'local' || ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

      if ($isDevelopment) {
         // ENTORNO DE DESARROLLO: Usa Whoops para una página de error detallada.
         $whoops = new Whoops();
         $whoops->pushHandler(new PrettyPageHandler());
         $whoops->handleException($e);
      } else {
         // ENTORNO DE PRODUCCIÓN: Loguea el error y muestra una página genérica.
         try {
            // 1. Loguear el error
            $logger = $this->container->resolve(LoggerInterface::class);
            $logger->error(
               $e->getMessage(),
               ['exception' => $e] // Monolog sabe cómo formatear esto.
            );

            // 2. Mostrar una vista de error genérica
            $statusCode = method_exists($e, 'getCode') && $e->getCode() >= 400 ? $e->getCode() : 500;
            $response = (new Response())
               ->status($statusCode)
               ->view('errors/500', [], 'default'); // Asumiendo un layout simple

            $this->sendResponse($response);
         } catch (Throwable $fatalError) {
            // Si incluso el logging o la vista fallan, muestra un error simple.
            http_response_code(500);
            echo 'A fatal error occurred. Please check the server logs.';
            // Loguea el error fatal directamente si es posible.
            error_log('Fatal error in exception handler: ' . $fatalError->getMessage());
         }
      }

      $message = ($_ENV['APP_ENV'] ?? 'production') === 'true'
         ? $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine()
         : 'Server Error';

      $response = new Response($message, $statusCode);
      $this->sendResponse($response);
   }
}
