<?php
/**
 * @package     Database/Executor
 * @subpackage  Operations
 * @file        SelectOperation
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-14 23:32:22
 * @version     1.0.0
 * @description Clase que se encarga de gestionar las operaciones de selección en la base de datos antes de ser ejecutadas.
 * @note        Esta clase es un esqueleto y debe ser implementada según las necesidades específicas de la aplicación.
 */

namespace Phast\System\Database\Executor\Operations;

class SelectOperation {
   public function __construct(private \PDO $pdo) {
   }
   public function execute(string $query, array $params = []): array {

      try {
         $params = $this->sanitizeInput($params);
         // Sanitizar los datos de entrada para evitar inyecciones SQL
         if (strpos($query, 'SELECT') !== 0) {
            throw new \Exception("Declaración SQL inválida para la operación de selección.");
         }

         $stmt = $this->pdo->prepare($query);
         $stmt->execute($params);

         $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

         // Si no se encuentra ningún resultado, retornar un array vacío
         if ($stmt->rowCount() === 0) {
            return [];
         }
         return $result ?: [];
      } catch (\Exception $e) {
         throw new \Exception("Error ejecutando la operación de selección: " . $e->getMessage());
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