# Documentación del Enrutador de Phast

El enrutador de Phast es responsable de procesar las peticiones HTTP entrantes y dirigirlas a la lógica apropiada de la aplicación, como un método de un controlador. Es un componente potente y expresivo que te permite definir rutas complejas de manera limpia y organizada.

Toda la definición de rutas se realiza en el archivo `routes/web.php`.

## 1. Definición Básica de Rutas

Para definir una ruta, utiliza los métodos estáticos de la fachada `Router`. Phast ofrece métodos para todos los verbos HTTP comunes.

```php
use System\Routing\Facades\Router;

// Apunta a una Closure (función anónima)
Router::get('/', function () {
    return new \System\Http\Response('¡Bienvenido a Phast!');
});

// Apunta a un método de un controlador
Router::get('/users', 'UserController@index');
Router::post('/users', 'UserController@store');
```

Los métodos disponibles son:

-  `Router::get(string $uri, mixed $action)`
-  `Router::post(string $uri, mixed $action)`
-  `Router::put(string $uri, mixed $action)`
-  `Router::patch(string $uri, mixed $action)`
-  `Router::delete(string $uri, mixed $action)`

## 2. Parámetros de Ruta

### Parámetros Requeridos

Captura segmentos de la URI definiéndolos entre llaves `{}`. Estos parámetros se inyectarán en el método de tu controlador.

```php
Router::get('/posts/{id}', 'PostController@show');
```

```php
// En app/Controllers/PostController.php
class PostController
{
    // Phast intentará convertir el tipo del parámetro (type-hinting)
    public function show(int $id)
    {
        // Si la URL es /posts/42, $id será el entero 42.
        $post = Post::find($id);
        // ...
    }
}
```

### Restricciones de Expresión Regular

Puedes restringir el formato de un parámetro de ruta añadiendo una expresión regular después del nombre del parámetro, separada por dos puntos `:`.

```php
// El parámetro {id} solo aceptará dígitos numéricos.
Router::get('/users/{id:\d+}', 'UserController@show');

// El parámetro {slug} solo aceptará letras, números, guiones y guiones bajos.
Router::get('/blog/{slug:[\w-]+}', 'BlogController@show');
```

Si una URI no cumple con la restricción, la ruta no coincidirá y Phast continuará buscando otras rutas.

## 3. Rutas con Nombre

Nombrar las rutas es la mejor práctica para generar URLs en tu aplicación. Te permite cambiar la URI de una ruta sin tener que actualizar cada enlace que apunta a ella.

Para nombrar una ruta, encadena el método `name()`:

```php
Router::get('/users/profile', 'ProfileController@edit')->name('profile.edit');
```

### Generación de URLs

Para generar una URL para una ruta con nombre, utiliza el método `Router::route()`.

```php
// Genera una URL relativa: /users/profile
$url = Router::route('profile.edit');

// Generar URL para una ruta con parámetros
Router::get('/users/{id}', 'UserController@show')->name('users.show');
$url = Router::route('users.show', ['id' => 123]); // Genera: /users/123

// Generar una URL absoluta (requiere APP_URL en el .env)
$absoluteUrl = Router::route('home', [], true); // Genera: http://tu-app.com/
```

Puedes usar `Router::route()` en tus controladores o pasarlo a tus vistas para generar enlaces dinámicos.

## 4. Grupos de Rutas

Los grupos te permiten aplicar atributos comunes, como prefijos de URI o middleware, a un conjunto de rutas, evitando la repetición.

### Prefijos de URI

Usa el atributo `prefix` para anteponer un segmento de URI a todas las rutas del grupo.

```php
Router::group(['prefix' => '/api/v1'], function () {
    // Responde a /api/v1/products
    Router::get('/products', 'Api\ProductController@index');

    // Responde a /api/v1/orders
    Router::get('/orders', 'Api\OrderController@index');
});
```

### Middleware en Grupos

Usa el atributo `middleware` para aplicar uno o más middlewares a todas las rutas del grupo.

```php
Router::group(['middleware' => 'Auth'], function () {
    Router::get('/dashboard', 'DashboardController@index');

    // Aplicar un middleware adicional a una ruta específica dentro del grupo
    Router::post('/posts', 'PostController@store')->middleware('AdminOnly');
});
```

Los grupos pueden anidarse, y los atributos como prefijos y middlewares se acumularán de forma inteligente.

## 5. Middleware

El middleware proporciona un mecanismo para filtrar o actuar sobre las peticiones HTTP antes o después de que lleguen a la lógica del controlador.

### Asignar Middleware

Para asignar un middleware a una ruta, encadena el método `middleware()`.

```php
Router::get('/settings', 'SettingsController@index')->middleware('Auth');

// Asignar múltiples middlewares
Router::post('/admin/data', 'AdminController@update')->middleware(['Auth', 'AdminOnly']);
```

### Crear un Middleware

Los middlewares se ubican en `app/Middleware` y deben implementar un método `handle`. Este método recibe la `Request` y una `Closure $next`, que representa la siguiente capa en la pila.

```php
// app/Middleware/AdminOnlyMiddleware.php
namespace App\Middleware;

use Closure;
use System\Http\Request;
use System\Http\Response;

class AdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->isAdmin()) { // Suponiendo que tienes un método de usuario
            // Si el usuario no es admin, detenemos la petición.
            return new Response('Access Denied.', 403);
        }

        // Si el usuario es admin, pasamos la petición a la siguiente capa.
        return $next($request);
    }
}
```

## 6. Caché de Rutas (Optimización para Producción)

En producción, para maximizar el rendimiento, puedes cachear la definición de tus rutas. Esto evita que Phast tenga que leer y procesar todos tus archivos de rutas en cada petición.

### Habilitar la Caché

En tu archivo de entorno `.env`, establece la siguiente variable a `true`:

```env
ROUTE_CACHE_ENABLED=true
```

Con la caché habilitada, Phast creará un archivo optimizado en `storage/cache/routes.php`. En las siguientes peticiones, leerá directamente de este archivo.

### Limpiar la Caché

Cada vez que modifiques tus rutas (en `routes/web.php`), **debes limpiar la caché** para que los cambios se apliquen.

Puedes hacerlo manualmente eliminando el archivo `storage/cache/routes.php`. Se recomienda crear un script o comando para automatizar esta tarea.

```bash
rm storage/cache/routes.php
```

**Importante:** La caché de rutas debe estar **deshabilitada** durante el desarrollo para ver los cambios en tus rutas al instante.

---
