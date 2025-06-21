<?php
/**
 * @package     Phoenix/Relations
 * @subpackage  
 * @file        RelationManager.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 14:30:00
 * @version     1.0.0
 * @description Gestiona la definición y carga de relaciones para una instancia de entidad.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Relations;

use Phast\System\Phoenix\Entity\EntityInterface;
use Phast\System\Phoenix\Query\Director;
use Phast\System\Phoenix\Relations\Strategies\RelationStrategyInterface;
use RuntimeException;

/**
 * Gestiona y carga las relaciones para una instancia específica de una entidad.
 *
 * Cada instancia de entidad tendrá su propio RelationManager, que se encarga de:
 * 1. Registrar las estrategias de relación disponibles para esa entidad.
 * 2. Cargar bajo demanda (lazy load) las relaciones cuando se solicitan.
 * 3. Almacenar en caché los resultados de las relaciones ya cargadas para evitar
 *    consultas redundantes a la base de datos.
 */
final class RelationManager {
   private EntityInterface $parent;
   private Director $director;

   /**
    * Almacena las estrategias de relación definidas, indexadas por su nombre.
    * @var array<string, RelationStrategyInterface>
    */
   private array $relations = [];

   /**
    * Caché para los resultados de las relaciones ya cargadas.
    * @var array<string, mixed>
    */
   private array $loaded = [];

   public function __construct(EntityInterface $parent, Director $director) {
      $this->parent = $parent;
      $this->director = $director;
   }

   /**
    * Define una nueva relación y la estrategia para resolverla.
    *
    * @param string $name El nombre con el que se accederá a la relación (ej: 'posts').
    * @param RelationStrategyInterface $strategy La instancia de la estrategia que sabe cómo cargar la relación.
    * @return void
    */
   public function define(string $name, RelationStrategyInterface $strategy): void {
      $this->relations[$name] = $strategy;
   }

   /**
    * Obtiene el resultado de una relación.
    *
    * Carga la relación si no ha sido cargada previamente, o devuelve el resultado
    * cacheado si ya lo fue.
    *
    * @param string $name El nombre de la relación a cargar.
    * @return array<int, EntityInterface>|EntityInterface|null El resultado de la relación.
    * @throws RuntimeException Si se solicita una relación que no ha sido definida.
    */
   public function get(string $name) {
      // 1. Devolver desde el caché si ya se cargó.
      if (array_key_exists($name, $this->loaded)) {
         return $this->loaded[$name];
      }

      // 2. Verificar que la relación esté definida.
      if (!isset($this->relations[$name])) {
         throw new RuntimeException("La relación '{$name}' no está definida en la entidad '" . get_class($this->parent) . "'.");
      }

      // 3. Obtener y ejecutar la estrategia de carga.
      $strategy = $this->relations[$name];
      $result = $strategy->load($this->parent, $this->director);

      // 4. Almacenar el resultado en el caché y devolverlo.
      return $this->loaded[$name] = $result;
   }
}