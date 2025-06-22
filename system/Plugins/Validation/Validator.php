<?php
/**
 * @package     phast/system
 * @subpackage  Validation
 * @file        Validator
 * @description Motor principal para la validación de datos.
 */

declare(strict_types=1);

namespace Phast\System\Plugins\Validation;

class Validator {
   protected array $data;
   protected array $rules;
   protected array $errors = [];
   protected array $messages;

   /**
    * Mensajes de error por defecto. Pueden ser sobreescritos.
    */
   protected const DEFAULT_MESSAGES = [
      'required' => 'The :field field is required.',
      'email' => 'The :field must be a valid email address.',
      'min' => 'The :field must be at least :min characters.',
      'numeric' => 'The :field must be a number.',
      'string' => 'The :field must be a string.',
   ];

   private function __construct(array $data, array $rules, array $messages = []) {
      $this->data = $data;
      $this->rules = $rules;
      $this->messages = array_merge(self::DEFAULT_MESSAGES, $messages);
   }

   /**
    * Método de fábrica estático para crear y ejecutar el validador.
    */
   public static function make(array $data, array $rules, array $messages = []): self {
      $validator = new self($data, $rules, $messages);
      $validator->validate();
      return $validator;
   }

   /**
    * Ejecuta el proceso de validación.
    */
   protected function validate(): void {
      foreach ($this->rules as $field => $fieldRules) {
         $rules = explode('|', $fieldRules);
         $value = $this->data[$field] ?? null;

         foreach ($rules as $rule) {
            // Parsea la regla y sus parámetros (ej: min:8)
            $params = [];
            if (str_contains($rule, ':')) {
               [$rule, $paramString] = explode(':', $rule, 2);
               $params = explode(',', $paramString);
            }

            $methodName = 'validate' . ucfirst($rule);

            if (method_exists($this, $methodName)) {
               // Si una regla falla, no continuamos con las demás para ese campo.
               if (!$this->$methodName($field, $value, $params)) {
                  break;
               }
            }
         }
      }
   }

   public function passes(): bool {
      return empty($this->errors);
   }

   public function fails(): bool {
      return !$this->passes();
   }

   public function errors(): array {
      return $this->errors;
   }

   /**
    * Añade un mensaje de error para un campo y una regla.
    */
   protected function addError(string $field, string $rule, array $params = []): void {
      $message = $this->messages[$rule] ?? 'Validation failed for :field.';
      $message = str_replace(':field', $field, $message);

      // Reemplazar parámetros como :min, :max, etc.
      if (!empty($params)) {
         $message = str_replace(":$rule", $params[0], $message); // para 'min' => ':min'
      }

      $this->errors[$field][] = $message;
   }

   // --- Métodos de Reglas de Validación ---

   protected function validateRequired(string $field, $value): bool {
      if (is_null($value) || (is_string($value) && trim($value) === '')) {
         $this->addError($field, 'required');
         return false;
      }
      return true;
   }

   protected function validateEmail(string $field, $value): bool {
      if (!is_null($value) && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
         $this->addError($field, 'email');
         return false;
      }
      return true;
   }

   protected function validateMin(string $field, $value, array $params): bool {
      $min = (int) ($params[0] ?? 0);
      if (!is_null($value) && mb_strlen((string) $value) < $min) {
         $this->addError($field, 'min', $params);
         return false;
      }
      return true;
   }

   protected function validateNumeric(string $field, $value): bool {
      if (!is_null($value) && !is_numeric($value)) {
         $this->addError($field, 'numeric');
         return false;
      }
      return true;
   }

   protected function validateString(string $field, $value): bool {
      if (!is_null($value) && !is_string($value)) {
         $this->addError($field, 'string');
         return false;
      }
      return true;
   }
}