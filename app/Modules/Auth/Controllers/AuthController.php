<?php

namespace Phast\App\Modules\Auth\Controllers;

use Phast\System\Auth\AuthManager;
use Phast\System\Database\DB;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Plugins\Session\SessionManager;
use Phast\App\Modules\Auth\Models\Entities\UserEntity;

use Phast\App\Services\RateLimiter;

class AuthController
{

    public function __construct(
        protected AuthManager $auth,
        protected SessionManager $session,
        protected RateLimiter $limiter
    ) {}
    public function indexAction(Request $request, Response $response): Response
    {
        return $response->view('auth/index', [], 'auth');
    }

    /**
     * metodo que se encarga de recibir el formulario de login
     * y autenticar al usuario.
     */

    public function loginAction(Request $request, Response $response): Response
    {

        $throttleKey = strtolower($request->input('email')) . '|' . $request->getIp();
        // Verificar si se han superado los intentos
        if ($this->limiter->attempt($throttleKey)) {
            return $response->redirect('/auth')
                ->withError('Demasiados intentos de inicio de sesión. Por favor, inténtelo de nuevo más tarde.');
        }
        // 1. Validar los datos de entrada
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar al usuario
        if ($this->auth->attempt($credentials)) {
            // 3. Login exitoso: regenerar la sesión y cargar datos
            $this->limiter->clear($throttleKey);
            $this->session->regenerate();

            /** @var UserEntity $user */
            $user = $this->auth->user();

            // 4. Cargar perfiles y permisos
            $permissions = $this->loadUserPermissions($user->getAuthIdentifier());

            $this->session->set('user_id', $user->getAuthIdentifier());
            $this->session->set('user_permissions', $permissions); // Guardamos la lista de permisos
            $this->session->set('user_full_name', $user->first_name . ' ' . $user->last_name);

            // 5. Redirigir al dashboard (o a donde sea necesario)
            return $response->redirect('/dashboard');
        }
        // 6. Login fallido: redirigir de vuelta con un error
        return $response->redirect('/auth')
            ->withError('Las credenciales proporcionadas no son correctas.');
    }

    public function logoutAction(Request $request, Response $response): Response
    {
        $this->auth->logout();
        $this->session->destroy(); // Asegura que la sesión se destruya completamente
        return $response->redirect('/auth');
    }

    private function loadUserPermissions(int $userId): array
    {
        $permissionsQuery = "
            SELECT DISTINCT p.name
            FROM permissions p
            JOIN user_group_permissions ugp ON p.id = ugp.permission_id
            JOIN user_user_groups uug ON ugp.user_group_id = uug.user_group_id
            WHERE uug.user_id = ?
        ";

        // Asumiendo que tenemos una clase DB para consultas directas
        $results = DB::select($permissionsQuery, [$userId]);

        // Devolvemos un array plano con los nombres de los permisos
        // ej: ['work.create', 'work.approve.coordinator', 'user.manage']
        return array_column($results, 'name');
    }
}
