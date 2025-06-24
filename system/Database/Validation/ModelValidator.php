<?php

/**
 * @package     phast/system
 * @subpackage  Database/Validation
 * @file        ModelValidator
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Validador integrado para modelos
 */

declare(strict_types=1);

namespace Phast\System\Database\Validation;

use Phast\System\Database\Model;
use Phast\System\Plugins\Validation\ValidationException;

class ModelValidator {
   protected Model $model;
   protected array $rules = [];
   protected array $messages = [];
   protected array $errors = [];

   public function __construct(Model $model) {
      $this->model = $model;
   }

   /**
    * Establece las reglas de validación
    */
   public function setRules(array $rules): self {
      $this->rules = $rules;
      return $this;
   }

   /**
    * Establece los mensajes de error personalizados
    */
   public function setMessages(array $messages): self {
      $this->messages = $messages;
      return $this;
   }

   /**
    * Valida los atributos del modelo
    */
   public function validate(array $attributes = []): bool {
      $attributes = $attributes ?? $this->model->getAttributes();
      $this->errors = [];

      foreach ($this->rules as $field => $rules) {
         $value = $attributes[$field] ?? null;
         $fieldRules = is_array($rules) ? $rules : explode('|', $rules);

         foreach ($fieldRules as $rule) {
            $this->validateField($field, $value, $rule);
         }
      }

      return empty($this->errors);
   }

   /**
    * Valida un campo específico
    */
   protected function validateField(string $field, $value, string $rule): void {
      $ruleName = $rule;
      $parameters = [];

      if (strpos($rule, ':') !== false) {
         [$ruleName, $parameterString] = explode(':', $rule, 2);
         $parameters = explode(',', $parameterString);
      }

      $method = 'validate' . ucfirst($ruleName);

      if (method_exists($this, $method)) {
         if (!$this->$method($field, $value, $parameters)) {
            $this->addError($field, $ruleName, $parameters);
         }
      }
   }

   /**
    * Agrega un error de validación
    */
   protected function addError(string $field, string $rule, array $parameters = []): void {
      $message = $this->messages[$field][$rule] ?? $this->getDefaultMessage($field, $rule, $parameters);
      $this->errors[$field][] = $message;
   }

   /**
    * Obtiene el mensaje por defecto
    */
   protected function getDefaultMessage(string $field, string $rule, array $parameters = []): string {
      $messages = [
         'required' => "El campo {$field} es obligatorio.",
         'email' => "El campo {$field} debe ser un email válido.",
         'min' => "El campo {$field} debe tener al menos {$parameters[0]} caracteres.",
         'max' => "El campo {$field} no puede tener más de {$parameters[0]} caracteres.",
         'numeric' => "El campo {$field} debe ser numérico.",
         'unique' => "El valor del campo {$field} ya existe.",
         'exists' => "El valor del campo {$field} no existe.",
      ];

      return $messages[$rule] ?? "El campo {$field} no es válido.";
   }

   /**
    * Obtiene los errores de validación
    */
   public function getErrors(): array {
      return $this->errors;
   }

   /**
    * Verifica si hay errores
    */
   public function hasErrors(): bool {
      return !empty($this->errors);
   }

   // --- REGLAS DE VALIDACIÓN ---

   protected function validateRequired(string $field, $value): bool {
      return $value !== null && $value !== '';
   }

   protected function validateEmail(string $field, $value): bool {
      return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
   }

   protected function validateMin(string $field, $value, array $parameters): bool {
      $min = (int) $parameters[0];
      return strlen($value) >= $min;
   }

   protected function validateMax(string $field, $value, array $parameters): bool {
      $max = (int) $parameters[0];
      return strlen($value) <= $max;
   }

   protected function validateNumeric(string $field, $value): bool {
      return is_numeric($value);
   }

   protected function validateUnique(string $field, $value, array $parameters): bool {
      $table = $parameters[0] ?? $this->model->getTable();
      $column = $parameters[1] ?? $field;
      $ignoreId = $parameters[2] ?? null;

      $query = $this->model->newQuery()
         ->from($table)
         ->where($column, '=', $value);

      if ($ignoreId) {
         $query->where('id', '!=', $ignoreId);
      }

      return $query->count() === 0;
   }

   protected function validateExists(string $field, $value, array $parameters): bool {
      $table = $parameters[0];
      $column = $parameters[1] ?? $field;

      return $this->model->newQuery()
         ->from($table)
         ->where($column, '=', $value)
         ->count() > 0;
   }
}
