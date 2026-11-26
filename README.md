# IEANJESÚS La Libertad

## Sistema de gestión y control de informes mensuales

Plataforma web para registrar, consultar y dar seguimiento a los informes mensuales de los departamentos de IEANJESÚS La Libertad.

## Proyecto académico

Trabajo de integración curricular: Diseño e Implementación de una Plataforma Web para la Gestión y Control de Informes Mensuales de los Departamentos de la IEANJESÚS La Libertad.

Estudiante: Mariela Abigail Yagual Intriago.

## Funcionalidades

- Autenticación y gestión de usuarios activos.
- Administración de departamentos y asignación de roles.
- Formularios de informes mensuales basados en plantillas.
- Registro de borradores, envío, revisión, observaciones, corrección y aprobación.
- Consulta mediante filtros de departamento, período y estado.
- Panel de indicadores de cumplimiento y seguimiento.

## Roles previstos

| Rol | Responsabilidad |
|---|---|
| Administrador | Gestión de usuarios y departamentos; consulta institucional |
| Secretario de departamento | Elaboración, corrección y envío de informes propios |
| Secretaría de Junta Local | Revisión, observaciones y aprobación |
| Director de departamento | Consulta departamental |
| Pastor | Supervisión y consulta institucional |

## Tecnologías

Laravel 10, PHP, PostgreSQL, Blade, Tailwind CSS, Livewire, Composer, Node.js y Vite.

## Instalación en un entorno nuevo

Revisar los requisitos de composer.json y package.json antes de instalar.

```powershell
composer install
npm.cmd ci
Copy-Item .env.example .env
php artisan key:generate
```

El comando de copia y la generación de clave son exclusivos de una instalación nueva. No sobrescribir el archivo .env ni regenerar APP_KEY en una instalación existente.

Configurar .env con los valores de la base PostgreSQL previamente creada:

```dotenv
APP_NAME="IEANJESÚS La Libertad"
APP_ENV=local
APP_URL=http://127.0.0.1:8001
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ieanjesus
DB_USERNAME=usuario_local
DB_PASSWORD=contraseña_local
```

En una base nueva ejecutar `php artisan migrate`. La inicialización de plantillas y cuentas institucionales debe realizarse mediante el procedimiento autorizado del proyecto; no ejecutar los seeders de demostración heredados sobre una base institucional.

```powershell
npm.cmd run build
php artisan serve --host=127.0.0.1 --port=8001
```

Acceso local: http://127.0.0.1:8001

## Verificación y seguridad

Comprobar la configuración mediante `php artisan migrate:status` y `php artisan view:cache`. Ejecutar `php artisan test` únicamente con una base de pruebas aislada y previamente configurada. Las pruebas funcionales de los cinco roles deben documentarse con resultados reales.

No publicar credenciales, archivos .env, copias de bases de datos ni datos personales. En producción utilizar HTTPS, APP_DEBUG=false y respaldos periódicos. No utilizar migrate:fresh sobre bases con información institucional.

## Créditos y licencia

Adaptación académica basada en el proyecto [EDO Portal](https://github.com/SemGAA/edo-portal), con personalización institucional y módulos de informes mensuales. Conservar los avisos de autoría y las condiciones de licencia del código original y sus dependencias. La licencia concreta del proyecto base debe verificarse en el repositorio antes de redistribuirlo.
