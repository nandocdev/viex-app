# Phoenix ORM

Phoenix es un ORM (Object-Relational Mapper) moderno y modular para PHP 8+, diseñado para ofrecer una experiencia fluida, segura y eficiente en el mapeo entre objetos y bases de datos relacionales. Soporta múltiples motores SQL (MySQL, Oracle, etc.), aprovecha los atributos de PHP 8 y sigue buenas prácticas de arquitectura y diseño.

---

## Tabla de Contenidos

-  [Instalación](#instalación)
-  [Conceptos Básicos](#conceptos-básicos)
-  [Definición de Entidades](#definición-de-entidades)
-  [Atributos de Entidad](#atributos-de-entidad)
-  [Consultas Básicas](#consultas-básicas)
-  [Consultas Avanzadas](#consultas-avanzadas)
-  [Relaciones](#relaciones)
-  [Transacciones](#transacciones)
-  [Migraciones y Seeds](#migraciones-y-seeds)
-  [Extensión y Personalización](#extensión-y-personalización)
-  [Preguntas Frecuentes](#preguntas-frecuentes)
-  [Licencia](#licencia)

---

## Instalación

Phoenix está pensado para integrarse fácilmente en cualquier proyecto PHP 8+. Puedes instalarlo mediante Composer (si está disponible) o incluirlo directamente en tu proyecto.

```bash
composer require phast/phoenix
```

Configura tu conexión en el archivo de configuración correspondiente:

```php
return [
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'database' => 'mi_base',
    'username' => 'usuario',
    'password' => 'secreto',
    'charset'  => 'utf8mb4',
];
```

---

## Conceptos Básicos

-  **Entidad:** Clase PHP que representa una tabla de la base de datos.
-  **Hydrator:** Se encarga de convertir arrays en entidades y viceversa.
-  **Director:** Orquesta la ejecución de consultas y operaciones CRUD.
-  **QueryBuilder:** Permite construir consultas SQL de forma fluida y segura.
-  **Grammar:** Traduce el QueryBuilder al dialecto SQL del motor correspondiente.
-  **MetadataRegistry:** Servicio centralizado para obtención y cacheo de metadatos de entidades.

---

## Definición de Entidades

Las entidades deben extender `AbstractEntity` y usar atributos PHP para definir el mapeo.

```php
use Phast\System\Phoenix\Entity\AbstractEntity;
use Phast\System\Phoenix\Entity\Attributes\Table;
use Phast\System\Phoenix\Entity\Attributes\Column;
use Phast\System\Phoenix\Entity\Attributes\PrimaryKey;

#[Table(name: 'users')]
class User extends AbstractEntity
{
    #[PrimaryKey('id')]
    #[Column(name: 'id')]
    protected int $id;

    #[Column(name: 'email')]
    protected string $email;

    #[Column(name: 'password')]
    protected string $password;
}
```

---

## Atributos de Entidad

-  `#[Table(name: ...)]` — Define el nombre de la tabla.
-  `#[PrimaryKey(name: ...)]` — Define la clave primaria.
-  `#[Column(name: ...)]` — Define el nombre de la columna.

---

## Consultas Básicas

### Obtener un registro

```php
$user = $director->firstRaw('SELECT * FROM users WHERE id = ?', [1], User::class);
```

### Insertar

```php
$user = new User();
$user->setAttribute('email', 'test@demo.com');
$user->setAttribute('password', 'secreto');
$director->insert($user);
```

### Actualizar

```php
$user->setAttribute('email', 'nuevo@demo.com');
$director->update($user);
```

### Eliminar

```php
$director->delete($user);
```

---

## Consultas Avanzadas

### QueryBuilder fluido

```php
$builder = (new QueryBuilder('users'))
    ->where('email', '=', 'test@demo.com')
    ->orderBy('id', 'DESC')
    ->limit(10);

$sql = $grammar->compileSelect($builder);
$results = $adapter->rawQuery($sql, $builder->bindings['where']);
```

---

## Relaciones

Phoenix soporta relaciones entre entidades mediante estrategias:

-  **HasMany**
-  **BelongsTo**
-  **HasOne**

Ejemplo:

```php
class Post extends AbstractEntity
{
    // ...
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
```

---

## Transacciones

```php
$transaction->begin();
try {
    $director->insert($user);
    $director->insert($profile);
    $transaction->commit();
} catch (\Exception $e) {
    $transaction->rollback();
    throw $e;
}
```

---

## Migraciones y Seeds

Phoenix no incluye migraciones por defecto, pero puedes integrarlas fácilmente usando scripts SQL o herramientas externas.

---

## Extensión y Personalización

-  **Soporte para nuevos motores:** Implementa una nueva clase Grammar y Adapter.
-  **Nuevos atributos:** Puedes crear tus propios atributos para metadatos adicionales.
-  **Estrategias de relación:** Implementa nuevas estrategias para relaciones avanzadas.

---

## Preguntas Frecuentes

**¿Puedo usar claves primarias compuestas?**  
Sí, extendiendo el atributo `PrimaryKey` y adaptando el Hydrator.

**¿Cómo agrego validaciones?**  
Implementa lógica en tus entidades o usa eventos antes/después de guardar.

**¿Puedo usar Phoenix fuera de frameworks?**  
Sí, es independiente y puede integrarse en cualquier proyecto PHP 8+.

---

## Licencia

Phoenix ORM es software libre bajo la licencia MIT.

---

**Autor:** Fernando Castillo  
**Contacto:** fdocst@gmail.com
