# Mejoras del Módulo Database

## 🚀 Nuevas Características Implementadas

### 1. **Sistema de Relaciones Avanzadas**

El módulo Database ahora incluye un sistema completo de relaciones similar a Laravel:

```php
class User extends Model
{
    protected $table = 'users';

    // Relación uno a uno
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // Relación uno a muchos
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Relación pertenece a
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relación muchos a muchos
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}

// Uso de relaciones
$user = User::find(1);
$profile = $user->profile; // Carga automática
$posts = $user->posts; // Array de posts
$role = $user->role; // Objeto role
$permissions = $user->permissions; // Array de permissions

// Carga anticipada
$users = User::with(['profile', 'posts', 'role'])->get();
```

### 2. **Sistema de Validación Integrada**

Validación automática antes de guardar modelos:

```php
class User extends Model
{
    protected $table = 'users';

    // Reglas de validación
    protected $validationRules = [
        'name' => 'required|min:3|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'role_id' => 'required|exists:roles,id'
    ];

    // Mensajes personalizados
    protected $validationMessages = [
        'name.required' => 'El nombre es obligatorio',
        'email.unique' => 'Este email ya está registrado'
    ];
}

// Uso
$user = new User([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => '123456'
]);

if ($user->save()) {
    echo "Usuario guardado exitosamente";
} else {
    $errors = $user->getValidationErrors();
    print_r($errors);
}
```

### 3. **Sistema de Cache Inteligente**

Cache automático de consultas para mejorar el rendimiento:

```php
class User extends Model
{
    protected $table = 'users';
    protected $useCache = true;
    protected $cacheTtl = 1800; // 30 minutos
}

// Cache automático en consultas
$users = User::all(); // Se cachea automáticamente
$user = User::find(1); // Se cachea automáticamente

// Deshabilitar cache para consultas específicas
$user = new User();
$user->useCache(false);
$recentUsers = $user->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-1 hour')))->get();

// Estadísticas del cache
$stats = ModelCache::getStats();
print_r($stats);
```

### 4. **Sistema de Eventos**

Eventos automáticos cuando se crean, actualizan o eliminan modelos:

```php
class User extends Model
{
    protected $table = 'users';
    protected $fireEvents = true;

    // Eventos personalizados
    protected function fireEvent($event): void
    {
        if ($event instanceof ModelCreated) {
            // Enviar email de bienvenida
            $this->sendWelcomeEmail();
        }

        if ($event instanceof ModelUpdated) {
            // Registrar cambios en log
            $this->logChanges($event->getOriginal());
        }

        if ($event instanceof ModelDeleted) {
            // Limpiar archivos asociados
            $this->cleanupFiles();
        }
    }

    private function sendWelcomeEmail(): void
    {
        // Lógica para enviar email
    }

    private function logChanges(array $original): void
    {
        // Lógica para registrar cambios
    }

    private function cleanupFiles(): void
    {
        // Lógica para limpiar archivos
    }
}
```

### 5. **Mutadores y Accesores**

Transformación automática de datos:

```php
class User extends Model
{
    protected $table = 'users';

    // Accesor - se ejecuta al obtener el atributo
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

    // Mutador - se ejecuta al establecer el atributo
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Accesor para nombre completo
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Mutador para hash de contraseña
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
}

// Uso
$user = new User();
$user->name = 'john doe'; // Se convierte a 'John Doe'
$user->email = 'JOHN@EXAMPLE.COM'; // Se convierte a 'john@example.com'
$user->password = '123456'; // Se hashea automáticamente

echo $user->name; // 'John Doe'
echo $user->full_name; // 'John Doe' (atributo calculado)
```

### 6. **Métodos de Consulta Mejorados**

```php
class User extends Model
{
    protected $table = 'users';

    // Scope local
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role_id', $role);
    }
}

// Uso de scopes
$activeUsers = User::active()->get();
$adminUsers = User::active()->byRole(1)->get();

// Consultas con cache
$users = User::with(['profile', 'role'])
    ->active()
    ->orderBy('name')
    ->get();
```

## 🔧 Configuración Avanzada

### Configuración Global

```php
// En el ServiceProvider
class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Configurar cache global
        ModelCache::setDefaultTtl(3600);

        // Configurar eventos globales
        EventManager::listen(ModelCreated::class, function($event) {
            // Lógica global para creación
        });
    }
}
```

### Optimización de Rendimiento

```php
// Deshabilitar cache para operaciones masivas
User::useCache(false)->where('status', 'inactive')->delete();

// Cache personalizado para consultas complejas
$key = 'users_with_stats_' . date('Y-m-d');
$users = ModelCache::remember($key, function() {
    return User::with(['posts', 'comments'])
        ->select('users.*')
        ->selectRaw('COUNT(posts.id) as post_count')
        ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
        ->groupBy('users.id')
        ->get();
}, 3600);
```

## 📊 Beneficios de las Mejoras

1. **Rendimiento**: Cache automático reduce consultas a la base de datos
2. **Seguridad**: Validación automática previene datos inválidos
3. **Mantenibilidad**: Relaciones claras y eventos organizados
4. **Flexibilidad**: Mutadores y accesores para transformación de datos
5. **Escalabilidad**: Sistema modular y extensible

## 🚀 Próximas Mejoras Sugeridas

1. **Soft Deletes**: Eliminación suave con timestamps
2. **Polymorphic Relations**: Relaciones polimórficas
3. **Query Scopes**: Scopes más avanzados
4. **Database Migrations**: Sistema de migraciones
5. **Seeding**: Sistema de datos de prueba
6. **Connection Pooling**: Pool de conexiones
7. **Query Logging**: Log de consultas para debugging
8. **Database Transactions**: Transacciones automáticas
