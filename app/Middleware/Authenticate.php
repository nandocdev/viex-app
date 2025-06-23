<?php
/**
 * @package     viex.com/app
 * @subpackage  Middlewares
 * @file        Authenticate
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-22 22:24:03
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Middleware;

use Closure;
use Phast\System\Auth\AuthManager;
use Phast\System\Http\Request;
use Phast\System\Http\Response;

class Authenticate {
   public function __construct(protected AuthManager $auth) {
   }

   public function handle(Request $request, Closure $next) {
      // Usamos el método `guest()` del AuthManager. Es legible y directo.
      if ($this->auth->guest()) {
         // Si el usuario NO está autenticado, creamos una nueva respuesta
         // y la redirigimos a la ruta del formulario de login.
         // Usamos nuestro helper `route()` para mantener el código limpio.
         return (new Response())->redirect(route('auth.login.form'))
            ->withError('Debes iniciar sesión para acceder a esta página.');
      }

      // Si el código llega hasta aquí, significa que el usuario SÍ está autenticado.
      // Llamamos a `$next($request)` para permitir que la petición continúe su flujo
      // hacia el controlador.
      return $next($request, new Response());
   }
}