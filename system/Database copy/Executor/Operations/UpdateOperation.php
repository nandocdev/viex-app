<?php
/**
 * @package     Database/Executor
 * @subpackage  Operations
 * @file        UpdateOperation
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-14 23:32:27
 * @version     1.0.0
 * @description Clase que se encarga de gestionar las operaciones de actualización en la base de datos antes de ser ejecutadas.
 * @note        Esta clase es un esqueleto y debe ser implementada según las necesidades específicas de la aplicación.
 * @todo        Implementar la lógica de actualización y sanitización de datos.
 * @see         InsertOperation, DeleteOperation, SelectOperation
 */

namespace Phast\System\Database\Executor\Operations;

class UpdateOperation {
   public function __construct(private \PDO $pdo) {
   }

   public function execute(string $sql, array $params): bool {
      $this->pdo->beginTransaction();
      try {
         $params = $this->sanitizeInput($params);
         // Sanitizar los datos de entrada para evitar inyecciones SQL
         if (strpos($sql, 'UPDATE') !== 0) {
            // throw new \Exception("Invalid SQL statement for update operation."); # en Español
            throw new \Exception("Declaración SQL inválida para la operación de actualización.");
         }

         $stmt = $this->pdo->prepare($sql);
         $stmt->execute($params);
         $this->pdo->commit();
         return $stmt->rowCount() > 0;
      } catch (\Exception $e) {
         $this->pdo->rollBack();
         throw new \Exception("Error ejecutando la operación de actualización: " . $e->getMessage());
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