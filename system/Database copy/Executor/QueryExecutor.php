<?php
/**
 * @package     system/Database
 * @subpackage  Executor
 * @file        QueryExecutor
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-14 23:29:58
 * @version     1.0.0
 * @description
 */

namespace Phast\System\Database\Executor;



class QueryExecutor {
   function __construct(private \PDO $pdo) {
   }

   function execute(string $query, array $params = []): \PDOStatement {

      // Sanitizar los datos de entrada para evitar inyecciones SQL
      $params = $this->sanitizeInput($params);

      // Preparar y ejecutar la consulta
      if (strpos($query, 'SELECT') === 0) {
         $query = str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $query);
      }
      $statement = $this->pdo->prepare($query);
      $statement->execute($params);
      return $statement;
   }

   // metodo que limpia los datos de entrada para evitar inyecciones SQL
   function sanitizeInput(array $data): array {
      foreach ($data as $key => $value) {
         if (is_string($value)) {
            $data[$key] = htmlspecialchars(strip_tags($value));
         }
      }
      return $data;
   }


}