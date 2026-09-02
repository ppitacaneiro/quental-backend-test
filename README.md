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