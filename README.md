<p align="center">
  <img src="URL_A_TU_LOGO_AQUI" alt="Phast Framework Logo" width="150"/>
</p>

<h1 align="center">Phast Framework</h1>

<p align="center">
  <strong>Un framework PHP moderno, minimalista y ultrarrápido para el desarrollo web.</strong><br>
  Diseñado para aprovechar al máximo PHP 8+, Phast ofrece una base sólida, eficiente y elegante para construir aplicaciones web de alto rendimiento con un enfoque en la calidad del código y la mantenibilidad.
</p>

<p align="center">
  <a href="https://github.com/tu-usuario/phast-framework/blob/main/LICENSE" target="_blank"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="Licencia MIT"></a>
  <a href="https://www.php.net/releases/8.1" target="_blank"><img src="https://img.shields.io/badge/PHP-8.1%2B-blue.svg" alt="Versión de PHP"></a>
  <a href="#"><img src="https://img.shields.io/badge/status-en%20desarrollo-orange.svg" alt="Estado del proyecto"></a>
</p>

---

## 🚀 Filosofía

Phast nace de la convicción de que el desarrollo web moderno puede ser simple, performante y elegante sin sacrificar las buenas prácticas. No busca ser un framework monolítico "todo en uno", sino que se enfoca en proporcionar las herramientas esenciales y una arquitectura limpia. Esto te permite construir aplicaciones de forma rápida, eficiente y escalable, manteniendo siempre el control total sobre tu proyecto.

-  **Moderno:** Aprovecha al máximo las características más recientes de PHP 8+, incluyendo el tipado estricto, propiedades de constructor y atributos, para un código más robusto y expresivo.
-  **Ligero:** Un núcleo mínimo con dependencias cuidadosamente seleccionadas, garantizando una huella de memoria reducida y un tiempo de arranque (bootstrapping) excepcionalmente veloz.
-  **Desacoplado:** Construido sobre un potente Contenedor de Inyección de Dependencias (DI), promueve la creación de un código modular, fácilmente probable y adaptable a los cambios.
-  **Extensible:** Diseñado con puntos de extensión claros, facilitando la integración de tus propias librerías, componentes o paquetes de terceros.

## ✨ Características Principales

Phast te equipa con funcionalidades robustas para manejar los desafíos del desarrollo web:

-  **Enrutador HTTP Inteligente:**
   -  Soporte completo para verbos HTTP (GET, POST, PUT, DELETE, etc.).
   -  Manejo de parámetros de ruta dinámicos y expresiones regulares.
   -  Grupos de rutas con prefijos y middlewares comunes.
   -  Rutas con nombre para una generación de URLs sencilla y robusta.
   -  Sistema de caché de rutas optimizado para un rendimiento superior en entornos de producción.
-  **Contenedor de Inyección de Dependencias (DI):**
   -  Gestión centralizada de los servicios de tu aplicación.
   -  Resolución automática de dependencias (auto-wiring) para una inyección de dependencias sin esfuerzo.
   -  Soporte para singletons y resolución dinámica.
-  **ORM Básico (Active Record):**
   -  Una capa de abstracción de base de datos intuitiva que simplifica las interacciones con tu BBDD.
   -  Facilita la manipulación de registros y la ejecución de consultas.
-  **Sistema de Vistas Flexible:**
   -  Motor de plantillas simple y eficiente, basado en PHP puro.
   -  Soporte robusto para layouts (`@content`) para definir la estructura común de tu sitio.
   -  Inclusión de vistas parciales (`@partial`) para reutilizar componentes de interfaz.
-  **Gestor de Conexiones a BBDD Multiples:**
   -  Conéctate a diferentes motores de base de datos (MySQL, PostgreSQL, SQLite) de forma transparente y gestiona múltiples conexiones simultáneamente.
-  **Configuración Basada en Entorno:**
   -  Carga de configuración segura y flexible a través de archivos `.env`, ideal para gestionar configuraciones por entorno (desarrollo, producción).
-  **Middleware Pipeline:**
   -  Procesa las solicitudes HTTP de forma estructurada a través de un sistema de "capas" (el patrón cebolla), permitiendo la lógica pre y post-controlador.

## 📋 Requisitos

Asegúrate de tener instalado lo siguiente:

