<?php

/**
 * @package     phast/system
 * @subpackage  Http
 * @file        Request
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:03:33
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Http;

class Request {
   public function getMethod(): string {
      return $_SERVER['REQUEST_METHOD'] ?? 'GET';
   }

   public function getPath(): string {
      $path = $_SERVER['REQUEST_URI'] ?? '/';
      $position = strpos($path, '?');

      return $position === false ? $path : substr($path, 0, $position);
   }

   public function getBody(): array {
      $body = [];

      if ($this->getMethod() === 'GET') {
         foreach ($_GET as $key => $value) {
            $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
         }
      }

      if ($this->getMethod() === 'POST') {
         foreach ($_POST as $key => $value) {
            $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
         }
      }

      $json = json_decode(file_get_contents('php://input'), true);

      if (json_last_error() === JSON_ERROR_NONE) {
         $body = array_merge($body, $json);
      }

      return $body;
   }

   public function getHeader(string $name): ?string {
      $headers = $this->getAllHeaders();
      $name = strtolower($name);

      foreach ($headers as $key => $value) {
         if (strtolower($key) === $name) {
            return $value;
         }
      }

      return null;
   }

   public function set(string $name, mixed $value): void {
      self::${$name} = $value;
   }

   public function getAllHeaders(): array {
      $headers = [];

      foreach ($_SERVER as $key => $value) {
         if (str_starts_with($key, 'HTTP_')) {
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
         }
      }

      return $headers;
   }
}
