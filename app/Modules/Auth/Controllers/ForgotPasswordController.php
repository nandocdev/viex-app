<?php
/**
 * @package     Modules/Auth
 * @subpackage  Controllers
 * @file        ForgotPasswordController
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-23 09:04:15
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Modules\Auth\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Phast\App\Services\MailerService;
use Phast\App\Modules\Auth\Models\Entities\UserEntity;
use Carbon\Carbon; // Recomendado: composer require nesbot/carbon

class ForgotPasswordController {
   public function __construct(protected MailerService $mailer) {
   }

   public function showLinkRequestFormAction(Request $request, Response $response): Response {
      return $response->view('auth/forgot-password', [], 'auth');
   }

   public function sendResetLinkEmailAction(Request $request, Response $response): Response {
      $request->validate(['email' => 'required|email']);
      $email = $request->input('email');

      // Por seguridad, no revelamos si el usuario existe o no.
      $user = UserEntity::where('email', '=', $email)->first();
      if ($user) {
         // Generar token
         $token = bin2hex(random_bytes(32));

         // Guardar en la BD
         DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()] // now() asume Carbon
         );

         // Renderizar la plantilla del correo
         $resetUrl = route('password.reset', ['token' => $token], true);
         $emailBody = (new Response())->view('emails/password_reset', [
            'resetUrl' => $resetUrl,
            'userName' => $user->first_name
         ], null);

         $this->mailer->send($email, 'Restablecer Contraseña - VIEX', $emailBody);
      }

      return $response->redirect(route('password.request'))
         ->withSuccess('Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.');
   }

   public function showResetFormAction(Request $request, Response $response, string $token): Response {
      return $response->view('auth/reset-password', ['token' => $token], 'auth');
   }

   public function resetPasswordAction(Request $request, Response $response): Response {
      $validatedData = $request->validate([
         'token' => 'required',
         'email' => 'required|email',
         'password' => 'required|min:8',
         'password_confirmation' => 'required|same:password'
      ]);

      $resetRecord = DB::table('password_reset_tokens')
         ->where('email', '=', $validatedData['email'])
         ->where('token', '=', $validatedData['token'])
         ->first();

      // Validar token y expiración (ej. 60 minutos)
      if (!$resetRecord || Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
         return $response->redirect(route('password.request'))
            ->withError('El token de restablecimiento no es válido o ha expirado.');
      }

      $user = UserEntity::where('email', '=', $validatedData['email'])->first();
      if (!$user) {
         return $response->redirect(route('password.request'))
            ->withError('No se encontró un usuario con ese correo electrónico.');
      }

      // Actualizar contraseña y eliminar token
      $user->password_hash = password_hash($validatedData['password'], PASSWORD_DEFAULT);
      $user->save();
      DB::table('password_reset_tokens')->where('email', '=', $validatedData['email'])->delete();

      return $response->redirect(route('auth.login.form'))
         ->withSuccess('¡Tu contraseña ha sido restablecida! Ya puedes iniciar sesión.');
   }
}