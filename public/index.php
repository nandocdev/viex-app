<?php

/**
 * @package     http/phast
 * @subpackage  public
 * @file        index
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 22:57:56
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

define('PHAST_BASE_PATH', dirname(__DIR__));
// 1. Registrar el autoloader de Composer
require_once PHAST_BASE_PATH . '/vendor/autoload.php';


// 2. Crear una instancia de la aplicación
// La aplicación se encargará de cargar el entorno, los servicios y las rutas.
$app = new Phast\System\Core\Application(
   PHAST_BASE_PATH
);


// 3. Ejecutar la aplicación
// Esto inicia el proceso de enrutamiento y envío de la respuesta.
$app->run();
