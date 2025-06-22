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

   public function resolve(string $key, array $resolving = []) {

      if (isset($this->bindings[$key])) {
         return call_user_func($this->bindings[$key]);
      }

      if (in_array($key, $resolving)) {
         throw new Exception("Circular dependency detected while resolving {$key}.");
      }
      $resolving[] = $key;

      $reflector = new ReflectionClass($key);
      if (!$reflector->isInstantiable()) {
         throw new Exception("Class {$key} is not instantiable.");
      }

      $constructor = $reflector->getConstructor();
      if (is_null($constructor)) {
         return new $key();
      }

      $dependencies = array_map(
         function (ReflectionParameter $param) use ($key, $resolving) {
            $type = $param->getType();
            if (!$type || $type->isBuiltin() || !($type instanceof \ReflectionNamedType)) {
               throw new Exception("Cannot resolve primitive dependency \${$param->getName()} for class {$key}.");
            }
            return $this->resolve($type->getName(), $resolving);
         },
         $constructor->getParameters()
      );

      return $reflector->newInstanceArgs($dependencies);
   }
}
