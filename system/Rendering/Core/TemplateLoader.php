<?php

/**
 * @package     system/Rendering
 * @subpackage  Core
 * @file        TemplateLoader
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 21:50:15
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Core;

use Phast\System\Core\Container;
use Phast\System\Core\Application;
use \InvalidArgumentException;

class TemplateLoader {
   private string $layoutsBasePath;
   private string $viewsBasePath;
   private string $partialsBasePath; // Una ruta base para parciales globales/comunes
   const DS = DIRECTORY_SEPARATOR;

   public function __construct(private readonly string $basePath) {

      $this->layoutsBasePath = rtrim($basePath . '/resources/templates/layouts', self::DS) . self::DS;
      $this->viewsBasePath = rtrim($basePath . '/resources/views', self::DS) . self::DS;
      $this->partialsBasePath = rtrim($basePath . '/resources/templates/partials', self::DS) . self::DS;

      if (!is_dir($this->layoutsBasePath)) {
         throw new InvalidArgumentException("La ruta base de layouts no es un directorio válido: {$this->layoutsBasePath}");
      }
      if (!is_dir($this->viewsBasePath)) {
         throw new InvalidArgumentException("La ruta base de vistas no es un directorio válido: {$this->viewsBasePath}");
      }
      // Opcional: Validar si la ruta de parciales global existe y es un directorio
      if ($this->partialsBasePath && !is_dir($this->partialsBasePath)) {
         throw new InvalidArgumentException("La ruta base de parciales no es un directorio válido: {$this->partialsBasePath}");
      }
   }

   /**
    * Carga la ruta completa de un layout.
    * @param string $layoutName El nombre del layout (ej. 'default').
    * @return string La ruta completa del archivo de layout.
    * @throws InvalidArgumentException Si el archivo de layout no existe.
    */
   public function loadLayoutPath(string $layoutName): string {
      // Asume un formato de archivo específico para layouts, ej. 'default/index.layout.phtml'
      $layoutPath = $this->layoutsBasePath . $layoutName . self::DS . 'index.layout.phtml';

      if (!file_exists($layoutPath)) {
         throw new InvalidArgumentException("El archivo de layout no existe: {$layoutPath}");
      }
      return $layoutPath;
   }

   /**
    * Carga la ruta completa de una vista.
    * @param string $viewName El nombre de la vista (ej. 'home').
    * @param string $viewSubPath Una subruta dentro del directorio de vistas (ej. 'Dashboard').
    * @return string La ruta completa del archivo de vista.
    * @throws InvalidArgumentException Si el archivo de vista no existe.
    */
   public function loadViewPath(string $viewName, ): string {
      $fullPath = $this->viewsBasePath;
      $viewPath = $fullPath . $viewName . '.view.phtml';

      if (!file_exists($viewPath)) {
         throw new InvalidArgumentException("El archivo de vista no existe: {$viewPath}");
      }
      return $viewPath;
   }

   /**
    * Carga la ruta completa de un parcial.
    * @param string $partialName El nombre del parcial (ej. 'header').
    * @param string $baseTemplatePath La ruta del archivo de plantilla (layout o vista) donde se usa el parcial,
    * para buscar parciales relativos.
    * @return string La ruta completa del archivo de parcial.
    * @throws InvalidArgumentException Si el archivo de parcial no existe.
    */
   public function loadPartialPath(string $partialName, string $baseTemplatePath = ''): string {
      $partialPath = '';

      // Prioridad: buscar parciales relativos a la plantilla actual
      if (!empty($baseTemplatePath)) {
         $baseDir = dirname($baseTemplatePath);
         $relativePartialPath = $baseDir . self::DS . 'partials' . $partialName . '.partial.phtml';
         if (file_exists($relativePartialPath)) {
            return $relativePartialPath;
         }
      }

      // Segunda prioridad: buscar en una ruta de parciales global si está configurada
      if ($this->partialsBasePath) {
         $globalPartialPath = $this->partialsBasePath . $partialName . '.partial.phtml';
         if (file_exists($globalPartialPath)) {
            return $globalPartialPath;
         }
      }

      throw new InvalidArgumentException("El archivo de partial no existe: {$partialName}. Buscado en: " . ($baseTemplatePath ? dirname($baseTemplatePath) . '/partials/' : '') . " y " . $this->partialsBasePath);
   }
}
