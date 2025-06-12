<?php

/**
 * @package     phast/system
 * @subpackage  Http
 * @file        Request
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 23:30:00
 * @version     2.0.0
 * @description Clase para encapsular y acceder a todos los datos de la solicitud HTTP entrante.
 */

declare(strict_types=1);

namespace Phast\System\Http;
use Phast\System\Plugins\Validation\Validator;
use Phast\System\Plugins\Validation\ValidationException;
class Request {
   protected array $server;
   protected array $cookies;
   protected array $session; // Cambiado a 'session' para evitar conflicto con $_SESSION global
   protected array $files;
   protected string $method;
   protected string $path; // Representa la ruta de la URI sin el query string
   protected string $fullUrl; // La URL completa de la solicitud
   protected string $baseUrl; // La URL base de la aplicación
   protected string $referer;
   protected string $ip;
   protected bool $isAjax;
   protected string $scheme;
   protected string $userAgent;
   protected string $contentType;
   protected int $contentLength;
   protected bool $isSecure;
   protected string $accept;
   protected string $proxyIp;
   protected string $host;
   protected array $body; // Contiene datos GET, POST, JSON combinados
   protected array $headers;
   protected array $params = []; // Para almacenar parámetros de ruta, populado por el router

   public function __construct() {
      $this->server = $_SERVER;
      $this->cookies = $_COOKIE;
      // Solo asignamos si $_SESSION está disponible y es un array, o un array vacío
      $this->session = isset($_SESSION) && is_array($_SESSION) ? $_SESSION : [];
      $this->files = $_FILES;

      $this->method = $this->determineMethod();
      $this->headers = $this->getHeaders(); // Necesario antes de contentType, userAgent, etc.
      $this->scheme = $this->determineScheme();
      $this->host = $this->getServerVar('HTTP_HOST', '');
      $this->path = $this->determinePath();
      $this->fullUrl = $this->determineFullUrl();
      $this->baseUrl = $this->determineBaseUrl();
      $this->referer = $this->getServerVar('HTTP_REFERER', '');
      $this->ip = $this->determineIp();
      $this->isAjax = $this->isAjaxRequest();
      $this->userAgent = $this->getServerVar('HTTP_USER_AGENT', '');
      $this->contentType = $this->getServerVar('CONTENT_TYPE', '');
      $this->contentLength = (int) $this->getServerVar('CONTENT_LENGTH', 0);
      $this->isSecure = $this->scheme === 'https';
      $this->accept = $this->getServerVar('HTTP_ACCEPT', '');
      $this->proxyIp = $this->setProxyIp();

      $this->body = $this->parseBody();
   }

   /**
    * Obtiene el método HTTP de la solicitud.
    * @return string
    */
   public function getMethod(): string {
      return $this->method;
   }

   /**
    * Obtiene la ruta de la URI sin el query string.
    * @return string
    */
   public function getPath(): string {
      return $this->path;
   }

   /**
    * Obtiene la URL completa de la solicitud.
    * @return string
    */
   public function getFullUrl(): string {
      return $this->fullUrl;
   }

   /**
    * Obtiene la URL base de la aplicación.
    * @return string
    */
   public function getBaseUrl(): string {
      return $this->baseUrl;
   }

   /**
    * Obtiene todos los datos del cuerpo de la solicitud (GET, POST, JSON).
    * @return array
    */
   public function getBody(): array {
      return $this->body;
   }

   /**
    * Obtiene un valor específico del cuerpo de la solicitud.
    * @param string $key La clave del dato.
    * @param mixed $default El valor por defecto si la clave no existe.
    * @return mixed
    */
   public function input(string $key, mixed $default = null): mixed {
      return $this->body[$key] ?? $default;
   }

   /**
    * Obtiene un valor de los headers de la solicitud.
    * @param string $name El nombre del header (case-insensitive).
    * @return string|null
    */
   public function getHeader(string $name): ?string {
      $name = strtolower($name);
      foreach ($this->headers as $key => $value) {
         if (strtolower($key) === $name) {
            return $value;
         }
      }
      return null;
   }

   /**
    * Obtiene todos los headers de la solicitud.
    * @return array
    */
   public function getAllHeaders(): array {
      return $this->headers;
   }

   /**
    * Obtiene todos los datos de las cookies.
    * @return array
    */
   public function getCookies(): array {
      return $this->cookies;
   }

