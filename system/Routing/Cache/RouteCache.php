<?php
/**
 * @package     Phast/System
 * @subpackage  Routing/Cache
 * @file        RouteCache.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-06-09
 * @version     1.0.0
 * @description Gestiona el almacenamiento y recuperación de rutas desde un archivo de caché.
 */
declare(strict_types=1);

namespace Phast\System\Routing\Cache;

class RouteCache {
   /**
    * La ruta completa al archivo de caché.
    * @var string
    */
   protected string $cachePath;

   /**
    * Constructor.
    *
    * @param string $cachePath La ruta donde se guardará el archivo de caché.
    */
   public function __construct(string $cachePath) {
      $this->cachePath = $cachePath;
   }

   /**
    * Comprueba si existe un archivo de caché válido.
    *
    * @return bool
    */
   public function exists(): bool {
      return file_exists($this->cachePath);
   }

   /**
    * Carga las rutas desde el archivo de caché.
    *
    * @return array Un array con 'routes' y 'namedRoutes'.
    */
   public function load(): array {
      // Usamos `require` para cargar el archivo PHP, que es más rápido que `unserialize`.
      return require $this->cachePath;
   }

   /**
    * Guarda las rutas en el archivo de caché.
    *
    * @param array $routes El array de rutas a cachear.
    * @param array $namedRoutes El array de rutas con nombre a cachear.
    */
   public function save(array $routes, array $namedRoutes): void {
      $cacheDir = dirname($this->cachePath);
      if (!is_dir($cacheDir)) {
         // Crea el directorio de caché si no existe.
         mkdir($cacheDir, 0755, true);
      }

      // Preparamos los datos para ser escritos en el archivo.
      $data = [
         'routes' => $routes,
         'namedRoutes' => $namedRoutes,
      ];

      // Convertimos el array a una representación de string PHP usando var_export.
      // Esto crea un archivo PHP que puede ser incluido, lo cual es mucho más rápido
      // que usar serialize/unserialize, ya que puede ser cacheado por OPcache.
      $content = '<?php return ' . var_export($data, true) . ';';

      // Escribimos el contenido en el archivo de caché de forma atómica.
      file_put_contents($this->cachePath, $content, LOCK_EX);
   }

   /**
    * Elimina el archivo de caché de rutas.
    *
    * @return bool True si el archivo fue eliminado, false si no existía.
    */
   public function clear(): bool {
      if ($this->exists()) {
         return unlink($this->cachePath);
      }
      return false;
   }
}