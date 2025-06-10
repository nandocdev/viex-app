# Documentación del Sistema de Vistas Phast

El sistema de vistas de Phast proporciona una forma flexible y potente de renderizar la interfaz de usuario de tu aplicación, separando la lógica de la presentación. Utiliza un enfoque basado en plantillas PHP simples (`.phtml` o `.php`) y permite la reutilización de código a través de layouts, vistas y parciales.

## Conceptos Clave

Antes de empezar, familiarízate con los siguientes términos:

-  **Vista (View):** Un archivo de plantilla (`.view.phtml`) que contiene el código HTML y PHP específico para una parte particular de tu interfaz de usuario (ej. la página de un producto, un formulario de login).
-  **Layout:** Un archivo de plantilla (`.layout.phtml`) que define la estructura HTML general de tu aplicación (cabecera, pie de página, navegación). Las vistas se "inyectan" dentro de un layout en un marcador `@content`.
-  **Parcial (Partial):** Pequeños fragmentos de plantillas (`.partial.phtml`) que se pueden reutilizar en diferentes vistas o layouts (ej. un widget de perfil de usuario, un componente de alerta).
-  **Motor de Vistas (View Engine):** La lógica subyacente que toma las plantillas y los datos y produce el HTML final. Actualmente, Phast utiliza un motor PHP simple.
-  **DataHandler:** La clase que gestiona los datos que se hacen disponibles en las plantillas.

## Estructura de Directorios

Para que el sistema de vistas funcione correctamente, tu proyecto debe seguir una estructura de directorios estándar para las plantillas.

```
.
├── app/
│   └── views/                  # Vistas específicas de módulos/controladores
│       ├── Welcome/            # Un directorio de ejemplo para un módulo 'Welcome'
│       │   ├── home.view.phtml # Archivo de vista para la página de inicio
│       │   └── partials/       # Parciales específicos de la vista 'home'
│       │       └── my_partial.partial.phtml
│       └── Dashboard/
│           └── index.view.phtml
├── resources/
│   └── views/
│       ├── layouts/            # Plantillas de layout globales
│       │   └── default/
│       │       └── index.layout.phtml # Layout por defecto
│       │   └── auth/
│       │       └── index.layout.phtml # Layout para autenticación
│       └── partials/           # Parciales globales (reutilizables en toda la aplicación)
│           └── global_header.partial.phtml
│           └── sidebar.partial.phtml
└── src/
    └── Phast/
        └── System/
            └── View/           # El código fuente del sistema de vistas de Phast
                ├── Contracts/
                ├── Core/
                ├── Engines/
                ├── Render.php
                └── View.php
```

**Nota:** Las rutas exactas para `layouts`, `views` y `partials` son configurables en tu archivo de configuración (`Config`).

## Cómo Renderizar Vistas

Para renderizar una vista, interactuarás principalmente con la clase `Phast\System\View\Render`.

### 1. Instancia del Renderer

Primero, asegúrate de tener una instancia del `Render` en tu controlador o donde necesites renderizar una vista. Esto generalmente se hace a través de la inyección de dependencias si usas un Contenedor de Inversión de Control (IoC).

```php
<?php

namespace Phast\App\Controllers;

use Phast\System\View\Render;
use Phast\System\View\View; // Importa la clase View

class HomeController
{
    private Render $renderer;

    public function __construct(Render $renderer) // Inyectar el Render
    {
        $this->renderer = $renderer;
    }

    public function index()
    {
        // ... Lógica del controlador ...

        // Datos que quieres pasar a la vista
        $data = [
            'title' => 'Página de Inicio',
            'welcomeMessage' => '¡Bienvenido a nuestra aplicación Phast!',
            'user' => [
                'name' => 'Jane Doe',
                'role' => 'Admin'
            ]
        ];

        // Crear una instancia de la vista
        $view = new View(
            'home',        // Nombre de la vista (home.view.phtml)
            'Welcome',     // Subdirectorio de la vista (app/views/Welcome/)
            'default',     // Nombre del layout (resources/views/layouts/default/index.layout.phtml)
            $data          // Datos para la vista
        );

        // Renderizar la vista y obtener el HTML
        $htmlOutput = $this->renderer->render($view);

        // Enviar la respuesta (ej. en un framework, esto sería parte de un objeto Response)
        echo $htmlOutput;
    }
}
```

### 2. Parámetros del objeto `View`

La clase `Phast\System\View\View` toma los siguientes parámetros en su constructor:

