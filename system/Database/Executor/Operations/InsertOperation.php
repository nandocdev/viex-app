<?php
/**
 * @package     Database/Executor
 * @subpackage  Operations
 * @file        InsertOperation
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-14 23:32:17
 * @version     1.0.0
 * @description Clase que se encarga de gestionar las operaciones de inserción en la base de datos antes de ser ejecutadas.
 */

namespace Phast\System\Database\Executor\Operations;

class InsertOperation {
   public function __construct(private \PDO $pdo) {
   }

   public function execute(string $sql, array $params): bool {
      $this->pdo->beginTransaction();
      try {
         $params = $this->sanitizeInput($params);
         // Sanitizar los datos de entrada para evitar inyecciones SQL
         if (strpos($sql, 'INSERT') !== 0) {
            // throw new \Exception("Invalid SQL statement for insert operation."); # en Español
            throw new \Exception("Declaración SQL inválida para la operación de inserción.");
         }

         $stmt = $this->pdo->prepare($sql);
         $stmt->execute($params);
         $this->pdo->commit();
         return $stmt->rowCount() > 0;
      } catch (\Exception $e) {
         $this->pdo->rollBack();
         throw new \Exception("Error ejecutando la operación de inserción: " . $e->getMessage());
      }
   }

   public function sanitizeInput(array $data): array {
      foreach ($data as $key => $value) {
         if (is_string($value)) {
            $data[$key] = htmlspecialchars(strip_tags($value));
         }
      }
      return $data;
   }

}