   /**
    * Obtiene un valor de las cookies.
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function getCookie(string $key, mixed $default = null): mixed {
      return $this->cookies[$key] ?? $default;
   }

   /**
    * Obtiene todos los datos de la sesión (si están disponibles).
    * @return array
    */
   public function getSession(): array {
      return $this->session;
   }

   /**
    * Obtiene un valor de la sesión.
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function getSessionVar(string $key, mixed $default = null): mixed {
      return $this->session[$key] ?? $default;
   }

   /**
    * Obtiene los archivos subidos.
    * @return array
    */
   public function getFiles(): array {
      return $this->files;
   }

   /**
    * Obtiene la IP del cliente.
    * @return string
    */
   public function getIp(): string {
      return $this->ip;
   }

   /**
    * Verifica si la solicitud es AJAX.
    * @return bool
    */
   public function isAjax(): bool {
      return $this->isAjax;
   }

   /**
    * Obtiene el esquema de la solicitud (http o https).
    * @return string
    */
   public function getScheme(): string {
      return $this->scheme;
   }

   /**
    * Verifica si la solicitud es segura (HTTPS).
    * @return bool
    */
   public function isSecure(): bool {
      return $this->isSecure;
   }

   /**
    * Obtiene el User-Agent.
    * @return string
    */
   public function getUserAgent(): string {
      return $this->userAgent;
   }

   /**
    * Obtiene el Content-Type.
    * @return string
    */
   public function getContentType(): string {
      return $this->contentType;
   }

   /**
    * Obtiene el Content-Length.
    * @return int
    */
   public function getContentLength(): int {
      return $this->contentLength;
   }

   /**
    * Obtiene la IP del proxy (si existe).
    * @return string
    */
   public function getProxyIp(): string {
      return $this->proxyIp;
   }

   /**
    * Obtiene el host de la solicitud.
    * @return string
    */
   public function getHost(): string {
      return $this->host;
   }

   /**
    * Establece los parámetros de la ruta (normalmente llamados por el router).
    * @param array $params
    * @return void
    */
   public function setRouteParams(array $params): void {
      $this->params = $params;
   }

   /**
    * Obtiene los parámetros de la ruta.
    * @return array
    */
   public function getRouteParams(): array {
      return $this->params;
   }

   /**
    * Obtiene un parámetro de la ruta específico.
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function getRouteParam(string $key, mixed $default = null): mixed {
      return $this->params[$key] ?? $default;
   }

   /* --- Métodos internos de determinación --- */

   /**
    * Parsea el cuerpo de la solicitud (GET, POST, y JSON).
    * Prioriza JSON si es aplicable.
    * @return array
    */
   protected function parseBody(): array {
      // Simplemente combina los datos crudos. Sin sanitización aquí.
      $body = $_GET;
      if ($this->method === 'POST') {
         $body = array_merge($body, $_POST);
      }


      $rawBody = file_get_contents('php://input'); // ¡Leer solo una vez!
      $contentType = $this->getContentType();

      if (str_contains($contentType, 'application/json')) {
         $json = json_decode($rawBody, true);
         if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $body = array_merge($body, $json);
         }
      } elseif (in_array($this->method, ['PUT', 'DELETE', 'PATCH'])) {
         parse_str($rawBody, $parsedVars);
         $body = array_merge($body, $parsedVars);
      }

