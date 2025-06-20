¡Excelente! Aquí tienes la propuesta final completa y detallada para una capa de base de datos robusta en Phast. Esta propuesta consolida todo lo que hemos discutido, presentando una arquitectura cohesiva para el Query Builder y el ORM básico, incluyendo las funcionalidades avanzadas.

---

## **Propuesta Final: Arquitectura de Base de Datos para Phast Framework**

### **Filosofía de Diseño**

El objetivo es crear una capa de abstracción de base de datos que sea:

-  **Expresiva y Fluida:** Permite a los desarrolladores escribir consultas complejas de manera intuitiva y legible.
-  **Segura por Defecto:** Utiliza consultas preparadas en todo momento para eliminar el riesgo de inyección SQL.
-  **Desacoplada y Extensible:** Se divide en dos capas claras (Query Builder y ORM) que pueden usarse de forma independiente y son fáciles de extender.
-  **Eficiente:** Realiza las operaciones de forma lógica, evitando trabajo innecesario.

### **1. Estructura de Directorios Final**

Esta estructura organiza claramente cada componente de la capa de base de datos.

```
.
├── app
│   └── Modules
│       └── Users
│           └── Models
│               └── User.php           # Modelo de la aplicación
└── system
    └── Database
        ├── Connection.php             # Gestiona la conexión PDO (Existente)
        ├── DB.php                     # Fachada estática para iniciar consultas (Refactorizado)
        ├── Model.php                  # Clase base del ORM (Nuevo)
        └── Query
            ├── Builder.php            # El Query Builder principal (Nuevo)
            └── Grammars
                ├── Grammar.php        # Interfaz/Clase base para la gramática SQL (Opcional, avanzado)
                └── MySqlGrammar.php   # Lógica específica de compilación para MySQL (Opcional, avanzado)
```

_Nota: El directorio `Grammars` es una mejora opcional para soportar diferentes dialectos de SQL (MySQL, PostgreSQL, etc.) de forma limpia. Por ahora, la lógica de compilación puede vivir directamente en el `Builder.php`._

---

### **2. Componentes y Flujo de Trabajo**

#### **Componente 1: `DB.php` (La Fachada)**

-  **Responsabilidad:** Ser el único punto de entrada público y estático para interactuar con la base de datos.
-  **Métodos Clave:**
   -  `public static function table(string $name): Builder`: Inicia una consulta en una tabla específica. Crea y devuelve una nueva instancia del `Query\Builder`.
   -  `public static function select(string $query, array $bindings = []): array`: Ejecuta una consulta SQL cruda de tipo SELECT.
   -  `public static function statement(string $query, array $bindings = []): bool`: Ejecuta una consulta cruda que no devuelve resultados (UPDATE, DELETE, etc.).
   -  `public static function transaction(Closure $callback)`: Ejecuta un conjunto de operaciones dentro de una transacción de base de datos. Hace `commit` si todo va bien, o `rollback` si se lanza una excepción.

#### **Componente 2: `Query\Builder.php` (El Corazón)**

-  **Responsabilidad:** Construir y ejecutar consultas SQL de forma programática.
-  **Propiedades Internas:**

   -  `protected PDO $pdo`: La instancia de la conexión.
   -  `protected string $from`: La tabla base.
   -  `protected array $columns = ['*']`: Columnas a seleccionar.
   -  `protected array $joins = []`: Cláusulas JOIN.
   -  `protected array $wheres = []`: Condiciones WHERE.
   -  `protected array $groups = []`: Cláusulas GROUP BY.
   -  `protected array $havings = []`: Condiciones HAVING.
   -  `protected array $orders = []`: Cláusulas ORDER BY.
   -  `protected ?int $limit`: Límite de resultados.
   -  `protected ?int $offset`: Desplazamiento de resultados.
   -  `protected array $bindings = ['where' => [], 'having' => []]`: Un array estructurado para almacenar los bindings de forma segura.

-  **Métodos Fluidos (Devuelven `$this`):**

   -  `select(string...|array $columns)`: Define las columnas.
   -  `addSelect(string $column)`: Añade una columna a la selección existente.
   -  `selectRaw(string $expression)`: Permite expresiones crudas en el SELECT.
   -  `from(string $table)`: Define la tabla.
   -  `join()`, `leftJoin()`, `rightJoin()`: Añaden cláusulas JOIN.
   -  `where(string $column, string $operator, mixed $value)`: Añade una condición WHERE.
   -  `orWhere()`: Añade una condición `OR WHERE`.
   -  `whereIn(string $column, array $values)`: Añade una condición `WHERE IN (...)`.
   -  `whereNull(string $column)`: Añade una condición `WHERE column IS NULL`.
   -  `groupBy(string...|array $columns)`: Añade columnas al GROUP BY.
   -  `having(string $column, string $operator, mixed $value)`: Añade una condición HAVING.
   -  `orderBy(string $column, string $direction = 'ASC')`: Define el orden.
   -  `latest(string $column = 'created_at')`: Atajo para `orderBy($column, 'DESC')`.
   -  `oldest(string $column = 'created_at')`: Atajo para `orderBy($column, 'ASC')`.
   -  `limit(int $value)` y `offset(int $value)`: Definen la paginación manual.

