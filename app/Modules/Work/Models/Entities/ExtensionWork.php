<?php
/**
 * @package     Work/Models
 * @subpackage  Entities
 * @file        ExtensionWork
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-25 22:44:46
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Modules\Work\Models\Entities;

use Phast\System\Database\Model;

class ExtensionWork extends Model {
   protected string $table = 'extension_works';

   protected array $fillable = [
      'title', 'work_type_id', 'primary_responsible_user_id',
      'organizational_unit_id', 'start_date', 'end_date',
      'current_status_id', 'is_draft'
   ];
}