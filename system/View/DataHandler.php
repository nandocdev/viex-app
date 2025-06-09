<?php

/**
 * @package     phast/system
 * @subpackage  View
 * @file        DataHandler
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-08 23:14:17
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\View;

class DataHandler {
   private array $data = [];
   private array $sharedData = [];

   public function setData(array $data, bool $override = false): void {
      $this->data = $override ? $data : array_merge($this->data, $data);
   }

   public function share(array $data): void {
      $this->sharedData = array_merge($this->sharedData, $data);
   }

   public function getData(string $key, mixed $default = null): mixed {
      return $this->data[$key] ?? $this->sharedData[$key] ?? $default;
   }

   public function removeData(string $key): void {
      unset($this->data[$key]);
   }

   public function prepareForView(): array {
      return array_merge($this->sharedData, $this->data);
   }
}
