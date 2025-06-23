<?php

/**
 * @package     viex.com/app
 * @subpackage  Services
 * @file        RateLimiter
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-22 20:28:04
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Services;

use Phast\System\Plugins\Session\SessionManager;

class RateLimiter
{
   public function __construct(protected SessionManager $session) {}

   /**
    * Registra un intento para una clave dada.
    * Retorna true si se ha excedido el límite de intentos.
    */
   public function attempt(string $key, int $maxAttempts = 5, int $decayMinutes = 1): bool
   {
      $attempts = $this->getAttempts($key);
      $attempts++;

      $this->session->set($this->key($key), [
         'attempts' => $attempts,
         'expires' => time() + ($decayMinutes * 60)
      ]);

      return $attempts > $maxAttempts;
   }

   /**
    * Limpia los intentos para una clave dada.
    */
   public function clear(string $key): void
   {
      $this->session->forget($this->key($key));
   }

   private function getAttempts(string $key): int
   {
      $data = $this->session->get($this->key($key));

      if (!$data || time() > $data['expires']) {
         $this->clear($key);
         return 0;
      }

      return $data['attempts'];
   }

   private function key(string $key): string
   {
      return 'rate_limiter_' . sha1($key);
   }
}