-  `$viewName` (string): El nombre del archivo de la vista (ej. `'home'` para `home.view.phtml`).
-  `$viewSubPath` (string, opcional): La ruta de un subdirectorio dentro de `app/views/` (ej. `'Welcome'` para `app/views/Welcome/home.view.phtml`). Si tu vista está directamente en `app/views/`, déjalo vacío o en `''`.
-  `$layoutName` (string, opcional): El nombre del layout a usar (ej. `'default'` para `resources/views/layouts/default/index.layout.phtml`). Si no se especifica, el sistema intentará usar un layout por defecto (configurable).
-  `$data` (array, opcional): Un array asociativo de datos que estarán disponibles en la vista y el layout.

## Dentro de los Archivos de Plantilla

Las plantillas son archivos PHP simples que pueden contener código HTML y PHP.

### Acceso a los Datos

Todos los datos pasados a la vista a través del array `$data` estarán disponibles como variables locales dentro de tus plantillas.

```php
<h1><?= $title ?></h1>
<p><?= $welcomeMessage ?></p>

<?php if (isset($user)): ?>
    <p>¡Hola, <?= $user['name'] ?>! Tu rol es: <?= $user['role'] ?></p>
<?php else: ?>
    <p>Usuario no logueado.</p>
<?php endif; ?>
```

### Inyección de Contenido de Vista en Layouts (`@content`)

Los layouts utilizan un marcador especial `@content` para indicar dónde se debe inyectar el contenido de la vista principal.

```html
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8" />
		<title><?= $title ?? 'Mi Aplicación Phast' ?></title>
	</head>
	<body>
		<header>
			<h1>Mi Aplicación Phast</h1>
			<nav>...</nav>
		</header>

		<main>@content</main>

		<footer>
			<p>&copy; 2025 Todos los derechos reservados.</p>
		</footer>
	</body>
</html>
```

### Incluir Parciales (`@partial()`)

Puedes incluir parciales en tus layouts o vistas utilizando la directiva `@partial()`. Los parciales pueden ser archivos de plantilla que contienen HTML reutilizable.

La sintaxis es `@partial('nombre_del_parcial')`.

**Ejemplo:**

```php
<h2>Contenido Principal de la Página</h2>
@partial('my_component') <p>Más contenido...</p>
```

**Ubicación de Parciales:**

El sistema de vistas buscará los parciales en el siguiente orden:

1. **Parciales relativos a la vista o layout actual:** Si llamas `@partial('nombre')` desde `app/views/Welcome/home.view.phtml`, el sistema buscará `app/views/Welcome/partials/nombre.partial.phtml`.
2. **Parciales globales:** Si no se encuentra un parcial relativo, el sistema buscará en el directorio configurado para parciales globales (ej. `resources/views/partials/nombre.partial.phtml`).

Esto permite tener parciales específicos de un módulo/vista y también parciales que se pueden usar en toda la aplicación.

**Ejemplo de un parcial:**

```php
<div class="card">
    <h3>Bienvenido, <?= $user['name'] ?>!</h3>
    <p>Estos son tus detalles: Email: <?= $user['email'] ?? 'N/A' ?></p>
</div>
```

**Nota:** Los parciales comparten el mismo ámbito de datos que la vista o layout que los incluye. Esto significa que tienen acceso a las mismas variables pasadas a la vista principal.

## Configuración de Rutas

Las rutas de tus plantillas se configuran a través de tu clase `Config`. Asegúrate de que los siguientes valores estén definidos:

```php
// Ejemplo de configuración (puede estar en config/app.php o similar)
return [
    'path' => [
        'root' => '/ruta/absoluta/a/tu/proyecto',
        'layouts' => '/ruta/absoluta/a/tu/proyecto/resources/views/layouts',
        'views' => '/ruta/absoluta/a/tu/proyecto/app/views',
        'partials' => '/ruta/absoluta/a/tu/proyecto/resources/views/partials', // Opcional para parciales globales
    ],
    // ...
];
```

Asegúrate de que estas rutas sean correctas y que los directorios existan y sean legibles por el servidor web.

## Manejo de Errores

Si un archivo de plantilla (vista, layout o parcial) no se encuentra, el sistema lanzará una `\InvalidArgumentException`. En un entorno de desarrollo, esto mostrará un error detallado. En producción, deberías tener un manejador de excepciones global que capture estas excepciones y muestre una página de error amigable.

Cuando un parcial no se encuentra, el sistema de vistas insertará un comentario HTML en el lugar del parcial para facilitar la depuración, por ejemplo: ``.
