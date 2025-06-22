<?php
/**
 * @package     system/Auth
 * @subpackage  Contracts
 * @file        Guard
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 01:48:10
 * @version     1.0.0
 * @description
 */
declare(strict_types=1);
namespace Phast\System\Auth\Contracts;

use Phast\System\Auth\Authenticatable;

interface Guard {
   public function check(): bool; // ¿Hay un usuario autenticado?
   public function guest(): bool; // ¿Es un invitado (no autenticado)?
   public function user(): ?Authenticatable; // Devuelve el modelo del usuario autenticado o null
   public function id(); // Devuelve el ID del usuario autenticado o null
   public function validate(array $credentials = []): bool; // Valida credenciales sin iniciar sesión
   public function attempt(array $credentials = [], bool $remember = false): bool; // Intenta iniciar sesión
   public function login(Authenticatable $user, bool $remember = false): void; // Inicia sesión con un modelo de usuario
   public function logout(): void; // Cierra la sesión
}