<?php

/**
 * @package     phast/system
 * @subpackage  View
 * @file        TemplateLoader
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:15:03
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\View;

use Phast\System\Core\Container;

class TemplateLoader {
   private string $viewsPath;
   private string $layoutsPath;
   private string $partialsPath;

   public function __construct() {
      $basePath = Container::getInstance()->resolve('basePath');

      $this->viewsPath = $basePath . '/app/Views/';
      $this->layoutsPath = $basePath . '/resources/layouts/';
      $this->partialsPath = $basePath . '/resources/partials/';
   }

   public function loadView(string $view): string {
      $path = $this->viewsPath . $view . '.php';
      if (!file_exists($path)) {
         throw new \InvalidArgumentException("View file not found: {$view}");
      }
      return $path;
   }

   public function loadLayout(string $layout): string {
      $path = $this->layoutsPath . $layout . '.php';
      if (!file_exists($path)) {
         throw new \InvalidArgumentException("Layout file not found: {$layout}");
      }
      return $path;
   }

   public function loadPartial(string $partial): string {
      $path = $this->partialsPath . $partial . '.php';
      if (!file_exists($path)) {
         throw new \InvalidArgumentException("Partial file not found: {$partial}");
      }
      return $path;
   }
}
