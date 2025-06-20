<?php
/**
 * @package     phast/system
 * @subpackage  Database
 * @file        DB
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-14 23:59:48
 * @version     1.0.0
 * @description
 */

namespace Phast\System\Database\CP;

use Phast\System\Database\Executor\Operations\InsertOperation;
use Phast\System\Database\Executor\Operations\UpdateOperation;
use Phast\System\Database\Executor\Operations\SelectOperation;
use Phast\System\Database\Executor\Operations\DeleteOperation;
use Phast\System\Database\Connection;

class DB {
   public function __construct(private Connection $connection) {
      $this->connection = $connection;
   }

   public function getConnection(): Connection {
      return $this->connection;
   }

   public function insert(): InsertOperation {
      return new InsertOperation($this->connection->getPDO());
   }

   public function update(): UpdateOperation {
      return new UpdateOperation($this->connection->getPDO());
   }

   public function select(): SelectOperation {
      return new SelectOperation($this->connection->getPDO());
   }

   public function delete(): DeleteOperation {
      return new DeleteOperation($this->connection->getPDO());
   }

   public function close(): void {
      $this->connection->close();
   }
}

