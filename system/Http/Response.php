<?php

/**
 * @package     phast/system
 * @subpackage  Http
 * @file        Response
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:03:43
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Http;

use Phast\System\Core\Container;
use Phast\System\Rendering\View;
use Phast\System\Rendering\Render;
use Phast\System\Plugins\Session\SessionManager;

class Response
{
   /**
    * @param string $body El cuerpo de la respuesta.
    * @param int $statusCode El código de estado HTTP.
    * @param array $headers Las cabeceras HTTP.
    */
   public function __construct(
      protected string $body = '',
      protected int $statusCode = 200,
      protected array $headers = ['Content-Type' => 'text/html; charset=utf-8']
   ) {}

   // --- Getters para el Front Controller ---

   public function getBody(): string
   {
      return $this->body;
   }

   public function getStatusCode(): int
   {
      return $this->statusCode;
   }

   public function getHeaders(): array
   {
      return $this->headers;
   }

   // --- Métodos de la API Fluida (Inmutables) ---

   /**
    * Establece el código de estado.
    * Crea una nueva instancia para mantener la inmutabilidad.
    */
   public function status(int $code): self
   {
      $new = clone $this;
      $new->statusCode = $code;
      return $new;
   }

   // public function withError(string $message): self {
   //    // Crea una respuesta de error con el mensaje proporcionado.
   //    $new = clone $this;
   //    $new->statusCode = 500; // Código de error interno del servidor.
   //    $new->body = "<h1>Error</h1><p>{$message}</p>";
   //    return $new->header('Content-Type', 'text/html; charset=utf-8');
   // }

   public function withError(string $message): self
   {
      Container::getInstance()->resolve(SessionManager::class)->flash('error', $message);
      return $this;
   }





   /**
    * Añade o sobreescribe una cabecera.
    */
   public function header(string $name, string $value): self
   {
      $new = clone $this;
      // Normaliza el nombre de la cabecera para evitar duplicados por mayúsculas/minúsculas
      $new->headers[ucwords(strtolower($name), '-')] = $value;
      return $new;
   }

   /**
    * Prepara una respuesta JSON.
    */
   public function json(array|object $data, int $statusCode = 200): self
   {
      try {
         $body = json_encode($data, JSON_THROW_ON_ERROR);
      } catch (\JsonException $e) {
         // Manejar el error de codificación, quizás con una respuesta de error interna.
         $body = json_encode(['error' => 'Failed to encode JSON response.']);
         $statusCode = 500;
      }

      $new = $this->status($statusCode)
         ->header('Content-Type', 'application/json');
      $new->body = $body;

      return $new;
   }

   /**
    * Prepara una respuesta de vista renderizada.
    * ¡Delega la renderización al servicio de Vistas del contenedor!
    */
   public function view(string $viewName, array $data = [], string $layoutName = 'default'): self
   {
      // 1. Obtener el servicio de renderizado del contenedor.
      $renderer = Container::getInstance()->resolve(Render::class);

      // 2. Crear el objeto View.
      $viewObject = new View($viewName, $data, $layoutName);

      // 3. Renderizar la vista.
      $viewContent = $renderer->render($viewObject);

      // 4. Devolver una nueva instancia de Response con el contenido.
      $new = clone $this;
      $new->body = $viewContent;
      // Asegura que el Content-Type es correcto para HTML
      return $new->header('Content-Type', 'text/html; charset=utf-8');
   }

   /**
    * Prepara una respuesta de redirección.
    */
   public function redirect(string $url, int $statusCode = 302): self
   {
      // Valida que el código de estado sea uno de redirección.
      if ($statusCode < 300 || $statusCode > 308) {
         $statusCode = 302;
      }

      return $this->status($statusCode)
         ->header('Location', $url)
         ->setBody(''); // El cuerpo de una redirección debe estar vacío.
   }

   /**
    * Añade una cookie a la respuesta.
    * La cookie se enviará usando la función setcookie() en el Front Controller.
    * Aquí solo preparamos la cabecera 'Set-Cookie'.
    */
   public function cookie(
      string $name,
      string $value = '',
      int $expires = 0,
      string $path = '/',
      string $domain = '',
      bool $secure = true,
      bool $httpOnly = true,
      string $sameSite = 'Lax' // 'Lax' es un default más seguro que 'Strict' para la mayoría de casos.
   ): self {
      $cookieString = urlencode($name) . '=' . urlencode($value);
      if ($expires !== 0) {
         $cookieString .= '; Expires=' . gmdate('D, d M Y H:i:s T', $expires);
      }
      $cookieString .= '; Path=' . $path;
      if ($domain) {
         $cookieString .= '; Domain=' . $domain;
      }
      if ($secure) {
         $cookieString .= '; Secure';
      }
      if ($httpOnly) {
         $cookieString .= '; HttpOnly';
      }
      $cookieString .= '; SameSite=' . $sameSite;

      // Usamos addHeader para manejar múltiples Set-Cookie
      return $this->addHeader('Set-Cookie', $cookieString);
   }

   /**
    * Método helper para añadir cabeceras sin sobreescribir las existentes del mismo nombre.
    * Útil para Set-Cookie.
    */
   public function addHeader(string $name, string $value): self
   {
      $new = clone $this;
      $name = ucwords(strtolower($name), '-');

      if (!isset($new->headers[$name])) {
         $new->headers[$name] = [];
      }

      // Convertimos a array si no lo es
      if (!is_array($new->headers[$name])) {
         $new->headers[$name] = [$new->headers[$name]];
      }

      $new->headers[$name][] = $value;
      return $new;
   }

   /**
    * Establece el cuerpo de la respuesta.
    * No debería ser público para fomentar el uso de métodos como json() o view().
    */
   protected function setBody(string $body): self
   {
      $new = clone $this;
      $new->body = $body;
      return $new;
   }
}
