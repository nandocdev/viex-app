# Documentación Oficial de Phast Framework

Bienvenido a la documentación oficial de Phast, un framework PHP 8.1+ minimalista y de alto rendimiento. Phast está diseñado para desarrolladores que aman la simplicidad, el código limpio y los principios SOLID, y que desean construir aplicaciones web modernas sin el sobrepeso de los frameworks tradicionales.

Nuestra filosofía es proporcionar una base sólida y extensible, dándote el control total sobre tu aplicación.

---

## Tabla de Contenido

1. [Requisitos e Instalación](#1-requisitos-e-instalación)
2. [Estructura de Directorios](#2-estructura-de-directorios)
3. [Configuración](#3-configuración)
4. [El Ciclo de Vida de la Petición](#4-el-ciclo-de-vida-de-la-petición)
5. [Enrutamiento (Routing)](#5-enrutamiento-routing)
   -  [Rutas Básicas](#rutas-básicas)
   -  [Parámetros de Ruta](#parámetros-de-ruta)
   -  [Restricciones con Expresiones Regulares](#restricciones-con-expresiones-regulares)
   -  [Rutas con Nombre](#rutas-con-nombre)
   -  [Grupos de Rutas](#grupos-de-rutas)
6. [Controladores (Controllers)](#6-controladores-controllers)
   -  [Crear un Controlador](#crear-un-controlador)
   -  [Inyección de Dependencias](#inyección-de-dependencias)
7. [Petición y Respuesta (Request & Response)](#7-petición-y-respuesta-request--response)
   -  [El Objeto Request](#el-objeto-request)
   -  [El Objeto Response](#el-objeto-response)
8. [Vistas y Renderizado (Views)](#8-vistas-y-renderizado-views)
   -  [Creando Vistas y Layouts](#creando-vistas-y-layouts)
   -  [Pasando Datos a las Vistas](#pasando-datos-a-las-vistas)
   -  [Usando Parciales](#usando-parciales)
   -  [Helpers de Vista](#helpers-de-vista)
9. [Middleware](#9-middleware)
   -  [Creando un Middleware](#creando-un-middleware)
   -  [Registrando Middleware](#registrando-middleware)
10.   [Base de Datos](#10-base-de-datos)
      -  [Configuración](#configuración-db)
      -  [Ejecutando Consultas](#ejecutando-consultas)
      -  [Transacciones](#transacciones)
11.   [Seguridad](#11-seguridad)
      -  [Protección XSS](#protección-xss)
      -  [Protección CSRF](#protección-csrf)
12.   [Interfaz de Línea de Comandos (CLI)](#12-interfaz-de-línea-de-comandos-cli)

---

## 1. Requisitos e Instalación

### Requisitos

-  PHP 8.1 o superior
-  Composer
-  Servidor web (Nginx, Apache) o el servidor integrado de PHP para desarrollo.

### Instalación

La forma más sencilla de crear un nuevo proyecto Phast es usando Composer.

```bash
composer create-project phast/starter-kit nombre-del-proyecto
cd nombre-del-proyecto
```

Después de la instalación, realiza los siguientes pasos:

1. **Crea tu archivo de entorno:** Copia el archivo de ejemplo.
   ```bash
   cp .env.example .env
   ```
2. **Configura tu entorno:** Abre el archivo `.env` y ajusta las variables, especialmente las de conexión a la base de datos (`DB_*`).
3. **Inicia el servidor de desarrollo:** Phast incluye un script para usar el servidor integrado de PHP.
   ```bash
   php phast serve
   ```
   Tu aplicación estará disponible en `http://localhost:8000`.

## 2. Estructura de Directorios

La estructura de directorios de Phast es simple e intuitiva.

-  `/app`: Contiene el núcleo de tu aplicación, incluyendo Controladores, Middlewares y Vistas.
-  `/config`: Archivos de configuración del framework (base de datos, aplicación, etc.).
-  `/public`: El único directorio accesible desde la web. Contiene el `index.php` (punto de entrada) y tus assets (CSS, JS, imágenes).
-  `/resources`: Contiene plantillas de vista no directamente ligadas a un controlador, como layouts y parciales.
-  `/routes`: Aquí defines todas las rutas de tu aplicación.
-  `/storage`: Directorios para archivos generados por el framework: caché, logs, sesiones. Debe tener permisos de escritura.
-  `/system`: El núcleo del framework Phast. No deberías modificar archivos en este directorio.
-  `/vendor`: Dependencias de Composer.

## 3. Configuración

Toda la configuración específica del entorno se gestiona en el archivo `.env` en la raíz de tu proyecto. Phast utiliza la librería `vlucas/phpdotenv` para cargar estas variables.

**Variables Clave:**

-  `APP_ENV`: El entorno de la aplicación (`local`, `production`).
-  `APP_DEBUG`: Activa/desactiva el modo de depuración (`true`/`false`). En `local`, los errores se mostrarán detalladamente. En `production`, se mostrará una página de error genérica y se registrará el error.
-  `DB_HOST`, `DB_DATABASE`, etc.: Credenciales de la base de datos.

## 4. El Ciclo de Vida de la Petición

Entender el ciclo de vida de una petición en Phast es simple:

1. Una petición llega al `public/index.php`.
2. Se carga el autoloader de Composer.
3. Se crea una instancia de `Phast\System\Core\Application`.
4. La aplicación inicializa el contenedor de dependencias, carga las variables de entorno y registra los _Service Providers_ (que configuran el enrutador, la base de datos, etc.).
5. El `Router` busca una ruta que coincida con la URI y el método HTTP de la petición.
6. Si se encuentra una ruta, la petición pasa a través de la pila de `Middleware` definida para esa ruta.
7. Finalmente, la petición llega al método del `Controller` especificado en la ruta.
8. El controlador procesa la lógica, interactúa con modelos o servicios y devuelve un objeto `Response`.
9. La aplicación envía la respuesta (contenido, headers, código de estado) al cliente.

## 5. Enrutamiento (Routing)

Todas las rutas se definen en el archivo `routes/web.php`. El enrutador de Phast es potente y flexible.

### Rutas Básicas

Puedes definir rutas para diferentes verbos HTTP usando la fachada `Router`.

```php
use Phast\System\Routing\Facades\Router;

// Ruta que devuelve una Closure
Router::get('/', function () {
    return '¡Bienvenido a Phast!';
});

// Ruta que apunta a un método de un controlador
Router::get('/users', 'UserController@index');
Router::post('/users', 'UserController@store');
```

### Parámetros de Ruta

Captura segmentos de la URI para usarlos en tu controlador.

```php
Router::get('/users/{id}', 'UserController@show');
```

Dentro de tu método `show($id)` en `UserController`, el valor del segmento `{id}` estará disponible como argumento.

### Restricciones con Expresiones Regulares

Puedes restringir el formato de un parámetro de ruta.

```php
// El ID solo aceptará números
Router::get('/users/{id:\d+}', 'UserController@show');

// El nombre solo aceptará letras
Router::get('/posts/{name:[a-zA-Z]+}', 'PostController@showByName');
```

### Rutas con Nombre

Asignar un nombre a una ruta facilita la generación de URLs.

```php
Router::get('/users/profile', 'ProfileController@show')->name('profile.show');
```

Para generar la URL a esta ruta, puedes usar el helper `route()`:

```php
// Generará la URL: /users/profile
$url = route('profile.show');

// Para rutas con parámetros
Router::get('/users/{id}', 'UserController@show')->name('users.show');
$url = route('users.show', ['id' => 123]); // Genera: /users/123
```

### Grupos de Rutas

Agrupa rutas que comparten atributos, como un prefijo de URI o middleware.

```php
Router::group(['prefix' => 'api/v1'], function () {
    Router::get('/posts', 'Api\PostController@index');
    Router::get('/posts/{id:\d+}', 'Api\PostController@show');
});

Router::group(['middleware' => ['Auth']], function () {
    Router::get('/dashboard', 'DashboardController@index');
    Router::post('/logout', 'AuthController@logout');
});
```

## 6. Controladores (Controllers)

Los controladores organizan la lógica de manejo de peticiones. Se encuentran en `app/Controllers`.

### Crear un Controlador

Puedes crear un controlador manualmente o usando la CLI:

```bash
php phast make:controller UserController
```

Esto generará un archivo `app/Controllers/UserController.php`.

```php
<?php

namespace Phast\App\Controllers;

use Phast\System\Http\Request;
use Phast\System\Http\Response;

class UserController
{
    public function index(Request $request, Response $response)
    {
        // Lógica para obtener todos los usuarios...
        $users = [['name' => 'John Doe'], ['name' => 'Jane Doe']];
        return $response->json($users);
    }

    public function show(Request $request, Response $response, string $id)
    {
        // Lógica para mostrar el usuario con ID $id...
        return $response->view('users.show', ['userId' => $id]);
    }
}
```

### Inyección de Dependencias

Phast utiliza un contenedor de dependencias que permite inyectar clases en los constructores y métodos de tus controladores.

```php
class UserController
{
    protected MyUserService $userService;

    // El contenedor de Phast inyectará automáticamente una instancia de MyUserService
    public function __construct(MyUserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->input('validated_data');
        $user = $this->userService->create($data);
        // ...
    }
}
```

## 7. Petición y Respuesta (Request & Response)

### El Objeto Request

El objeto `Phast\System\Http\Request` proporciona una forma orientada a objetos de interactuar con la petición HTTP entrante. Se inyecta automáticamente en tus métodos de controlador.

-  **Obtener la URI y el método:**
   ```php
   $path = $request->getPath(); // /users/1
   $method = $request->getMethod(); // GET, POST...
   ```
-  **Obtener datos de entrada (Input):**

   ```php
   // Obtener un solo valor
   $name = $request->input('name');

   // Obtener un valor con un default
   $page = $request->input('page', 1);

   // Obtener todos los datos de entrada
   $allData = $request->getBody();
   ```

-  **Obtener Headers:**
   ```php
   $userAgent = $request->getHeader('User-Agent');
   ```

### El Objeto Response

El objeto `Phast\System\Http\Response` te permite construir y enviar la respuesta.

-  **Respuestas de Texto/HTML:**
   ```php
   return $response->send('<h1>Hola Mundo</h1>', 200);
   ```
-  **Respuestas JSON:**
   ```php
   $data = ['id' => 1, 'name' => 'Phast User'];
   return $response->json($data);
   ```
-  **Renderizar una Vista:**
   ```php
   return $response->view('pages.about', ['team' => $teamMembers]);
   ```
-  **Redirecciones:**

   ```php
   // Redirigir a una URI
   return $response->redirect('/login');

   // Redirigir a una ruta con nombre
   return $response->redirectToRoute('profile.show');
   ```

## 8. Vistas y Renderizado (Views)

Las vistas contienen el HTML de tu aplicación y están separadas de la lógica.

### Creando Vistas y Layouts

-  **Vistas:** Se encuentran en `app/Views`. Suelen corresponder a una acción de un controlador. Ejemplo: `app/Views/home.view.phtml`.
-  **Layouts:** Son plantillas base. Se encuentran en `resources/views/layouts`. Un layout típico contiene el `<html>`, `<head>` y `<body>`, y un marcador `@content` donde se inyectará el contenido de la vista. Ejemplo: `resources/views/layouts/default/index.layout.phtml`.

**Ejemplo de Layout (`default/index.layout.phtml`):**

```html
<!DOCTYPE html>
<html lang="es">
	<head>
		<title><?= $title ?? 'Phast Framework' ?></title>
		<link
			rel="stylesheet"
			href="<?= asset('css/app.css') ?>" />
	</head>
	<body>
		@partial('header')

		<main>@content</main>

		@partial('footer')
	</body>
</html>
```

### Pasando Datos a las Vistas

Pasa un array de datos como segundo argumento al método `view()`.

```php
// En un controlador
return $response->view('home', ['name' => 'Fernando']);
```

Dentro de la vista (`home.view.phtml`), puedes acceder a los datos como variables:

```php
<h1>Bienvenido, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h1>
```

### Usando Parciales

Los parciales son fragmentos de vista reutilizables (headers, footers, sidebars). Se cargan con la directiva `@partial`.

-  Se buscan en `resources/views/partials/`.
-  Ejemplo: `@partial('header')` cargará `resources/views/partials/header.partial.phtml`.

### Helpers de Vista

Phast proporciona helpers para tareas comunes en las vistas:

-  `asset('path/to/file')`: Genera una URL a un archivo en el directorio `public`.
-  `route('name', ['params'])`: Genera una URL a una ruta con nombre.
-  `csrf_field()`: Genera un campo de input oculto con el token CSRF para proteger tus formularios.

## 9. Middleware

El middleware proporciona un mecanismo para filtrar las peticiones HTTP que entran a tu aplicación. Por ejemplo, para verificar si un usuario está autenticado.

### Creando un Middleware

Usa la CLI para generar un nuevo middleware:

```bash
php phast make:middleware AuthMiddleware
```

Esto creará `app/Middleware/AuthMiddleware.php`.

```php
<?php

namespace Phast\App\Middleware;

use Phast\System\Http\Request;
use Phast\System\Http\Response;
use Closure;

class AuthMiddleware
{
    public function handle(Request $request, Response $response, Closure $next)
    {
        if (!is_authenticated()) { // Tu lógica de autenticación aquí
            return $response->redirect('/login');
        }

        // Si el usuario está autenticado, pasa la petición a la siguiente capa
        return $next($request, $response);
    }
}
```

### Registrando Middleware

Aplica el middleware a una ruta o a un grupo en `routes/web.php`.

```php
// Aplicar a una sola ruta
Router::get('/dashboard', 'DashboardController@index')->middleware('Auth');

// Aplicar a un grupo de rutas
Router::group(['middleware' => ['Auth']], function () {
    Router::get('/profile', 'ProfileController@edit');
    Router::post('/profile', 'ProfileController@update');
});
```

_Nota: El nombre del middleware (`'Auth'`) se deriva de la clase (`AuthMiddleware`)._

## 10. Base de Datos

### Configuración {#configuración-db}

Configura tu conexión a la base de datos en el archivo `.env`. Phast soporta MySQL, PostgreSQL y SQLite.

### Ejecutando Consultas

Phast proporciona una capa de abstracción simple sobre PDO. Puedes usar la fachada `DB`.

-  **Select:**

   ```php
   use Phast\System\Database\Facades\DB;

   // Obtiene todos los resultados
   $users = DB::select('SELECT * FROM users WHERE active = ?', [1]);

   // Obtiene un solo resultado
   $user = DB::selectOne('SELECT * FROM users WHERE id = ?', [$id]);
   ```

-  **Insert, Update, Delete:**

   ```php
   DB::insert('INSERT INTO users (name, email) VALUES (?, ?)', ['John', 'john@example.com']);

   $affectedRows = DB::update('UPDATE users SET name = ? WHERE id = ?', ['John Doe', 1]);

   $deletedRows = DB::delete('DELETE FROM users WHERE id = ?', [1]);
   ```

### Transacciones

Para ejecutar un conjunto de operaciones dentro de una transacción, usa el método `transaction`.

```php
DB::transaction(function ($db) {
    $db->update('UPDATE users SET balance = balance - ? WHERE id = ?', [100, 1]);
    $db->update('UPDATE accounts SET balance = balance + ? WHERE id = ?', [100, 5]);
});
```

Si se lanza una excepción dentro de la Closure, la transacción hará un `rollback` automáticamente. Si no, hará un `commit` al finalizar.

## 11. Seguridad

### Protección XSS

Phast no sanitiza los datos de entrada por defecto para no corromperlos. Tu responsabilidad es escapar los datos en el punto de salida, es decir, en tus vistas. Usa la función `htmlspecialchars`.

```php
// Incorrecto, vulnerable a XSS
<h1><?= $untrusted_comment ?></h1>

// Correcto, protegido contra XSS
<h1><?= htmlspecialchars($untrusted_comment, ENT_QUOTES, 'UTF-8') ?></h1>
```

### Protección CSRF

Phast proporciona protección contra ataques de falsificación de peticiones en sitios cruzados (CSRF) de forma automática para las rutas `POST`, `PUT`, `PATCH`, y `DELETE`.

Para proteger tus formularios, simplemente añade el helper `csrf_field()` dentro de tu etiqueta `<form>`.

```html
<form
	method="POST"
	action="/profile">
	<?= csrf_field() ?>

	<!-- ... tus otros campos de formulario ... -->

	<button type="submit">Guardar</button>
</form>
```

Esto generará un campo `_token` oculto. El middleware `VerifyCsrfToken` se encargará de validar la petición.

## 12. Interfaz de Línea de Comandos (CLI)

Phast incluye una herramienta de CLI llamada `phast` para ayudarte en el desarrollo.

-  **Iniciar el servidor de desarrollo:**
   ```bash
   php phast serve
   ```
-  **Listar todas las rutas:**
   ```bash
   php phast route:list
   ```
-  **Gestionar la caché de rutas (para producción):**
   ```bash
   php phast route:cache  // Crea el archivo de caché de rutas
   php phast route:clear  // Elimina el archivo de caché de rutas
   ```
-  **Generar clases (scaffolding):**
   ```bash
   php phast make:controller NombreController
   php phast make:middleware NombreMiddleware
   ```
