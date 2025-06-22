<?php
/**
 * @package     Phoenix/Entity
 * @subpackage  Hydrator
 * @file        HydratorInterface.php
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2024-05-16 10:30:00
 * @version     1.0.0
 * @description Define el contrato para la hidratación y deshidratación de entidades.
 */
declare(strict_types=1);

namespace Phast\System\Phoenix\Entity\Hydrator;

use Phast\System\Phoenix\Entity\EntityInterface;
use Phast\System\Phoenix\Core\Exceptions\HydrationException;

/**
 * Contrato para los servicios de Hidratación.
 *
 * Un "hidratador" es responsable de dos procesos críticos y opuestos:
 * 1. Hidratación: Poblar una instancia de Entidad con datos crudos (generalmente de la DB).
 * 2. Deshidratación: Extraer los datos de una instancia de Entidad a un array simple,
 *    listo para ser persistido en la base de datos.
 *
 * Esta interfaz asegura que el ORM pueda cambiar la estrategia de hidratación
 * sin afectar a los componentes que la utilizan.
 */
interface HydratorInterface {
   /**
    * Rellena un objeto de entidad con datos de un array.
    *
    * @template T of EntityInterface
    * @param array<string, mixed> $data El array asociativo de datos (ej: una fila de la DB).
    * @param class-string<T> $entityClass El nombre de la clase de la entidad a instanciar.
    * @return T Una instancia de la entidad solicitada, poblada con los datos.
    * @throws HydrationException Si la clase no puede ser instanciada o hay un error de mapeo.
    */
   public function hydrate(array $data, string $entityClass): EntityInterface;

   /**
    * Extrae los valores de una entidad a un array asociativo.
    *
    * @param EntityInterface $entity La instancia de la entidad de la que se extraerán los datos.
    * @return array<string, mixed> Un array donde las claves son los nombres de las columnas
    *                               y los valores son los valores de las propiedades.
    * @throws HydrationException Si ocurre un error al leer las propiedades de la entidad.
    */
   public function dehydrate(EntityInterface $entity): array;
}