-  **PHP 8.1 o superior**
-  [Composer](https://getcomposer.org/) (administrador de dependencias de PHP)
-  Un servidor web (Nginx, Apache, o el servidor integrado de PHP para desarrollo)
-  Una base de datos (MySQL, MariaDB, PostgreSQL, o SQLite)

## 📦 Instalación

Sigue estos pasos para poner en marcha tu proyecto con Phast:

1. **Clona el repositorio:**

   ```bash
   git clone [https://github.com/tu-usuario/phast-framework.git](https://github.com/tu-usuario/phast-framework.git) mi-proyecto-phast
   cd mi-proyecto-phast
   ```

2. **Instala las dependencias de Composer:**

   ```bash
   composer install
   ```

3. **Configura tu entorno:**
   Copia el archivo de ejemplo `.env.example` a `.env` y personaliza las variables, especialmente las de conexión a la base de datos y `APP_DEBUG`.

   ```bash
   cp .env.example .env
   ```

4. **Configura tu servidor web:**
   Apunta el "Document Root" (o raíz de documentos) de tu servidor al directorio `/public` de tu proyecto. Esto es crucial por seguridad y para el correcto funcionamiento del enrutador.

   -  Para **Nginx**, tu configuración podría ser similar a esta:

      ```nginx
      server {
          listen 80;
          server_name tu-dominio.test; # O localhost si es local
          root /ruta/absoluta/a/tu-proyecto-phast/public; # ¡Asegúrate de cambiar esto!

          index index.php;

          location / {
              try_files $uri $uri/ /index.php?$query_string;
          }

          location ~ \.php$ {
              include snippets/fastcgi-php.conf;
              fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Ajusta tu versión de PHP-FPM
              fastcgi_split_path_info ^(.+\.php)(/.+)$;
              fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
              fastcgi_read_timeout 300;
          }
      }
      ```

   -  Para el **servidor integrado de PHP** (solo para desarrollo):
      Puedes iniciar un servidor simple desde el directorio raíz de tu proyecto ejecutando:

      ```bash
      composer serve # O `php -S localhost:8000 -t public`
      ```

      Luego, visita `http://localhost:8000` en tu navegador.

5. **¡Listo!**
   Visita la URL configurada en tu navegador (ej. `http://tu-dominio.test` o `http://localhost:8000`) y deberías ver la página de bienvenida de Phast.

## 📂 Estructura del Proyecto

```

/app                \# Lógica principal de tu aplicación (Controladores, Modelos, etc.)
/config             \# Archivos de configuración de la aplicación
/public             \# El punto de entrada web (index.php) y assets públicos (CSS, JS, imágenes)
/routes             \# Definición de todas las rutas de tu aplicación
/storage            \# Archivos generados por la aplicación (cache, logs, uploads, etc.)
/system             \# El núcleo del framework Phast (no debe ser modificado directamente)
/vendor             \# Librerías de terceros instaladas por Composer
.env                \# Variables de entorno específicas para tu despliegue
.env.example        \# Ejemplo de variables de entorno para una configuración inicial
composer.json       \# Manifiesto del proyecto y sus dependencias
composer.lock       \# Bloqueo de versiones exactas de las dependencias

```

## 💡 Ejemplo de Uso

Aquí te mostramos cómo interactuar con los componentes clave de Phast:

### Definir una Ruta

Las rutas se definen en `routes/web.php` (o en otros archivos si los organizas por módulos):

```php
// routes/web.php

use Phast\System\Http\Request;
use Phast\System\Http\Response;

// Ruta simple GET
$router->get('/', function (Request $request, Response $response) {
    return $response->send('¡Bienvenido a Phast Framework!');
});

// Ruta con parámetros y un controlador
$router->get('/users/{id}', 'UserController@show')->name('users.show');
```

### Crear un Controlador

Los controladores residen en `app/Controllers`. Utilizan la Inyección de Dependencias para obtener las instancias necesarias.

```php
// app/Controllers/UserController.php
<?php

namespace App\Controllers;

use Phast\System\Http\Request;  // Asegúrate de importar Request
use Phast\System\Http\Response; // Asegúrate de importar Response
use App\Models\User;            // Importa tu modelo User

class UserController
{
    // Las dependencias se inyectan automáticamente gracias al Contenedor DI
    public function __construct(
        private Response $response
        // Puedes inyectar Request si lo necesitas como propiedad aquí:
        // private Request $request
    ) {}

    /**
     * Muestra un usuario específico.
     * @param Request $request La instancia de la solicitud HTTP (inyectada por el RouterManager)
     * @param Response $response La instancia de la respuesta HTTP (inyectada por el RouterManager)
     * @param int $id El ID del usuario, capturado desde la URL
     * @return Response
     */
    public function show(Request $request, Response $response, int $id): Response
    {
        // Ejemplo de uso de un modelo (asumiendo ORM básico)
        $user = User::find($id);

        if (!$user) {
            return $response->send("Usuario no encontrado", 404);
        }

        // Renderiza una vista y pasa los datos
        return $response->view('users.show', ['user' => $user]);
    }
}
```

### Crear un Modelo

Los modelos extienden de `Phast\System\Database\BaseModel` para interactuar con la base de datos.

```php
// app/Models/User.php
<?php

namespace App\Models;

use Phast\System\Database\BaseModel; // Asegúrate de que esta sea la ruta correcta

class User extends BaseModel
{
    // Define el nombre de la tabla si difiere de la convención de nombres (plural del nombre del modelo)
    protected string $table = 'users';

    // Opcional: Define las columnas fillable para asignación masiva
    // protected array $fillable = ['name', 'email', 'password'];
}
```

### Crear una Vista

Las vistas son archivos PHP puros que residen en tu directorio de vistas (ej. `views/users/show.view.phtml`).

```php
@content <h2>Detalles del Usuario</h2>
<p>ID: <?= htmlspecialchars($user->id ?? '') ?></p>
<p>Nombre: <?= htmlspecialchars($user->name ?? '') ?></p>
<p>Email: <?= htmlspecialchars($user->email ?? '') ?></p>

@partial('components.footer')
```

### Crear un Layout

Los layouts residen en tu directorio de layouts (ej. `views/layouts/default.layout.phtml`).

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phast App</title>
    </head>
<body>
    <header>
        <h1>Mi Aplicación Phast</h1>
        <nav>
            <a href="/">Inicio</a>
            <a href="/users/1">Usuario 1</a>
        </nav>
    </header>

    <main>
        @content </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Phast Framework. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
```

## 🤝 Contribuciones

¡Las contribuciones son siempre bienvenidas\! Si deseas mejorar Phast, por favor, siéntete libre de:

1. Abrir un "issue" para reportar un bug, sugerir una nueva característica o discutir cambios propuestos.
2. Enviar un "pull request" con tus mejoras. Asegúrate de seguir las convenciones de código existentes y de incluir pruebas si es aplicable.

## 📜 Licencia

Phast Framework es un software de código abierto licenciado bajo la [Licencia MIT](https://www.google.com/search?q=LICENSE).
