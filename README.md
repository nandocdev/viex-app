# Sistema de Gestión de Trabajos de Extensión VIEX

---

## Descripción General

El **Sistema de Gestión de Trabajos de Extensión VIEX** es una plataforma integral desarrollada con el framework **Phast** para optimizar y automatizar el ciclo de vida de los trabajos de extensión universitaria. Desde la creación por parte de los profesores hasta el complejo flujo de revisión, aprobación y certificación por parte de las autoridades académicas, VIEX busca digitalizar y centralizar todo el proceso. Esto asegura una mayor eficiencia, transparencia y trazabilidad en la gestión de las actividades de extensión universitaria.

---

## Tecnologías Utilizadas

-  **Backend:** PHP 8.1+ con Framework Phast (custom)
-  **Base de Datos:** MySQL/PostgreSQL con ORM Phoenix
-  **Frontend:** HTML5, CSS3, JavaScript con Bootstrap 4
-  **Autenticación:** Sistema de sesiones con middleware personalizado
-  **Templates:** Motor de vistas PHP con layouts y parciales
-  **Dependencias:** Composer para gestión de paquetes
-  **Servidor:** Apache/Nginx o servidor integrado de PHP

---

## Estructura del Proyecto

```
viex.com/
├── app/                          # Código de la aplicación
│   ├── Contracts/               # Contratos e interfaces
│   ├── Middleware/              # Middleware personalizado
│   ├── Modules/                 # Módulos de la aplicación
│   │   ├── Admin/              # Administración del sistema
│   │   ├── Auth/               # Autenticación y gestión de usuarios
│   │   ├── Home/               # Páginas principales y dashboard
│   │   ├── Projects/           # Gestión de trabajos de extensión
│   │   ├── Reports/            # Reportes y consultas
│   │   └── Workflow/           # Flujo de aprobación y certificación
│   ├── Providers/              # Proveedores de servicios
│   └── Services/               # Servicios de la aplicación
├── config/                      # Configuración del sistema
├── public/                      # Directorio público (assets, index.php)
├── resources/                   # Recursos de vistas y templates
├── routes/                      # Definición de rutas
├── system/                      # Núcleo del framework Phast
│   ├── Auth/                   # Sistema de autenticación
│   ├── Console/                # Comandos CLI
│   ├── Core/                   # Componentes principales
│   ├── Database/               # Capa de base de datos
│   ├── Http/                   # Manejo de peticiones HTTP
│   ├── Phoenix/                # ORM Phoenix
│   ├── Plugins/                # Plugins del sistema
│   ├── Providers/              # Proveedores del sistema
│   ├── Rendering/              # Motor de renderizado
│   └── Routing/                # Sistema de enrutamiento
└── vendor/                      # Dependencias de Composer
```

---

## Módulos Principales

### 1. Autenticación (`Auth`)

Maneja todo lo relacionado con el acceso de usuarios al sistema:

-  **Inicio y cierre de sesión**
-  **Gestión de perfiles de usuario**
-  **Recuperación y cambio de contraseñas**
-  **Middleware de autenticación**

### 2. Gestión de Proyectos (`Projects`)

Orientado a la gestión de trabajos de extensión:

-  **Creación y edición de proyectos**
-  **Gestión de evidencias y documentos**
-  **Estado y seguimiento de proyectos**
-  **Interfaz para profesores**

### 3. Flujo de Trabajo (`Workflow`)

Sistema de aprobación y certificación:

-  **Revisión de proyectos pendientes**
-  **Aprobación por coordinadores y decanos**
-  **Solicitud de subsanaciones**
-  **Certificación final de proyectos**

### 4. Administración (`Admin`)

Panel de administración del sistema:

-  **Gestión de usuarios y roles**
-  **Configuración del sistema**
-  **Mantenimiento de catálogos**
-  **Control de acceso y permisos**

### 5. Reportes (`Reports`)

Herramientas de consulta y análisis:

-  **Reportes estadísticos**
-  **Consultas globales de proyectos**
-  **Historial detallado de trabajos**
-  **Exportación de datos**

### 6. Páginas Principales (`Home`)

Contenido público y dashboard:

-  **Página de inicio**
-  **Dashboard de usuario**
-  **Proyectos públicos**
-  **Páginas informativas (about, contact)**

---

## Características del Framework Phast

### Arquitectura Modular

-  **Separación clara de responsabilidades**
-  **Módulos independientes y reutilizables**
-  **Sistema de rutas por módulo**
-  **Middleware personalizable**

### ORM Phoenix

-  **Query Builder fluido y expresivo**
-  **Mapeo objeto-relacional**
-  **Transacciones de base de datos**
-  **Relaciones entre entidades**

