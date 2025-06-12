<?php
/**
 * @package     phast/system
 * @subpackage  Validation
 * @file        ValidationException
 * @description Excepción lanzada cuando la validación de datos falla.
 */

declare(strict_types=1);

namespace Phast\System\Plugins\Validation;

class ValidationException extends \Exception {
   /**
    * @var array Los mensajes de error de validación.
    */
   protected array $errors;

   /**
    * @var array Los datos de entrada originales que fallaron la validación.
    */
   protected array $oldInput;

   /**
    * @param array $errors Los errores de validación.
    * @param array $oldInput Los datos de entrada originales.
    */
   public function __construct(array $errors, array $oldInput) {
      $this->errors = $errors;
      $this->oldInput = $oldInput;
      // El código 422 "Unprocessable Entity" es el estándar para errores de validación.
      parent::__construct('The given data was invalid.', 422);
   }
   /**
    * Obtiene los mensajes de error.
    */
   public function getErrors(): array {
      return $this->errors;
   }

   /**
    * Obtiene los datos de entrada originales.
    */
   public function getOldInput(): array {
      return $this->oldInput;
   }
}