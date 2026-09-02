# Quental Backend Test

Backend desarrollado como prueba técnica para Quental.

## Stack tecnológico

- PHP
- Laravel 13
- Laravel Sail
- MySQL 8.4
- Docker
- Docker Compose

## Requisitos

Para ejecutar el proyecto es necesario disponer de:

- Docker Desktop
- WSL2
- Ubuntu sobre WSL2
- Git

El entorno de desarrollo está preparado para ejecutarse mediante **Laravel Sail**.

## Configuración del entorno

El proyecto utiliza Laravel Sail para gestionar el entorno de desarrollo mediante contenedores Docker.

Actualmente se dispone de dos servicios:

- `laravel.test`: aplicación Laravel.
- `mysql`: base de datos MySQL 8.4.

### Levantar el entorno

Desde Ubuntu/WSL2, situarse en el directorio del proyecto:

```bash
cd /mnt/c/quental-backend-test

./vendor/bin/sail up -d
```

Comprobar el estado de los contenedores:

```bash
./vendor/bin/sail ps
```

### Base de datos

Las migraciones definen el modelo de datos necesario para la aplicación:

- users
- characters
- episodes
- locations
- character_episode
- favorites

Para ejecutar las migraciones:

```bash
./vendor/bin/sail artisan migrate
```

El modelo mantiene separado el identificador interno de cada entidad de su external_id, utilizado para relacionar los registros con la API de Rick and Morty y facilitar la sincronización idempotente.

### Integración con Rick and Morty

La comunicación con la API externa está desacoplada de la lógica de la aplicación.

La integración se organiza mediante:

- RickAndMortyClient: contrato de acceso a la API.
- RickAndMortyHttpClient: implementación mediante el cliente HTTP de Laravel.
- RickAndMortyResponseValidator: validación de las respuestas recibidas.
- Dtos: estructuras internas para los datos obtenidos.
- Mappers: transformación de las respuestas externas.

## Sincronización

La sincronización se realiza mediante RickAndMortySyncService y está preparada para trabajar de forma paginada e idempotente.

Para ejecutarla:

```bash
./vendor/bin/sail artisan sync:rick-and-morty
```

El proceso sincroniza las localizaciones, episodios y personajes, manteniendo sus relaciones.

### Arquitectura

La solución mantiene una separación sencilla de responsabilidades:

- Integrations: comunicación con servicios externos.
- Dtos: estructuras de datos.
- Mappers: transformación de datos externos.
- Services: lógica de sincronización.
- Models: persistencia y relaciones.
