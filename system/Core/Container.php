<?php

/**
 * @package     phast/system
 * @subpackage  Core
 * @file        Container
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 22:59:38
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Core;

use Closure;
use ReflectionClass;
use ReflectionParameter;
use Exception;

class Container {
   protected array $bindings = [];
   protected static ?self $instance = null;

   public static function getInstance(): self {
      if (is_null(self::$instance)) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function bind(string $key, Closure $resolver): void {
      $this->bindings[$key] = $resolver;
   }

   public function singleton(string $key, Closure $resolver): void {
      $this->bindings[$key] = function () use ($resolver) {
         static $resolvedInstance = null;
         if (is_null($resolvedInstance)) {
            $resolvedInstance = $resolver($this);
         }
         return $resolvedInstance;
      };
   }

   public function resolve(string $key) {

      if (isset($this->bindings[$key])) {
         return call_user_func($this->bindings[$key]);
      }

      $reflector = new ReflectionClass($key);
      if (!$reflector->isInstantiable()) {
         throw new Exception("Class {$key} is not instantiable.");
      }

      $constructor = $reflector->getConstructor();
      if (is_null($constructor)) {
         return new $key();
      }

      $dependencies = array_map(
         fn(ReflectionParameter $param) => $this->resolve($param->getType()->getName()),
         $constructor->getParameters()
      );

      return $reflector->newInstanceArgs($dependencies);
   }
}
