<p align="center">
  <img src="URL_A_TU_LOGO_AQUI" alt="Phast Framework Logo" width="150"/>
</p>

<h1 align="center">Phast Framework</h1>

<p align="center">
  <strong>Un framework PHP moderno, minimalista y ultrarrápido.</strong><br>
  Diseñado con PHP 8+ en mente, Phast ofrece una base sólida y elegante para construir aplicaciones web de alto rendimiento.
</p>

<p align="center">
  <a href="https://github.com/tu-usuario/phast-framework/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="Licencia MIT"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.1%2B-blue.svg" alt="Versión de PHP"></a>
  <a href="#"><img src="https://img.shields.io/badge/status-en%20desarrollo-orange.svg" alt="Estado"></a>
</p>

---

## Filosofía

Phast se basa en la simplicidad, el rendimiento y las buenas prácticas de desarrollo. No intenta ser un framework monolítico "todo en uno", sino que proporciona las herramientas esenciales y una arquitectura limpia para que puedas construir aplicaciones de forma rápida y mantenible.

-  **Moderno:** Aprovecha al máximo las características de PHP 8+, incluyendo tipado estricto y atributos.
-  **Ligero:** Un núcleo mínimo sin dependencias innecesarias, garantizando un arranque veloz.
-  **Desacoplado:** Construido sobre un potente Contenedor de Inyección de Dependencias (DI) para un código limpio y fácil de probar.
-  **Extensible:** Diseñado para ser fácilmente ampliable con tus propias librerías o paquetes de terceros.

## Características Principales

-  **Enrutador Avanzado:** Soporte para verbos HTTP (GET, POST, etc.), parámetros de ruta, grupos con prefijos, middleware y rutas con nombre. Incluye un sistema de caché de rutas para un rendimiento óptimo en producción.
-  **Contenedor de Inyección de Dependencias:** Gestión de servicios y resolución automática de dependencias (auto-wiring).
-  **ORM Básico (Active Record):** Una capa de abstracción de base de datos elegante para interactuar con tu BBDD de forma intuitiva.
-  **Sistema de Vistas Potente:** Motor de plantillas simple que soporta layouts (`@content`) y vistas parciales (`@partial`).
-  **Gestor de BBDD Multi-Conexión:** Conéctate a diferentes motores de base de datos (MySQL, PostgreSQL, SQLite) de forma transparente.
-  **Configuración Basada en Entorno:** Carga de configuración segura a través de archivos `.env`.
-  **Middleware Pipeline:** Procesa las solicitudes HTTP a través de un sistema de "capas" (patrón cebolla).

## Requisitos

-  PHP 8.1 o superior
-  Composer
-  Servidor web (Nginx, Apache, o el servidor integrado de PHP)
-  Base de datos (MySQL, MariaDB, PostgreSQL, o SQLite)

## Instalación

1. **Clona el repositorio:**

   ```bash
   git clone https://github.com/tu-usuario/phast-framework.git mi-proyecto
   cd mi-proyecto
   ```

2. **Instala las dependencias de Composer:**

   ```bash
   composer install
   ```

3. **Configura tu entorno:**
   Copia el archivo de ejemplo `.env.example` a `.env` y personaliza las variables, especialmente las de conexión a la base de datos.

   ```bash
   cp .env.example .env
   ```

4. **Configura tu servidor web:**
   Apunta el "Document Root" (o raíz de documentos) de tu servidor al directorio `/public` del proyecto. Esto es crucial por seguridad.

   Para **Nginx**, tu configuración podría ser similar a esta:

   ```nginx
   server {
       listen 80;
       server_name tu-dominio.test;
       root /ruta/a/tu-proyecto/public;

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Ajusta tu versión de PHP
       }
   }
   ```

5. **¡Listo!**
   Visita `http://tu-dominio.test` en tu navegador y deberías ver la página de bienvenida de Phast.

## Estructura del Proyecto

```
/app                # Lógica de tu aplicación (Controladores, Modelos, etc.)
/config             # Archivos de configuración
/public             # Punto de entrada y assets públicos
/routes             # Definición de las rutas de la aplicación
/storage            # Archivos generados (cache, logs, etc.)
/system             # El núcleo del framework Phast
/vendor             # Dependencias de Composer
.env                # Variables de entorno
composer.json       # Manifiesto del proyecto
```

## Ejemplo de Uso

### Definir una Ruta

En `routes/web.php`:

```php
// routes/web.php
$router->get('/users/{id}', 'UserController@show')->name('users.show');
```

### Crear un Controlador

En `app/Controllers/UserController.php`:

```php
// app/Controllers/UserController.php
namespace App\Controllers;

use App\Models\User;
use System\Http\Response;

class UserController
{
    public function __construct(private Response $response) {}

    public function show(int $id): Response
    {
        $user = User::find($id);
        return $this->response->view('users.show', ['user' => $user]);
    }
}
```

### Crear un Modelo

En `app/Models/User.php`:

```php
// app/Models/User.php
namespace App\Models;

use System\Database\BaseModel;

class User extends BaseModel
{
    protected string $table = 'users';
}
```

## Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar Phast, por favor, abre un "issue" para discutir los cambios propuestos o envía un "pull request".

## Licencia

Phast Framework es un software de código abierto licenciado bajo la [Licencia MIT](LICENSE).
