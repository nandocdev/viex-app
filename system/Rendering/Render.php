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
      // 1. Renderizar el contenido de la vista primero
      $viewPath = $this->templateLoader->loadViewPath($view->getViewName());
      // Pasamos los datos a la vista. El motor los pondrá disponibles.
      $viewContent = $this->viewEngine->render($viewPath, $view->getData());

      // 2. Renderizar el layout, pasándole el contenido de la vista como un dato más.
      $layoutPath = $this->templateLoader->loadLayoutPath($view->getLayoutName());

      // Fusionamos los datos originales con el contenido de la vista.
      $layoutData = array_merge($view->getData(), ['content' => $viewContent]);

      return $this->viewEngine->render($layoutPath, $layoutData);
   }
}