### Sistema de Vistas

-  **Motor de templates PHP**
-  **Layouts y parciales reutilizables**
-  **Componentes de UI**
-  **Renderizado eficiente**

### Seguridad

-  **Protección CSRF**
-  **Middleware de autenticación**
-  **Validación de datos**
-  **Sanitización de entradas**

---

## Instalación y Configuración

### Requisitos Previos

-  PHP 8.1 o superior
-  Composer
-  Servidor web (Apache/Nginx) o servidor integrado de PHP
-  Base de datos MySQL o PostgreSQL

### Pasos de Instalación

1. **Clonar el repositorio:**

   ```bash
   git clone https://github.com/tu-usuario/viex.com.git
   cd viex.com
   ```

2. **Instalar dependencias:**

   ```bash
   composer install
   ```

3. **Configurar variables de entorno:**

   ```bash
   cp .env.example .env
   # Editar .env con las configuraciones de tu entorno
   ```

4. **Configurar la base de datos:**

   -  Crear la base de datos
   -  Configurar las credenciales en `.env`
   -  Ejecutar migraciones (si están disponibles)

5. **Iniciar el servidor de desarrollo:**

   ```bash
   composer serve
   # O usar el servidor integrado de PHP
   php -S localhost:8000 -t public
   ```

6. **Acceder a la aplicación:**
   -  URL: `http://localhost:8000`
   -  Credenciales por defecto: consultar documentación

---

## Configuración del Entorno

### Variables de Entorno Principales

```env
APP_NAME=VIEX
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=tu-clave-de-32-caracteres

DB_HOST=localhost
DB_DATABASE=viex_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
DB_PORT=3306
```

### Configuración de Base de Datos

El sistema utiliza el ORM Phoenix que soporta:

-  **MySQL/MariaDB**
-  **PostgreSQL**
-  **SQLite** (para desarrollo)

---

## Uso del Sistema

### Para Profesores

1. **Acceder al sistema** con credenciales de profesor
2. **Crear nuevo proyecto** de extensión
3. **Subir evidencias** y documentos requeridos
4. **Enviar para revisión** cuando esté completo
5. **Seguir el estado** del proyecto en el dashboard

### Para Coordinadores/Decanos

1. **Revisar proyectos pendientes** en el módulo Workflow
2. **Aprobar o solicitar cambios** según corresponda
3. **Asignar a comisiones** evaluadoras si es necesario
4. **Certificar proyectos** aprobados

### Para Administradores

1. **Gestionar usuarios** y permisos
2. **Configurar parámetros** del sistema
3. **Generar reportes** y estadísticas
4. **Mantener catálogos** del sistema

---

## Desarrollo

### Estructura de un Módulo

```
Modules/NombreModulo/
├── Controllers/           # Controladores del módulo
├── Models/               # Modelos y entidades
│   ├── Entities/         # Entidades de base de datos
│   ├── Repositories/     # Repositorios de datos
│   └── ValueObjects/     # Objetos de valor
├── routes.php           # Rutas del módulo
└── Services/            # Servicios específicos
```

### Crear un Nuevo Módulo

```bash
php phast make:module NombreModulo
```

### Crear un Controlador

```bash
php phast make:controller NombreModulo/Controllers/MiController
```

### Crear un Modelo

```bash
php phast make:model NombreModulo/Models/MiModelo
```

---

## Contribución

¡Las contribuciones son bienvenidas! Para contribuir al proyecto:

1. **Fork del repositorio**
2. **Crear una rama** para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. **Realizar cambios** siguiendo las convenciones del proyecto
4. **Ejecutar pruebas** para asegurar que todo funciona
5. **Commit de cambios** con mensajes descriptivos
6. **Push a tu rama** (`git push origin feature/nueva-funcionalidad`)
7. **Crear Pull Request** con descripción detallada

### Convenciones de Código

-  **PSR-4** para autoloading
-  **PSR-12** para estilo de código
-  **Comentarios en español** para documentación
-  **Nombres descriptivos** para variables y métodos

---

## Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más detalles.

---

## Contacto

-  **Desarrollador:** Fernando Castillo
-  **Email:** fdocst@gmail.com
-  **Framework:** [Phast](https://github.com/nandocdev/phast)

---

## Documentación Adicional

-  [Documentación del Framework Phast](doc/PhastDoc.md)
-  [Guía de Base de Datos](doc/Database.md)
-  [Análisis Técnico](doc/analisis/Resumen.md)
-  [ORM Phoenix](doc/ORM.md)

---

_VIEX - Sistema de Gestión de Trabajos de Extensión Universitaria_
