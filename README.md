# Quental Backend Test

Backend desarrollado como prueba técnica para Quental. Sincroniza datos de la [API pública de Rick and Morty](https://rickandmortyapi.com/) (localizaciones, episodios y personajes) contra una base de datos propia, de forma paginada e idempotente.

## Stack tecnológico

- PHP 8.3+
- Laravel 13
- Laravel Sail
- MySQL 8.4
- Docker / Docker Compose

## Requisitos previos

- Docker Desktop
- WSL2 con una distribución Ubuntu (en Windows)
- Git

Todo el entorno de desarrollo está preparado para levantarse mediante **Laravel Sail**, sin necesidad de instalar PHP, Composer ni MySQL en el equipo anfitrión.

## Instalación y arranque (primera vez)

Los siguientes pasos se ejecutan desde una terminal Ubuntu/WSL2, situados en el directorio del proyecto (en Windows, la ruta equivalente es `/mnt/c/quental-backend-test`):

```bash
cd /mnt/c/quental-backend-test
```

1. Copiar el archivo de variables de entorno:

    ```bash
    cp .env.example .env
    ```

2. En el `.env`, configurar la conexión a MySQL para que apunte al servicio `mysql` de Docker Compose (por defecto el `.env.example` trae SQLite, pensado para tests locales sin contenedores):

    ```env
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=quental
    DB_USERNAME=sail
    DB_PASSWORD=password
    ```

3. Instalar las dependencias PHP. Como `vendor/` todavía no existe, la primera instalación se hace con un contenedor temporal de Composer (no requiere tener PHP/Composer instalados localmente):

    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php85-composer:latest \
        composer install --ignore-platform-reqs
    ```

4. Levantar los contenedores:

    ```bash
    ./vendor/bin/sail up -d
    ```

5. Generar la clave de la aplicación:

    ```bash
    ./vendor/bin/sail artisan key:generate
    ```

6. Ejecutar las migraciones:

    ```bash
    ./vendor/bin/sail artisan migrate
    ```

    Opcionalmente, con `--seed` se crea un usuario de prueba definido en `DatabaseSeeder`.

Comprobar que los contenedores están arriba:

```bash
./vendor/bin/sail ps
```

En arranques posteriores basta con repetir el paso 4 (`./vendor/bin/sail up -d`).

### Base de datos

Las migraciones definen el modelo de datos necesario para la aplicación:

- `users`
- `locations`
- `characters`
- `episodes`
- `character_episode`
- `favorites`

El modelo mantiene separado el identificador interno de cada entidad (`id`) de su `external_id`, utilizado para relacionar los registros con la API de Rick and Morty y facilitar la sincronización idempotente.

## Sincronización con la API externa

La sincronización se ejecuta mediante el comando Artisan:

```bash
./vendor/bin/sail artisan sync:rick-and-morty
```

El comando (`SyncRickAndMortyCommand`) invoca `RickAndMortySyncService`, que sincroniza en orden:

1. Localizaciones (`syncLocations`)
2. Episodios (`syncEpisodes`)
3. Personajes (`syncCharacters`), enlazándolos con sus localizaciones y episodios ya sincronizados

Cada entidad se recorre de forma paginada según la respuesta de la API (`info.pages`) y se persiste con `updateOrCreate` sobre `external_id`, por lo que ejecutar el comando varias veces no genera duplicados, solo actualiza los datos existentes.

## Tests

La suite de tests (PHPUnit) se ejecuta con:

```bash
./vendor/bin/sail artisan test
```

o, de forma equivalente:

```bash
./vendor/bin/sail composer test
```

Los tests están organizados en `tests/Unit` (mappers, cliente HTTP) y `tests/Feature` (servicio de sincronización end-to-end). Todas las llamadas a la API externa se simulan con `Http::fake`, por lo que la suite no depende de red ni de la disponibilidad de rickandmortyapi.com, y cubre casos de paginación, idempotencia, campos nulos/desconocidos y propagación de errores HTTP.

## Arquitectura

La solución mantiene una separación sencilla de responsabilidades:

- `Integrations/RickAndMorty`: comunicación con la API externa.
  - `RickAndMortyClient`: contrato de acceso a la API.
  - `RickAndMortyHttpClient`: implementación mediante el cliente HTTP de Laravel.
  - `RickAndMortyResponseValidator`: validación de la estructura de las respuestas recibidas.
- `Dtos`: estructuras internas e inmutables para los datos ya mapeados.
- `Mappers`: transforman la respuesta cruda de la API externa en DTOs.
- `Services`: `RickAndMortySyncService` orquesta la sincronización y persistencia.
- `Models`: persistencia y relaciones (`Character`, `Episode`, `Location`, `User`).

## Decisiones de diseño

- **Contrato `RickAndMortyClient` desacoplado de la implementación HTTP**: el servicio de sincronización depende de una interfaz, no del cliente HTTP concreto (bindeado en `RickAndMortyServiceProvider`). Esto permite sustituir la implementación o simularla en tests sin tocar la lógica de negocio.
- **DTOs + Mappers**: el formato de la API externa (claves en inglés, URLs anidadas, valores `"unknown"`, etc.) se transforma en DTOs propios antes de llegar a los modelos, aislando al resto de la aplicación de cambios en el contrato de la API externa.
- **`external_id` separado del `id` interno**: permite ejecutar la sincronización de forma repetida e idempotente (`updateOrCreate`) sin duplicar registros ni depender de que los IDs externos coincidan con los internos.
- **Comando Artisan como punto de entrada**: `sync:rick-and-morty` expone la sincronización como una operación ejecutable manualmente o programable vía scheduler/cron, manteniendo la lógica en un servicio reutilizable e independiente de la consola.
- **Validación explícita de la respuesta externa**: `RickAndMortyResponseValidator` falla rápido (`InvalidArgumentException`) ante respuestas con forma inesperada, evitando propagar datos corruptos a la base de datos.
- **Entorno reproducible con Docker/Sail**: evita depender de la configuración del equipo anfitrión (versión de PHP, MySQL, extensiones) y facilita que cualquier persona levante el proyecto con los mismos pasos.
- **Tests con `Http::fake`**: la suite cubre paginación, idempotencia, campos nulos y errores HTTP sin llamar a la API real, haciendo los tests deterministas y rápidos.

## Alcance actual

El proyecto expone una API REST propia para autenticación, consulta de personajes y gestión de favoritos. La documentación interactiva está disponible en `/docs/api` y el contrato OpenAPI versionado en `docs/openapi.yaml`.

La sincronización de localizaciones, episodios y personajes desde la API pública de Rick and Morty continúa ejecutándose mediante el comando Artisan `sync:rick-and-morty`; esa integración externa no forma parte de la API HTTP propia.
