<?php
/**
 * @package     Phoenix/Core
 * @subpackage  Exceptions
 * @file        QueryException
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 18:00:03
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Phoenix\Core\Exceptions;


use RuntimeException;
use Throwable;

/**
 * Lanzada cuando una consulta a la base de datos falla durante su ejecución.
 *
 * Esta excepción encapsula no solo el error original del driver (ej. PDOException),
 * sino también el contexto específico de la consulta que falló (SQL y bindings),
 * lo cual es crucial para una depuración eficiente.
 */
class QueryException extends RuntimeException {
   /**
    * La sentencia SQL que falló.
    * @var string
    */
   protected string $sql;

   /**
    * Los bindings asociados a la sentencia SQL.
    * @var array<int|string, mixed>
    */
   protected array $bindings;

   /**
    * Construye la excepción con información de depuración detallada.
    *
    * @param string $sql La sentencia SQL que causó el error.
    * @param array<int|string, mixed> $bindings Los parámetros enviados con la consulta.
    * @param Throwable $previous La excepción original del driver (ej. PDOException).
    */
   public function __construct(string $sql, array $bindings, Throwable $previous) {
      $this->sql = $sql;
      $this->bindings = $bindings;

      $message = $this->formatMessage($sql, $bindings, $previous);

      parent::__construct($message, (int) $previous->getCode(), $previous);
   }

   /**
    * Obtiene la sentencia SQL que falló.
    *
    * @return string
    */
   public function getSql(): string {
      return $this->sql;
   }

   /**
    * Obtiene los bindings que se usaron en la consulta fallida.
    *
    * @return array<int|string, mixed>
    */
   public function getBindings(): array {
      return $this->bindings;
   }

   /**
    * Formatea un mensaje de error claro y útil para el desarrollador.
    *
    * @param string $sql
    * @param array<int|string, mixed> $bindings
    * @param Throwable $previous
    * @return string
    */
   private function formatMessage(string $sql, array $bindings, Throwable $previous): string {
      $bindingsForDisplay = json_encode($bindings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      return sprintf(
         "Query execution failed: %s\n[SQL]: %s\n[Bindings]: %s",
         $previous->getMessage(),
         $sql,
         $bindingsForDisplay
      );
   }
}