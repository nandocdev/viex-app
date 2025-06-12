<?php
/**
 * @package     phast/system
 * @subpackage  Database/ORM
 * @file        ModelNotFoundException
 * @description Excepción para cuando findOrFail() falla
 */
declare(strict_types=1);

namespace Phast\System\Database\ORM\Exceptions;

class ModelNotFoundException extends \Exception {
   /**
    * Crea una nueva instancia de la excepción.
    *
    * @param string $model El nombre del modelo que no se encontró.
    * @param int $code Código de error opcional.
    */
   public function __construct(string $model, int $code = 0) {
      parent::__construct("El modelo '{$model}' no fue encontrado.", $code);
   }
}