<?php
/**
 * @package     system/Phoenix
 * @subpackage  Entity
 * @file        EntityInterface
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:20:29
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Phoenix\Entity;

/**
 * Contrato para todas las clases de Entidad.
 *
 * Esta interfaz establece los métodos fundamentales que una clase debe implementar
 * para ser gestionada por el ORM Phoenix. Garantiza que el sistema pueda obtener
 * metadatos esenciales (nombre de la tabla, clave primaria) y gestionar el ciclo
 * de vida de una instancia (creación, obtención de atributos) de una manera
 * consistente y predecible.
 */
interface EntityInterface {
   /**
    * Crea una nueva instancia de la entidad a partir de un conjunto de datos crudos.
    *
    * Este método de fábrica es utilizado internamente por el ORM para construir
    * objetos después de una consulta a la base de datos.
    *
    * @param array<string, mixed> $attributes Los datos de la base de datos.
    * @param bool $isExisting Indica si la instancia representa un registro ya existente.
    * @return static La nueva instancia de la entidad, poblada con los datos.
    */
   public static function newInstanceFromData(array $attributes, bool $isExisting = false): static;

   /**
    * Obtiene el valor de la clave primaria para esta instancia de entidad.
    *
    * @return int|string|null El valor de la PK, o null si no está establecido.
    */
   public function getPrimaryKeyValue(): int|string|null;

   /**
    * Devuelve el estado actual de la entidad como un array asociativo.
    *
    * Este método se utiliza para la "deshidratación", preparando los datos
    * de la entidad para ser persistidos en la base de datos.
    *
    * @return array<string, mixed>
    */
   public function getAttributes(): array;

   /**
    * Indica si la instancia actual de la entidad existe en la base de datos.
    *
    * @return bool True si la entidad fue cargada desde la base de datos o ha sido guardada.
    */
   public function isExisting(): bool;
}