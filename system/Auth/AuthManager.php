<?php
/**
 * @package     phast/system
 * @subpackage  Auth
 * @file        AuthManager
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:57:12
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);

namespace Phast\System\Auth;

use Phast\System\Core\Container;
use Phast\System\Auth\Contracts\Guard;
use Phast\System\Auth\Guards\SessionGuard;
use Phast\System\Auth\Guards\TokenGuard;
use Phast\System\Plugins\Session\SessionManager;
use Phast\System\Http\Request;
use InvalidArgumentException;

class AuthManager {
   /** La instancia del contenedor de la aplicación. */
   protected Container $container;

   /** La configuración de autenticación. */
   protected array $config;

   /**
    * El array de instancias de guards ya resueltas.
    * @var Guard[]
    */
   protected array $guards = [];

   public function __construct(Container $container) {
      $this->container = $container;
      // Asume que la configuración ya está cargada en el contenedor.
      $this->config = $container->resolve('config')['auth'] ?? [];
   }

   /**
    * Obtiene una instancia de un guard específico.
    * Si no se especifica un nombre, devuelve el guard por defecto.
    */
   public function guard(?string $name = null): Guard {
      $name = $name ?: $this->getDefaultDriver();

      // Si ya hemos creado esta instancia de guard, la devolvemos.
      if (isset($this->guards[$name])) {
         return $this->guards[$name];
      }

      // Si no, la creamos, la guardamos y la devolvemos.
      return $this->guards[$name] = $this->resolve($name);
   }

   /**
    * Resuelve y crea la instancia del guard solicitado.
    * Este método actúa como la "fábrica" de guards.
    */
   protected function resolve(string $name): Guard {
      $config = $this->getConfig($name);

      if (is_null($config)) {
         throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
      }

      // Usamos un switch para determinar qué tipo de guard crear
      // basándonos en el 'driver' de la configuración.
      switch ($config['driver']) {
         case 'session':
            return $this->createSessionDriver($config);
         case 'token':
            return $this->createTokenDriver($config);
         default:
            throw new InvalidArgumentException("Auth driver [{$config['driver']}] for guard [{$name}] is not supported.");
      }
   }

   /**
    * Crea una instancia del SessionGuard.
    */
   protected function createSessionDriver(array $config): SessionGuard {
      $provider = $this->getUserProvider($config['provider']);

      // Resolvemos SessionManager desde el contenedor
      $session = $this->container->resolve(SessionManager::class);

      return new SessionGuard($session, $provider['model']);
   }

   /**
    * Crea una instancia del TokenGuard.
    */
   protected function createTokenDriver(array $config): TokenGuard {
      $provider = $this->getUserProvider($config['provider']);

      // Resolvemos Request desde el contenedor
      $request = $this->container->resolve(Request::class);

      return new TokenGuard($request, $provider['model']);
   }

   /**
    * Obtiene la configuración para un guard específico.
    */
   protected function getConfig(string $name): ?array {
      return $this->config['guards'][$name] ?? null;
   }

   /**
    * Obtiene la configuración para un proveedor de usuarios.
    */
   protected function getUserProvider(string $name): ?array {
      return $this->config['providers'][$name] ?? null;
   }

   /**
    * Obtiene el nombre del guard por defecto.
    */
   public function getDefaultDriver(): string {
      return $this->config['defaults']['guard'];
   }

   /**
    * Establece el guard por defecto dinámicamente.
    */
   public function setDefaultDriver(string $name): void {
      $this->config['defaults']['guard'] = $name;
   }

   // --- MÉTODOS MÁGICOS PARA DELEGAR LLAMADAS ---

   /**
    * Delega dinámicamente las llamadas al guard por defecto.
    * Esto nos permite hacer Auth::user(), Auth::check(), etc.
    */
   public function __call(string $method, array $parameters) {
      return $this->guard()->$method(...$parameters);
   }
}