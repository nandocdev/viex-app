<?php

namespace Phast\App\Models;

use Phast\System\Database\ORM\Model;

class UserModel extends Model {
    /**
     * La tabla asociada con el modelo.
     * Si no se define, se inferirá a partir del nombre de la clase (ej: UserModel -> user_models).
     */
    protected string $table = 'user_models';

    /**
     * La clave primaria de la tabla.
     */
    protected string $primaryKey = 'id';

    /**
     * Indica si el modelo tiene timestamps automáticos.
     */
    public bool $timestamps = true;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected array $fillable = ['name', 'email', 'password'];
    /**
     * Oculta los atributos sensibles al serializar el modelo.
     */
    protected array $hidden = ['password', 'remember_token'];
    /**
     * Los atributos que se deben convertir a tipos específicos al serializar.
     */
    protected array $casts = [
        'email' => 'string',
        'password' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



}