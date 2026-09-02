# Quental Backend Test

Backend desarrollado como prueba técnica para Quental.

## Stack tecnológico

* PHP
* Laravel 13
* Laravel Sail
* MySQL 8.4
* Docker
* Docker Compose

## Requisitos

Para ejecutar el proyecto es necesario disponer de:

* Docker Desktop
* WSL2
* Ubuntu sobre WSL2
* Git

El entorno de desarrollo está preparado para ejecutarse mediante **Laravel Sail**.

## Configuración del entorno

El proyecto utiliza Laravel Sail para gestionar el entorno de desarrollo mediante contenedores Docker.

Actualmente se dispone de dos servicios:

* `laravel.test`: aplicación Laravel.
* `mysql`: base de datos MySQL 8.4.

### Levantar el entorno

Desde Ubuntu/WSL2, situarse en el directorio del proyecto:

```bash
cd /mnt/c/quental-backend-test
```

Levantar los servicios:

```bash
./vendor/bin/sail up -d
```

Comprobar el estado de los contenedores:

```bash
./vendor/bin/sail ps
```

## Base de datos

Las migraciones definen el modelo de datos necesario para la aplicación:

* `users`
* `characters`
* `episodes`
* `locations`
* `character_episode`
* `favorites`

Para ejecutar las migraciones:

```bash
./vendor/bin/sail artisan migrate
```

El modelo mantiene separado el identificador interno de cada entidad de su `external_id`, utilizado para relacionar los registros con la API de Rick and Morty y facilitar la sincronización idempotente.

## Arquitectura

Se mantiene una arquitectura sencilla, separando las responsabilidades principales.

La integración con Rick and Morty está aislada mediante:

* `RickAndMortyClient`: define el contrato de acceso a la API externa.
* `RickAndMortyHttpClient`: implementa dicho contrato utilizando el cliente HTTP de Laravel.

De esta forma, la lógica de sincronización y la aplicación no dependen directamente de HTTP ni de la implementación concreta de la API externa.

La solución evita introducir capas innecesarias, manteniendo únicamente las abstracciones necesarias para cumplir los requisitos de desacoplamiento, testabilidad y separación de responsabilidades.
