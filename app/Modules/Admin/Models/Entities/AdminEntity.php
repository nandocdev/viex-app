<?php
/**
 * Plantilla de Entidad Genérica
 *
 * Propósito: Representar una entidad de dominio y su mapeo con la base de datos.
 *
 * TODO: Reemplaza 'GenericEntity' con el nombre de tu entidad (ej. Product).
 * TODO: Reemplaza el namespace con la ruta correcta de tu módulo.
 */
declare(strict_types=1);

namespace Phast\App\Modules\Admin\Models\Entities;

use Phast\System\Database\Model;
// TODO: Importa las interfaces necesarias, como Authenticatable si es un modelo de usuario.
// use Phast\System\Auth\Authenticatable; 
// TODO: Importa los Value Objects que usarás en los 'casts'.
// use Phast\App\Modules\ModuleName\Models\ValueObjects\SpecificValueObject;

/**
 * TODO: Añade anotaciones @property para el autocompletado del IDE.
 * @property-read int $id
 * @property string $name
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
class AdminEntity extends Model {
    //--------------------------------------------------------------------------
    // SECCIÓN 1: CONFIGURACIÓN DEL ORM Y LA BASE DE DATOS
    //--------------------------------------------------------------------------

    /**
     * El nombre de la tabla de la base de datos.
     * Si se omite, Phast intentará adivinarlo a partir del nombre de la clase
     * (ej. 'Product' se convierte en 'products').
     *
     * TODO: Descomenta y ajusta si el nombre de tu tabla no sigue la convención.
     * @var string
     */
    // protected string $table = 'nombre_de_la_tabla';

    /**
     * La clave primaria de la tabla.
     *
     * TODO: Descomenta y ajusta si tu clave primaria no es 'id'.
     * @var string
     */
    // protected string $primaryKey = 'uuid';

    /**
     * Indica si el modelo debe gestionar las columnas `created_at` y `updated_at`.
     * Por defecto es `true`. Cámbialo a `false` si tu tabla no las tiene.
     *
     * TODO: Ajusta si es necesario.
     * @var bool
     */
    protected bool $timestamps = true;

    /**
     * Lista blanca de atributos que se pueden asignar masivamente.
     * ¡Medida de seguridad CRUCIAL! Solo los campos aquí listados podrán
     * ser rellenados usando `::create()` o `->update([...])`.
     *
     * TODO: Rellena este array con las columnas de tu tabla que pueden ser
     *       modificadas desde una petición de usuario.
     * @var array
     */
    protected array $fillable = [
        // 'name',
        // 'description',
        // 'price',
        // 'category_id',
    ];

    /**
     * Atributos que deben ser ocultados al serializar el modelo (a array o JSON).
     * Útil para información sensible como contraseñas, tokens, etc.
     *
     * TODO: Añade aquí los campos que no quieres exponer en tus respuestas de API.
     * @var array
     */
    protected array $hidden = [
        // 'password',
        // 'api_token',
    ];

    /**
     * Conversión automática de atributos a tipos nativos o Value Objects.
     * Mejora la integridad y la seguridad de los datos.
     *
     * TODO: Define las conversiones para tus columnas.
     * @var array
     */
    protected array $casts = [
        'id'         => 'int',
        // 'price'      => 'float',
        // 'is_published' => 'bool',
        // 'published_at' => 'datetime',
        // 'options'    => 'json', // Convierte a/desde array asociativo
        // 'email'      => SpecificValueObject::class, // Ejemplo de Value Object
    ];

    //--------------------------------------------------------------------------
    // SECCIÓN 2: LÓGICA DE NEGOCIO (COMPORTAMIENTO DEL DOMINIO)
    //--------------------------------------------------------------------------
    // Aquí defines lo que tu entidad puede HACER, sus reglas y sus estados.

    /**
     * Ejemplo de un método de negocio.
     * public function publish(): void
     * {
     *     if ($this->is_published) {
     *         throw new \Exception('This entity is already published.');
     *     }
     *     $this->is_published = true;
     *     $this->published_at = new \DateTime();
     * }
     */

    //--------------------------------------------------------------------------
    // SECCIÓN 3: RELACIONES CON OTRAS ENTIDADES
    //--------------------------------------------------------------------------
    // Define cómo se relaciona esta entidad con otras.

    /**
     * Ejemplo de una relación "pertenece a".
     * public function category()
     * {
     *     // TODO: Implementar lógica de relación en el Model base de Phast.
     *     // return $this->belongsTo(Category::class, 'category_id');
     * }
     */
     
    /**
     * Ejemplo de una relación "tiene muchos".
     * public function comments()
     * {
     *     // TODO: Implementar lógica de relación en el Model base de Phast.
     *     // return $this->hasMany(Comment::class, 'entity_id');
     * }
     */

    //--------------------------------------------------------------------------
    // SECCIÓN 4: IMPLEMENTACIÓN DE INTERFACES DEL FRAMEWORK
    //--------------------------------------------------------------------------
    // TODO: Si este es un modelo de usuario, implementa la interfaz Authenticatable.
    /*
    public function getAuthIdentifierName(): string
    {
        return $this->primaryKey;
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }
    */
}