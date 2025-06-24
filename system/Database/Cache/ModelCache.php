<?php

/**
 * @package     phast/system
 * @subpackage  Database/Cache
 * @file        ModelCache
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Sistema de cache inteligente para modelos
 */

declare(strict_types=1);

namespace Phast\System\Database\Cache;

use Phast\System\Database\Model;

class ModelCache {
   protected static array $cache = [];
   protected static array $tags = [];
   protected static int $defaultTtl = 3600; // 1 hora

   /**
    * Obtiene un valor del cache
    */
   public static function get(string $key) {
      if (!isset(self::$cache[$key])) {
         return null;
      }

      $item = self::$cache[$key];

      if (time() > $item['expires_at']) {
         unset(self::$cache[$key]);
         return null;
      }

      return $item['value'];
   }

   /**
    * Establece un valor en el cache
    */
   public static function set(string $key, $value, int $ttl = 0): void {
      $ttl = $ttl ?? self::$defaultTtl;

      self::$cache[$key] = [
         'value' => $value,
         'expires_at' => time() + $ttl,
         'created_at' => time()
      ];
   }

   /**
    * Verifica si existe una clave en el cache
    */
   public static function has(string $key): bool {
      return self::get($key) !== null;
   }

   /**
    * Elimina una clave del cache
    */
   public static function forget(string $key): bool {
      if (isset(self::$cache[$key])) {
         unset(self::$cache[$key]);
         return true;
      }

      return false;
   }

   /**
    * Limpia todo el cache
    */
   public static function flush(): void {
      self::$cache = [];
      self::$tags = [];
   }

   /**
    * Obtiene o establece un valor en el cache
    */
   public static function remember(string $key, callable $callback, int $ttl = 0) {
      if (self::has($key)) {
         return self::get($key);
      }

      $value = $callback();
      self::set($key, $value, $ttl);

      return $value;
   }

   /**
    * Genera una clave de cache para un modelo
    */
   public static function generateKey(Model $model, string $method, array $parameters = []): string {
      $class = get_class($model);
      $table = $model->getTable();
      $params = serialize($parameters);

      return md5("{$class}:{$table}:{$method}:{$params}");
   }

   /**
    * Cachea el resultado de una consulta
    */
   public static function cacheQuery(Model $model, string $method, array $parameters, callable $callback, int $ttl = 0) {
      $key = self::generateKey($model, $method, $parameters);

      return self::remember($key, $callback, $ttl);
   }

   /**
    * Invalida el cache de un modelo específico
    */
   public static function invalidateModel(Model $model): void {
      $class = get_class($model);
      $table = $model->getTable();

      foreach (self::$cache as $key => $item) {
         if (strpos($key, md5($class)) !== false || strpos($key, md5($table)) !== false) {
            unset(self::$cache[$key]);
         }
      }
   }

   /**
    * Obtiene estadísticas del cache
    */
   public static function getStats(): array {
      $total = count(self::$cache);
      $expired = 0;
      $valid = 0;

      foreach (self::$cache as $item) {
         if (time() > $item['expires_at']) {
            $expired++;
         } else {
            $valid++;
         }
      }

      return [
         'total' => $total,
         'valid' => $valid,
         'expired' => $expired,
         'memory_usage' => memory_get_usage(true)
      ];
   }
}
