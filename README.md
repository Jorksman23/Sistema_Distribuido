# Sistema Distribuido

Proyecto en **Laravel 12** con conexión a **SQL Anywhere** mediante **ODBC 17**.  

##  Requisitos previos

- PHP >= 8.1
- Composer
- Laravel 12
- Driver ODBC 17 instalado y configurado
- Extensión `odbc` habilitada en PHP
    
## Instalación de Dependencias

Sigue estos pasos en orden cronológico para asegurar el correcto despliegue del entorno de desarrollo tanto para el ecosistema backend como para el frontend.

### 1. Entorno Backend (Composer)

Instala las dependencias base del framework ejecutando el siguiente comando en tu terminal:
    ```bash
    composer install

### 2. Instalar módulos de Node

Ejecuta el siguiente comando en la terminal para descargar las librerías necesarias para el diseño y los scripts de la interfaz:
    ```bash
    npm install

## Instalar paquetes del servicio de correo, PDF y dependencias del sistema

Si deseas instalar todas las dependencias requeridas (tanto de producción como de desarrollo) en una sola línea de comandos unificada utilizando Composer, ejecuta el siguiente bloque en tu terminal:
    ```bash
    composer require barryvdh/laravel-dompdf:^3.1 getbrevo/brevo-php:^4.0 guzzlehttp/guzzle:^7.10 laravel/framework:^12.58 laravel/sanctum:^4.3 livewire/livewire:^4.3 yoramdelangen/laravel-pdo-odbc:^2.0 && composer require --dev fakerphp/faker:^1.24 laravel/pail:^1.2 laravel/pint:^1.29 laravel/sail:^1.58 laravel/tinker:^2.11 mockery/mockery:^1.6 nunomaduro/collision:^8.9 phpunit/phpunit:^11.5

## Configurar la conexion a la base de datos en el archivo .env
- DB_CONNECTION=odbc
- DB_DSN=SQLAnywhere_DSN
- DB_USERNAME=usuario
- DB_PASSWORD=contraseñaF

## Configuracion de empresa y servidor de imagenes en el archivo .env
La aplicación permite cambiar de empresa editando el archivo `.env`.
- COMPANY_CODE=(Cambiar por el codigo de su empresa existente en su base de datos)
## Servidor de imagenes
- IMAGE_SERVER_BASE_URL=(Direccion de su servidor de imagenes)
- IMAGE_SERVER_PATH_PRODUCTS=(Palabra que va concatenado para acceder a la imagenes de  su servidor)
- IMAGE_SERVER_PATH_LOGOS=logo_tienda=(Palabra que va concatenado para acceder al logo de la empresa)
## Subir al servidor de imagenes
IMAGE_SERVER_UPLOAD_URL=(Direccion de su servicio para subir al servidor de imagenes)

# Documentación del Proyecto

Este proyecto está desarrollado en Laravel y organizado bajo una arquitectura modular.  
Cada componente cumple una función específica dentro del sistema y se documenta por separado para facilitar el mantenimiento y la colaboración.

## Estructura del Proyecto

- [Ver documentación de controladores](docs/controladores.md)
- [Ver documentación de modelos](docs/modelos.md)
- [Ver documentación de servicios](docs/servicios.md)
- [Ver documentación de helpers](docs/helpers.md)
- [Ver documentación de JavaScript](docs/js.md)
- [Ver documentación de middleware](docs/middleware.md)
- [Ver documentación de mail](docs/mail.md)
- [Ver documentación de repositories](docs/repositories.md)
- [Ver documentación de providers](docs/providers.md)
- [Ver documentación de htaccess](docs/htaccess.md)
