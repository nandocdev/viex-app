<?php

/**
 * @package     phast/system
 * @subpackage  Rendering
 * @file        View
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-09 21:50:43
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering;

class View {
   private string $viewName;
   private string $layoutName;
   private array $data;

   public function __construct(string $viewName, array $data = [],  string $layoutName = 'default') {
      $this->viewName = $viewName;
      $this->layoutName = $layoutName;
      $this->data = $data;
   }

   public function getViewName(): string {
      return $this->viewName;
   }


   public function getLayoutName(): string {
      return $this->layoutName;
   }

   public function getData(): array {
      return $this->data;
   }

   /**
    * Fusiona datos adicionales con los datos existentes de la vista.
    * @param array $additionalData
    * @return self Una nueva instancia de View con los datos fusionados.
    */
   public function withData(array $additionalData): self {
      $new = clone $this;
      $new->data = array_merge($this->data, $additionalData);
      return $new;
   }
}
