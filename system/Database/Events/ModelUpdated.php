<?php

/**
 * @package     phast/system
 * @subpackage  Database/Events
 * @file        ModelUpdated
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Evento cuando se actualiza un modelo
 */

declare(strict_types=1);

namespace Phast\System\Database\Events;

use Phast\System\Database\Model;

class ModelUpdated extends ModelEvent
{
   protected array $original;

   public function __construct(Model $model, array $attributes = [], array $original = [])
   {
      parent::__construct($model, $attributes);
      $this->original = $original;
   }

   public function getOriginal(): array
   {
      return $this->original;
   }
}
