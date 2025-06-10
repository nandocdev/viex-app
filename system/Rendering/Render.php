<?php

/**
 * @package     phast/system
 * @subpackage  Rendering
 * @file        Render
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 21:50:34
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering;

use Phast\System\Rendering\Contracts\ViewEngine;
use Phast\System\Rendering\Core\TemplateLoader;
use Phast\System\Rendering\View;


class Render {
   private TemplateLoader $templateLoader;
   private ViewEngine $viewEngine; // Dependencia a la interfaz del motor de vistas

   /**
    * Constructor de la clase Render.
    * @param TemplateLoader $templateLoader El cargador de rutas de plantillas.
    * @param ViewEngine $viewEngine El motor de vistas a utilizar (ej. PhpEngine).
    */
   public function __construct(TemplateLoader $templateLoader, ViewEngine $viewEngine) {
      $this->templateLoader = $templateLoader;
      $this->viewEngine = $viewEngine;
   }

   /**
    * Renderiza una vista completa (vista + layout).
    * @param View $view El objeto View que contiene el nombre de la vista, subruta, layout y datos.
    * @return string El contenido HTML/string renderizado.
    * @throws \InvalidArgumentException Si alguna plantilla no existe.
    */
   public function render(View $view): string {
      // 1. Obtener la ruta de la vista principal
      $viewPath = $this->templateLoader->loadViewPath(
         $view->getViewName()
      );

      // 2. Renderizar el contenido de la vista
      $viewContent = $this->viewEngine->render($viewPath, $view->getData());

      // 3. Obtener la ruta del layout
      $layoutPath = $this->templateLoader->loadLayoutPath($view->getLayoutName());

      // 4. Leer el contenido raw del layout (sin ejecutar PHP aún)
      $layoutRawContent = file_get_contents($layoutPath);
      if ($layoutRawContent === false) {
         throw new \RuntimeException("No se pudo leer el contenido del layout: {$layoutPath}");
      }

      // 5. Inyectar el contenido de la vista en el marcador del layout (@content)
      $finalContent = str_replace('@content', $viewContent, $layoutRawContent);

      // 6. Compilar el layout con el contenido de la vista y los datos
      // Usamos compileContent aquí porque ya inyectamos el @content y ahora queremos ejecutar el PHP del layout
      return $this->viewEngine->compileContent($finalContent, $view->getData(), $layoutPath);
   }
}