-  **Métodos Terminales (Ejecutan la consulta):**
   -  `get(): array`: Ejecuta un SELECT y devuelve un array de objetos (`stdClass`).
   -  `first(): ?stdClass`: Ejecuta un SELECT con `LIMIT 1` y devuelve un solo objeto o `null`.
   -  `find(int|string $id, string $primaryKey = 'id')`: Atajo para `where($primaryKey, '=', $id)->first()`.
   -  `value(string $column)`: Obtiene el valor de una sola columna del primer resultado.
   -  `count()`: Ejecuta un `COUNT(*)` y devuelve el número de filas.
   -  `sum()`, `avg()`, `min()`, `max()`: Ejecutan funciones de agregación.
   -  `exists()`: Devuelve `true` si al menos un registro coincide con la consulta.
   -  `insert(array $data): bool`: Ejecuta un INSERT.
   -  `insertGetId(array $data): int|string`: Ejecuta un INSERT y devuelve el ID del nuevo registro.
   -  `update(array $data): int`: Ejecuta un UPDATE y devuelve el número de filas afectadas.
   -  `delete(): int`: Ejecuta un DELETE y devuelve el número de filas afectadas.
   -  `paginate(int $perPage = 15, int $page = 1): array`: Pagina los resultados y devuelve una estructura de datos completa para la paginación.

#### **Componente 3: `Model.php` (La Abstracción ORM)**

-  **Responsabilidad:** Mapear una tabla de la base de datos a un objeto PHP, proporcionando una API elegante para operaciones CRUD y consultas. Actúa como una capa sobre el `Query Builder`.
-  **Declaración:** `abstract class Model`
-  **Propiedades Protegidas Configurables:**

   -  `protected string $table`: Nombre de la tabla. Si no se define, se infiere del nombre de la clase (ej. `User` -> `users`).
   -  `protected string $primaryKey = 'id'`: Nombre de la clave primaria.
   -  `protected bool $timestamps = true`: Indica si el modelo debe gestionar automáticamente las columnas `created_at` y `updated_at`.
   -  `protected array $fillable = []`: Una "lista blanca" de atributos que se pueden asignar masivamente (`create`, `fill`). Es una medida de seguridad crucial.
   -  `protected array $guarded = ['id']`: Una "lista negra" de atributos que no se pueden asignar masivamente. Se usa como alternativa a `$fillable`.
   -  `protected array $attributes = []`: Almacena los datos de la fila del registro actual.

-  **Métodos Mágicos:**

   -  `__get(string $key)` y `__set(string $key, $value)`: Para acceder a los atributos del modelo como si fueran propiedades públicas del objeto (ej. `$user->name`).

-  **Métodos Estáticos (Punto de entrada para consultas):**

   -  `all(): array`: Devuelve una colección de todos los modelos. (Ej. `User::all()`).
   -  `find(int|string $id): ?static`: Busca un modelo por su clave primaria. (Ej. `User::find(1)`).
   -  `findOrFail(int|string $id): static`: Igual que `find`, pero lanza una excepción si no se encuentra el modelo.
   -  `create(array $attributes): static`: Crea un nuevo registro en la base de datos y devuelve una instancia del modelo. (Ej. `User::create([...])`).
   -  `where(...)`, `join(...)`, `orderBy(...)`, etc.: **Todos los métodos fluidos del `Builder` se exponen estáticamente en el modelo.** Esto se logra con un método mágico `__callStatic`. Cuando llamas a `User::where(...)`, el `Model` internamente crea una instancia del `Builder` para la tabla `users` y delega la llamada.

-  **Métodos de Instancia (Operaciones sobre un registro cargado):**
   -  `save(): bool`: Guarda el estado actual del modelo en la base de datos (hace un `INSERT` si es nuevo, o un `UPDATE` si ya existe).
   -  `update(array $attributes): bool`: Actualiza el modelo con nuevos atributos y lo guarda.
   -  `delete(): bool`: Elimina el registro de la base de datos.
   -  `fill(array $attributes)`: Rellena el modelo con un array de atributos, respetando la propiedad `$fillable`.

#### **Componente 4: `app/.../Models/User.php` (El Modelo de Aplicación)**

-  **Responsabilidad:** Representar una entidad de la aplicación.
-  **Lógica:**

   ```php
   <?php
   namespace Phast\App\Modules\Users\Models;

   use Phast\System\Database\Model;

   class User extends Model {
       // Opcional: el framework puede inferir 'users' del nombre de la clase.
       protected string $table = 'users';

       // ¡Muy importante para la seguridad de asignación masiva!
       protected array $fillable = [
           'name',
           'email',
           'password',
       ];

       // Opcional: Atributos que nunca deben ser mostrados al convertir a array/json.
       protected array $hidden = [
           'password',
           'remember_token',
       ];

       // Aquí se definirían las relaciones en el futuro (ej. posts(), roles(), etc.)
   }
   ```

---

### **Flujo de Trabajo del Desarrollador (Ejemplos)**

**Usando el Query Builder:**

```php
// Obtener todos los usuarios activos, ordenados por nombre
$users = DB::table('users')->where('status', '=', 'active')->orderBy('name')->get();

// Contar cuántos artículos tiene un usuario
$postCount = DB::table('posts')->where('user_id', '=', 123)->count();
```

**Usando el ORM:**

```php
// Encontrar un usuario
$user = User::find(1);
echo "Hola, " . $user->name;

// Crear un nuevo usuario de forma segura
$newUser = User::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT),
]);

// Actualizar un usuario
$user = User::find(1);
$user->name = 'Jane Smith';
$user->save();

// Consultas complejas con una sintaxis elegante
$activeAdmins = User::where('status', 'active')
    ->where('is_admin', true)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();
```

Esta propuesta final crea un sistema de base de datos que es a la vez potente para tareas complejas y elegante para operaciones simples, proporcionando una experiencia de desarrollo de primer nivel.
