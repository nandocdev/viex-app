<?php
/**
 * @package     Auth/Models
 * @subpackage  Entities
 * @file        UserEntity
 * @author      Fernando Castillo <fdocst@gmail.com>
 * @date        2025-06-22 17:59:50
 * @version     1.0.0
 * @description
 */

declare(strict_types=1);

namespace Phast\App\Modules\Auth\Models\Entities;
use Phast\System\Database\Model;
use Phast\System\Auth\Authenticatable;

class UserEntity extends Model implements Authenticatable{
   protected string $table = 'users';

   protected array $fillable = [
       'username', 'password_hash', 'first_name', 'last_name', 
       'cedula', 'email', 'office_phone', 'personal_phone',
       'main_organizational_unit_id', 'is_active'
   ];

   protected array $hidden = [
       'password_hash',
   ];

   public function getAuthIdentifierName(): string
   {
       return 'id';
   }

   public function getAuthIdentifier()
   {
       return $this->id;
   }

   public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function groups()
    {
        // Nota: Esta es una relación muchos-a-muchos.
        // Asumo que el ORM Phoenix soportará `belongsToMany` en el futuro.
        // Por ahora, esta definición es conceptual. La carga la haremos manualmente.
    }
}