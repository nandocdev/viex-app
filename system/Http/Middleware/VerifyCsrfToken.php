<?php
/**
 * @package     phast/system
 * @subpackage  Http/Middleware
 * @file        VerifyCsrfToken
 * @description Middleware para proteger la aplicación contra ataques CSRF.
 */
declare(strict_types=1);

namespace Phast\System\Http\Middleware;

use Closure;
use Phast\System\Http\Request;
use Phast\System\Plugins\Session\SessionManager;
use Phast\System\Http\Exceptions\TokenMismatchException;

class VerifyCsrfToken {
   /**
    * Las URIs que deben ser excluidas de la verificación CSRF.
    * Útil para webhooks de APIs externas.
    * @var array
    */
   protected array $except = [
      // 'api/stripe/webhook',
   ];

   public function __construct(protected SessionManager $session) {
   }

   public function handle(Request $request, Closure $next) {
      // Si la URI está en la lista de excepciones o es una petición de lectura,
      // no hacemos nada más que asegurar que haya un token para la próxima vez.
      if ($this->isReadingRequest($request) || $this->inExceptArray($request)) {
         $this->addCookieToResponse($request);
         return $next($request);
      }

      $token = $request->input('_token') ?: $request->getHeader('X-CSRF-TOKEN');

      if (!$this->session->validateToken($token)) {
         throw new TokenMismatchException('CSRF token mismatch.');
      }

      return $next($request);
   }

   protected function isReadingRequest(Request $request): bool {
      return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
   }

   protected function inExceptArray(Request $request): bool {
      foreach ($this->except as $except) {
         if ($except !== '/') {
            $except = trim($except, '/');
         }
         if ($request->getPath() === $except) {
            return true;
         }
      }
      return false;
   }

   /**
    * Asegura que haya un token en la sesión.
    * En un sistema real, también se añadiría el token a una cookie para ser leído por JS.
    */
   protected function addCookieToResponse(Request $request): void {
      if (!$this->session->getToken()) {
         $this->session->regenerateToken();
      }
   }
}