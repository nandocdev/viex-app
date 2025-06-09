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
use Phast\System\Routing\Facades\Router as RouterFacade;
use Phast\System\View\View;
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
   }

   protected function registerServices(): void {
      $this->container->singleton(Request::class, fn() => new Request());
      $this->container->singleton(Response::class, fn() => new Response());
      $this->container->singleton(RouterManager::class, function ($container) {
         return new RouterManager(
            $container,
            $container->resolve(Application::class)
         );
      });
      $this->container->singleton(View::class, fn(Container $c) => new View($c->resolve(Application::class)->basePath));

      // $this->container->bind(RouterFacade::class, fn(Container $container) => new RouterFacade());
   }

   protected function loadRoutes(): void {
      require_once $this->basePath . '/routes/web.php';
   }

   public function run(): void {
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
