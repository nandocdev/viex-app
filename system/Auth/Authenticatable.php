<?php
/**
 * @package     phast/system
 * @subpackage  Auth
 * @file        Authenticatable
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:48:48
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Auth;

interface Authenticatable {
   public function getAuthIdentifierName(): string; // Devuelve el nombre de la columna de ID (ej. 'id')
   public function getAuthIdentifier(); // Devuelve el valor del ID (ej. 123)
   public function getAuthPassword(): string; // Devuelve el hash de la contraseña
}