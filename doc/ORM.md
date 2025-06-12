¡Perfecto, Nando! A continuación te presento la **documentación de usuario** para el componente ORM de tu sistema `phast/system`, asumiendo el rol de un desarrollador senior y utilizando un tono técnico, claro y estructurado.

---

# 📘 Documentación de Usuario - ORM `phast/system`

## 🧩 Descripción General

El ORM (`Object-Relational Mapping`) de `phast/system` es una implementación ligera y extensible que permite interactuar con la base de datos mediante modelos PHP orientados a objetos, sin escribir SQL directamente. Soporta relaciones, control de atributos, timestamps, serialización y más.

Este sistema está inspirado en el enfoque de Laravel Eloquent, pero diseñado para ser independiente, portable y adaptable a distintos motores (MySQL, PostgreSQL, SQLite, SQL Server).

---

## ⚙️ Requisitos Previos

-  PHP >= 8.1
-  Extensión PDO habilitada
-  Archivo de configuración `config/database.php` con conexiones definidas

---

## 🏗️ Estructura del Modelo

Todos los modelos deben extender la clase abstracta `Phast\System\Database\ORM\Model`.

```php
use Phast\System\Database\ORM\Model;

class User extends Model {
   protected array $fillable = ['name', 'email', 'password'];
   protected array $hidden = ['password'];
}
```

### Propiedades configurables

| Propiedad     | Descripción                                               |
| ------------- | --------------------------------------------------------- |
| `$table`      | Nombre explícito de la tabla. Se infiere si no se define. |
| `$primaryKey` | Clave primaria del modelo. Default: `id`.                 |
| `$timestamps` | Activa/desactiva los campos `created_at` y `updated_at`.  |
| `$fillable`   | Lista blanca para asignación masiva (`fill`, `create`).   |
| `$hidden`     | Atributos a excluir en `toArray()`/`toJson()`.            |
| `$visible`    | Lista blanca de atributos visibles (anula `$hidden`).     |

---

## 🧪 Métodos Básicos

```php
$user = new User(['name' => 'Nando']);
$user->save(); // Inserta

$user->name = 'Fernando';
$user->save(); // Actualiza solo si hubo cambios

$user->delete(); // Elimina
```

También es posible usar acceso estilo propiedad:

```php
echo $user->name;
$user->email = 'test@example.com';
```

---

## 🔍 Consultas

### Métodos Estáticos

```php
User::where('email', '=', 'nando@example.com')->first();
User::find(1);
User::create([...]);
```

### Query Builder

```php
User::query()->where('active', '=', 1)->orderBy('name')->get();
```

---

## 🔗 Relaciones

Las relaciones se definen en el modelo usando métodos protegidos:

### hasOne / belongsTo

```php
class User extends Model {
   protected function phone() {
      return $this->hasOne(Phone::class);
   }
}

class Phone extends Model {
   protected function user() {
      return $this->belongsTo(User::class);
   }
}
```

Acceso:

```php
$phone = $user->phone()->getResults();
```

### hasMany (ya implementado)

```php
class User extends Model {
   protected function posts() {
      return $this->hasMany(Post::class);
   }
}
```

---

## 🧠 Atributos y Cambios

```php
$user->getAttributes(); // Todos los atributos actuales
$user->getDirty();      // Solo los modificados
$user->syncOriginal();  // Sincroniza cambios después de guardar
```

---

## 📦 Serialización

```php
$array = $user->toArray();
$json = $user->toJson(JSON_PRETTY_PRINT);
```

Control con `$hidden` y `$visible`.

---

## 🧬 Transacciones

Usa la fachada `Database` para ejecutar transacciones:

```php
use Phast\System\Database\Facades\DB;

DB::transaction(function ($db) {
   User::create([...]);
   Log::create([...]);
});
```

---

## 📂 Conexión y Configuración

El archivo `config/database.php` debe retornar un arreglo con:

```php
return [
   'default' => 'mysql',
   'connections' => [
      'mysql' => [
         'driver' => 'mysql',
         'host' => '127.0.0.1',
         'database' => 'phast',
         'username' => 'root',
         'password' => '',
         'charset' => 'utf8mb4',
      ],
   ],
];
```

---

## ❗ Excepciones

El método `findOrFail()` lanza una `ModelNotFoundException` si el modelo no se encuentra:

```php
try {
   $user = User::findOrFail(999);
} catch (ModelNotFoundException $e) {
   // Manejo de error
}
```

---

## 📚 Clases y Traits Clave

| Clase / Trait      | Descripción                                      |
| ------------------ | ------------------------------------------------ |
| `Model`            | Clase base del ORM                               |
| `Builder`          | Query builder orientado a modelos                |
| `HasAttributes`    | Manejo de atributos y dirty checking             |
| `HasRelationships` | Relaciones: hasOne, belongsTo                    |
| `HasTimestamps`    | Manejo de `created_at` y `updated_at`            |
| `HidesAttributes`  | Control de serialización (`toArray`, `toJson`)   |
| `Collection`       | Conjunto de modelos con soporte de JSON y arrays |

---

## ✅ Ejemplo Completo

```php
$user = User::create([
   'name' => 'Fernando',
   'email' => 'nando@demo.com'
]);

$phone = $user->phone()->getResults();
echo $user->toJson();
```

---

¿Deseas que te genere también una **versión en Markdown o HTML** para usarla como parte de la documentación del sistema?
