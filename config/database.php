<?php

/**
 * @package     http/phast
 * @subpackage  config
 * @file        database
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:50:54
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);



// Este archivo lee las variables de entorno y las estructura
// para el DatabaseManager.

return [
   /*
    |--------------------------------------------------------------------------
    | Conexión de Base de Datos por Defecto
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar cuál de las conexiones de abajo usar
    | por defecto en toda tu aplicación.
    |
    */
   'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',

   /*
    |--------------------------------------------------------------------------
    | Conexiones de Base de Datos
    |--------------------------------------------------------------------------
    |
    | Aquí están todas las configuraciones de conexión para tu aplicación.
    | Soportamos MySQL, PostgreSQL y SQLite.
    |
    */
   'connections' => [
      'mysql' => [
         'driver' => 'mysql',
         'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
         'port' => $_ENV['DB_PORT'] ?? '3306',
         'database' => $_ENV['DB_DATABASE'] ?? 'phast_db',
         'username' => $_ENV['DB_USERNAME'] ?? 'root',
         'password' => $_ENV['DB_PASSWORD'] ?? '',
         'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
      ],

      'pgsql' => [
         'driver' => 'pgsql',
         'host' => $_ENV['DB_HOST_PGSQL'] ?? '127.0.0.1',
         'port' => $_ENV['DB_PORT_PGSQL'] ?? '5432',
         'database' => $_ENV['DB_DATABASE_PGSQL'] ?? 'phast_db',
         'username' => $_ENV['DB_USERNAME_PGSQL'] ?? 'root',
         'password' => $_ENV['DB_PASSWORD_PGSQL'] ?? '',
      ],

      'sqlite' => [
         'driver' => 'sqlite',
         // La ruta es relativa al directorio raíz del proyecto.
         'database' => $_ENV['DB_DATABASE_SQLITE'] ?? dirname(__DIR__) . '/storage/database.sqlite',
      ],
   ],
];