      return $body;
   }

   /**
    * Determina el método HTTP de la solicitud.
    * Permite la sobrescritura a través del header X-HTTP-Method-Override.
    * @return string
    */
   protected function determineMethod(): string {
      $override = $this->getServerVar('HTTP_X_HTTP_METHOD_OVERRIDE');
      if ($override) {
         return strtoupper($override);
      }
      return strtoupper($this->getServerVar('REQUEST_METHOD', 'GET'));
   }

   /**
    * Determina la ruta de la URI sin el query string.
    * @return string
    */
   protected function determinePath(): string {
      $path = $this->getServerVar('REQUEST_URI', '/');
      $position = strpos($path, '?');
      $path = $position === false ? $path : substr($path, 0, $position);
      // Asegúrate de que la ruta siempre comience con /
      return '/' . ltrim($path, '/');
   }

   /**
    * Determina la URL completa de la solicitud.
    * @return string
    */
   protected function determineFullUrl(): string {
      $scheme = $this->getScheme();
      $host = $this->getServerVar('HTTP_HOST', '');
      $uri = $this->getServerVar('REQUEST_URI', '/');
      return $scheme . '://' . $host . $uri;
   }

   /**
    * Determina la URL base de la aplicación (útil para subdirectorios).
    * @return string
    */
   protected function determineBaseUrl(): string {
      $scriptName = $this->getServerVar('SCRIPT_NAME', '');
      $requestUri = $this->getServerVar('REQUEST_URI', '');
      $baseUrl = str_replace(basename($scriptName), '', $scriptName);

      // Si la URL actual está en un subdirectorio, ajusta la base URL
      if (strpos($requestUri, $baseUrl) === 0) {
         return rtrim($baseUrl, '/');
      }

      // Para index.php en el root
      return ''; // O '/', dependiendo de cómo quieras que se vea la base para el root
   }

   /**
    * Determina la IP del cliente, considerando proxies.
    * @return string
    */
   protected function determineIp(): string {
      foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
         if (!empty($this->server[$key])) {
            $ip = trim(explode(',', $this->server[$key])[0]); // Maneja múltiples IPs en X-Forwarded-For
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
               return $ip;
            }
         }
      }
      return '';
   }

   /**
    * Determina si la solicitud es AJAX.
    * @return bool
    */
   protected function isAjaxRequest(): bool {
      return strtolower($this->getServerVar('HTTP_X_REQUESTED_WITH', 'none')) === 'xmlhttprequest';
   }

   /**
    * Determina el esquema (http o https).
    * @return string
    */
   protected function determineScheme(): string {
      if (
         ($this->getServerVar('HTTPS') === 'on') ||
         ($this->getServerVar('HTTP_X_FORWARDED_PROTO') === 'https') ||
         ($this->getServerVar('SERVER_PORT') === '443')
      ) {
         return 'https';
      }
      return 'http';
   }

   /**
    * Intenta obtener la IP del proxy.
    * @return string
    */
   protected function setProxyIp(): string {
      foreach (['HTTP_FORWARDED', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED_FOR_IP', 'VIA', 'X_FORWARDED_FOR', 'FORWARDED_FOR', 'X_FORWARDED_FOR_IP', 'HTTP_PROXY_CONNECTION'] as $key) {
         if (isset($this->server[$key]) && filter_var($this->server[$key], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $this->server[$key];
         }
      }
      return '';
   }

   /**
    * Obtiene un valor del array $_SERVER de forma segura.
    * @param string $key La clave a buscar.
    * @param mixed $default El valor por defecto si la clave no existe.
    * @return mixed
    */
   protected function getServerVar(string $key, mixed $default = ''): mixed {
      return $this->server[$key] ?? $default;
   }

   /**
    * Obtiene todos los headers de la solicitud.
    * @return array
    */
   protected function getHeaders(): array {
      // getallheaders() es una función nativa si PHP se ejecuta como módulo Apache,
      // pero no en otros entornos (ej. Nginx con FPM).
      if (function_exists('getallheaders')) {
         return getallheaders();
      }

      $headers = [];
      foreach ($this->server as $name => $value) {
         if (str_starts_with($name, 'HTTP_')) {
            // Convierte HTTP_USER_AGENT a User-Agent
            $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$headerName] = $value;
         } elseif (in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
            // Incluye Content-Type y Content-Length que no tienen prefijo HTTP_
            $headerName = str_replace('_', '-', ucwords(strtolower($name), '-'));
            $headers[$headerName] = $value;
         }
      }
      return $headers;
   }

   /**
    * Valida los datos de la petición contra un conjunto de reglas.
    *
    * @param array $rules Las reglas de validación.
    * @param array $messages Mensajes de error personalizados.
    * @return array Los datos validados.
    * @throws ValidationException Si la validación falla.
    */
   public function validate(array $rules, array $messages = []): array {
      $validator = Validator::make($this->getBody(), $rules, $messages);

      if ($validator->fails()) {
         throw new ValidationException($validator->errors(), $this->getBody());
      }

      // ¡Importante! Devuelve solo los datos que estaban en las reglas.
      // Esto previene vulnerabilidades de asignación masiva.
      return array_intersect_key($this->getBody(), $rules);
   }
}
