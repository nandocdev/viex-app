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

class Response {

   public function __construct(
      protected string $body = '',
      protected int $statusCode = 200,
      protected array $headers = ['Content-Type' => 'text/html']
   ) {
   }

   public function getBody(): string {
      return $this->body;
   }

   public function getStatusCode(): int {
      return $this->statusCode;
   }

   public function getHeaders(): array {
      return $this->headers;
   }

   public function json(array|object $data, int $statusCode = 200): self {
      $this->body = json_encode($data);
      $this->statusCode = $statusCode;
      $this->headers['Content-Type'] = 'application/json';
      return $this;
   }

   public function send(string $body = '', int $statusCode = 200, array $headers = []): self {
      $this->body = $body;
      $this->statusCode = $statusCode;
      $this->headers = array_merge($this->headers, $headers);
      return $this;
   }

   // public function view(string $view, array $data = [], string $layout = ''): self {
   //    $viewContent = Container::getInstance()
   //       ->resolve(Render::class)
   //       ->render(new View($view, $data, $layout));
   //    $this->body = $viewContent;
   //    return $this;
   // }

   public function view(string $viewName, array $data = [], string $layoutName = 'default'): self {
      // 1. Crear una instancia de la clase View de manera correcta.
      // Aquí pasamos los argumentos en el orden y tipo correctos:
      // viewName, viewSubPath, layoutName, data
      $viewObject = new View($viewName, $data, $layoutName);

      try {
         // 2. Obtener una instancia del Render principal del contenedor.
         // Es el Render quien orquesta la carga de plantillas y el motor de vistas.
         $renderer = Container::getInstance()->resolve(Render::class);

         // 3. Llamar al método render del objeto Render, pasándole la instancia de View.
         $viewContent = $renderer->render($viewObject);

         $this->body = $viewContent;
         return $this;
      } catch (\Exception $e) {
         // Manejo de errores: loggea o lanza una excepción más específica.
         // Para depuración, puedes re-lanzar la excepción.
         throw new \Exception("Error al renderizar la vista '{$viewName}': " . $e->getMessage(), 0, $e);
      }
   }
}
