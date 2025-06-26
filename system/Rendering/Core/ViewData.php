<?php
/**
 * @package     system/Rendering
 * @subpackage  Core
 * @file        ViewData
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-25 21:29:50
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\System\Rendering\Core;
use Phast\System\Auth\Authenticatable;
// use \stdClass;

class ViewData {
   public readonly string $pageTitle;
   public readonly mixed $data;
   public readonly Authenticatable|array $user;
   public readonly array $extra;
   public function __construct(
      string $pageTitle,
      mixed $data = [],
      Authenticatable|array $user = [],
      array $extra = []
   ) {
      $this->pageTitle = $pageTitle;
      $this->data = $data;
      $this->user = $user;
      $this->extra = $extra;
   }

   public function toArray(): array {
      return [
         'pageTitle' => $this->pageTitle,
         'data' => $this->data,
         'user' => $this->user,
         'extra' => $this->extra,
      ];
   }

   public function __get(string $name) {
      if (property_exists($this, $name)) {
         return $this->$name;
      }
      throw new \InvalidArgumentException("Property '$name' does not exist in " . __CLASS__);
   }

   // permite establecer propiedades dinámicamente
   public function __set(string $name, mixed $value): void {
      if (property_exists($this, $name)) {
         $this->$name = $value;
      } else {
         throw new \InvalidArgumentException("Property '$name' does not exist in " . __CLASS__);
      }
   }
}