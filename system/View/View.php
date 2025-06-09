<?php

/**
 * @package     phast/system
 * @subpackage  View
 * @file        View
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:12:21
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\View;

/**
 * Motor de plantillas de Phast.
 * Fusiona la carga de plantillas, el manejo de datos y el renderizado.
 * Soporta layouts (@content), parciales (@partial) y datos compartidos.
 */

class View {
   protected string $viewsPath;
   protected string $layoutsPath;
   protected string $partialsPath;

   /**
    * @var array Datos compartidos globalmente con todas las vistas.
    */
   protected array $sharedData = [];
   protected string $defaultExtension = '.phtml';

   /**
    * View constructor.
    * Las dependencias (basePath) se inyectan a través del contenedor de DI.
    *
    * @param string $basePath La ruta base de la aplicación.
    */
   public function __construct(string $basePath) {
      // Unificamos la estructura de directorios dentro de /app/Views
      $this->viewsPath = $basePath . '/app/Views/';
      $this->layoutsPath = $basePath . '/app/Views/layouts/';
      $this->partialsPath = $basePath . '/app/Views/partials/';
   }

   /**
    * Comparte datos que estarán disponibles en todas las vistas.
    *
    * @param array $data Array asociativo de datos para compartir.
    */
   public function share(array $data): void {
      $this->sharedData = array_merge($this->sharedData, $data);
   }

   /**
    * Renderiza una vista, opcionalmente dentro de un layout.
    *
    * @param string $view El nombre de la vista (ej: 'users.show').
    * @param array $data Datos específicos para esta vista.
    * @param string|null $layout El layout a utilizar. Si es null, no se usa layout.
    * @return string El contenido HTML renderizado.
    * @throws Exception Si la vista o el layout no se encuentran.
    */
   public function render(string $view, array $data = [], ?string $layout = 'main'): string {
      // Fusiona datos compartidos y locales. Los locales tienen prioridad.
      $finalData = array_merge($this->sharedData, $data);

      // Renderiza el contenido principal de la vista.
      $viewContent = $this->renderFile($this->getViewPath($view), $finalData);

      // Si se especifica un layout, renderiza el layout y reemplaza @content.
      if ($layout) {
         $layoutContent = $this->renderFile($this->getLayoutPath($layout), $finalData);
         $finalContent = str_replace('@content', $viewContent, $layoutContent);
      } else {
         $finalContent = $viewContent;
      }

      // Procesa cualquier directiva @partial en el resultado final.
      return $this->processPartials($finalContent, $finalData);
   }

   /**
    * Renderiza un archivo PHP y devuelve su contenido como una cadena.
    *
    * @param string $filePath Ruta absoluta al archivo de la plantilla.
    * @param array $data Datos para extraer en el scope de la plantilla.
    * @return string Contenido renderizado.
    */
   private function renderFile(string $filePath, array $data): string {
      if (!file_exists($filePath)) {
         throw new \Exception("Template file not found: {$filePath}");
      }

      // extrae las variables del array asociativo para que estén disponibles en la vista.
      extract($data);

      ob_start();
      include $filePath;
      return ob_get_clean();
   }

   /**
    * Procesa las directivas @partial() en el contenido renderizado.
    *
    * @param string $content El contenido HTML a procesar.
    * @param array $data Los datos disponibles para el parcial.
    * @return string El contenido con los parciales reemplazados.
    */
   private function processPartials(string $content, array $data): string {
      $pattern = '/@partial\(\s*\'([a-zA-Z0-9\.\/_-]+)\'\s*\)/';

      return preg_replace_callback($pattern, function ($matches) use ($data) {
         $partialName = $matches[1];
         try {
            $partialPath = $this->getPartialPath($partialName);
            return $this->renderFile($partialPath, $data);
         } catch (\Exception $e) {
            // En modo debug, es útil ver el error. En producción, podrías querer un string vacío.
            if ($_ENV['APP_DEBUG'] === 'true') {
               return "<!-- Partial '{$partialName}' not found. -->";
            }
            return '';
         }
      }, $content);
   }

   /**
    * Normaliza un nombre de vista (ej. 'users.index') a una ruta de archivo.
    *
    * @param string $name
    * @return string
    */
   private function normalizePath(string $name): string {
      return str_replace('.', '/', $name) . $this->defaultExtension;
   }

   private function getViewPath(string $name): string {
      return $this->viewsPath . $this->normalizePath($name);
   }

   private function getLayoutPath(string $name): string {
      return $this->layoutsPath . $this->normalizePath($name);
   }

   private function getPartialPath(string $name): string {
      return $this->partialsPath . $this->normalizePath($name);
   }
}
