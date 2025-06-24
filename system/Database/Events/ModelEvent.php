<?php

/**
 * @package     phast/system
 * @subpackage  Database/Events
 * @file        ModelEvent
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Clase base para eventos de modelos
 */

declare(strict_types=1);

namespace Phast\System\Database\Events;

use Phast\System\Database\Model;

abstract class ModelEvent
{
   protected Model $model;
   protected array $attributes;

   public function __construct(Model $model, array $attributes = [])
   {
      $this->model = $model;
      $this->attributes = $attributes;
   }

   public function getModel(): Model
   {
      return $this->model;
   }

   public function getAttributes(): array
   {
      return $this->attributes;
   }
}
