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
use Phast\System\View\View;

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

   public function view(string $view, array $data = [], string $layout = null): self {
      $viewContent = Container::getInstance()->resolve(View::class)->render($view, $data, $layout);
      $this->body = $viewContent;
      return $this;
   }
}
