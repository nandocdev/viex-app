<?php

/**
 * @package     phast/system
 * @subpackage  Database/Events
 * @file        ModelDeleted
 * @author      Fernando Castillo <nando.castillo@outlook.com>
 * @date        2025-06-20 15:00:00
 * @version     1.0.0
 * @description Evento cuando se elimina un modelo
 */

declare(strict_types=1);

namespace Phast\System\Database\Events;

use Phast\System\Database\Model;

class ModelDeleted extends ModelEvent
{
   public function __construct(Model $model, array $attributes = [])
   {
      parent::__construct($model, $attributes);
   }
}
