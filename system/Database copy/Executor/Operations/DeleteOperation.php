<?php
/**
 * @package     Database/Executor
 * @subpackage  Operations
 * @file        DeleteOperation
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-14 23:32:11
 * @version     1.0.0
 * @description Clase que se encarga de gestionar las operaciones de eliminación en la base de datos antes de ser ejecutadas.
 */

namespace Phast\System\Database\Executor\Operations;

use \PDO;

class DeleteOperation {
   function __construct(private PDO $pdo) {
   }

   function execute(string $sql, array $params): bool {
      $this->pdo->beginTransaction();
      try {
         $params = $this->sanitizeInput($params);
         // Sanitizar los datos de entrada para evitar inyecciones SQL
         if (strpos($sql, 'DELETE') !== 0) {
            // throw new \Exception("Invalid SQL statement for delete operation."); # en Español
            throw new \Exception("Declaración SQL inválida para la operación de eliminación.");
         }

         $stmt = $this->pdo->prepare($sql);
         $stmt->execute($params);
         $this->pdo->commit();
         return $stmt->rowCount() > 0;
      } catch (\Exception $e) {
         $this->pdo->rollBack();
         throw new \Exception("Error ejecutando la operación de eliminación: " . $e->getMessage());
      }
   }

   function sanitizeInput(array $data): array {
      foreach ($data as $key => $value) {
         if (is_string($value)) {
            $data[$key] = htmlspecialchars(strip_tags($value));
         }
      }
      return $data;
   }

}