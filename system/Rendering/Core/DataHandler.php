<?php

/**
 * @package     system/Rendering
 * @subpackage  Core
 * @file        DataHandler
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 21:50:06
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Core;

class DataHandler {
   private array $data = [];

   /**
    * Establece los datos para la vista.
    * @param array $data Los datos a establecer.
    * @param bool $override Si es true, sobrescribe los datos existentes; de lo contrario, los fusiona.
    */
   public function setData(array $data, bool $override = false): void {
      $this->data = $override ? $data : array_merge($this->data, $data);
   }

   /**
    * Obtiene un valor de los datos de la vista.
    * @param string $key La clave del dato a obtener.
    * @param mixed $default El valor por defecto si la clave no existe.
    * @return mixed El valor del dato o el valor por defecto.
    */
   public function getData(string $key, mixed $default = null): mixed {
      return $this->data[$key] ?? $default;
   }

   /**
    * Elimina un dato de la vista por su clave.
    * @param string $key La clave del dato a eliminar.
    */
   public function removeData(string $key): void {
      unset($this->data[$key]);
   }

   /**
    * Prepara los datos para ser usados en la vista (ej. para extract()).
    * En este caso, simplemente devuelve el array completo.
    * @return array Los datos preparados.
    */
   public function prepareDataForView(): array {
      return $this->data;
   }
}
