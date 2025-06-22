<?php
/**
 * @package     Phoenix/Relations
 * @subpackage  Strategies
 * @file        RelationStrategyInterface.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 14:00:00
 * @version     1.0.0
 * @description Define el contrato para todas las estrategias de carga de relaciones.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Relations\Strategies;

use Phast\System\Phoenix\Entity\EntityInterface;
use Phast\System\Phoenix\Query\Director;

/**
 * Contrato para las estrategias de carga de relaciones.
 *
 * El Patrón Strategy se utiliza aquí para encapsular la lógica específica de cada
 * tipo de relación (HasMany, BelongsTo, etc.) en su propia clase. Cada estrategia
 * sabe cómo construir y ejecutar la consulta necesaria para cargar los modelos
 * relacionados para una entidad "padre" dada.
 *
 * Esto permite añadir nuevos tipos de relaciones sin modificar el núcleo del ORM,
 * siguiendo el Principio Abierto/Cerrado.
 */
interface RelationStrategyInterface {
   /**
    * Carga y devuelve los modelos relacionados.
    *
    * @param EntityInterface $parent El modelo "padre" desde el que se origina la relación.
    *                                La estrategia usará su clave primaria o foránea.
    * @param Director $director El director de consultas, que se utilizará para
    *                           construir y ejecutar la nueva consulta para los modelos relacionados.
    * @return array<int, EntityInterface>|EntityInterface|null El resultado de la relación:
    *                                                          - Un array de entidades para relaciones "to-many".
    *                                                          - Una única entidad para relaciones "to-one".
    *                                                          - Null si una relación "to-one" no encuentra coincidencias.
    */
   public function load(EntityInterface $parent, Director $director): array|EntityInterface|null;
}