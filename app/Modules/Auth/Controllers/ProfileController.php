<?php
/**
 * @package     Modules/Auth
 * @subpackage  Controllers
 * @file        ProfileController
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-22 22:32:23
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Modules\Auth\Controllers;

use Phast\System\Auth\AuthManager;
use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\System\Plugins\Session\SessionManager;
use Phast\App\Modules\Auth\Models\Entities\UserEntity;
use Phast\System\Rendering\Core\ViewData;
class ProfileController {
   public function __construct(
      protected AuthManager $auth,
      protected SessionManager $session
   ) {
   }

   public function showProfileAction(Request $request, Response $response): Response {
      /** @var UserEntity $user */
      $user = $this->auth->user();
      // convierte el usuario a stdClass para pasarlo a la vista
      // $user = (array) $user;

      $data = new ViewData(
         'Mi Perfil',
         [],
         $user, // Convertimos el usuario a stdClass para pasarlo a la vista
         [
            'pageTitle' => 'Mi Perfil',
            'breadcrumb' => [
               ['label' => 'Inicio', 'url' => route('home.index')],
               ['label' => 'Mi Perfil', 'url' => route('profile.show')],
            ],
         ]
      );
      // return $response->json($user);


      // Pasamos el objeto de usuario completo a la vista
      return $response->view('users/profile', $data);
   }

   public function updateProfileAction(Request $request, Response $response): Response {
      /** @var UserEntity $user */
      $user = $this->auth->user();

      // 1. Definimos las reglas de validación para los campos editables.
      $rules = [
         'office_phone' => 'nullable|string|max:20',
         'personal_phone' => 'nullable|string|max:20',
      ];

      // 2. Validamos la petición. Si falla, se lanzará una ValidationException
      // que automáticamente redirigirá al usuario con los errores.
      $validatedData = $request->validate($rules);

      // 3. Actualizamos el modelo con los datos validados.
      $user->office_phone = $validatedData['office_phone'];
      $user->personal_phone = $validatedData['personal_phone'];

      // 4. Guardamos los cambios en la base de datos.
      // Asumimos que el ORM tiene un método save() que maneja el UPDATE.
      $user->save();

      // 5. Redirigimos de vuelta a la página de perfil con un mensaje de éxito.
      return $response->redirect(route('profile.show'))
         ->withSuccess('¡Tu perfil ha sido actualizado con éxito!');
   }
}