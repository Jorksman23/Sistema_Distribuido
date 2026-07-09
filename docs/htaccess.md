## `.htaccess` (Enrutamiento Raíz)

Este archivo `.htaccess` se ubica en la **raíz del proyecto** y actúa como el despachador de tráfico principal a nivel de servidor web (Apache). Su propósito es emular el comportamiento de un servidor dedicado, redirigiendo el tráfico hacia la carpeta `public/` de Laravel sin exponer la estructura interna de la aplicación en la URL.

##  Información General

* **Tecnología:** Servidor Web Apache (`mod_rewrite`).
* **Propósito:** Enmascaramiento de rutas, soporte para URLs amigables y puente (*bridge*) hacia el Front Controller de Laravel.

---

##  Reglas de Enrutamiento y Lógica Técnica

El motor de reescritura (`RewriteEngine On`) evalúa las peticiones entrantes bajo un pipeline secuencial de dos fases:

### Fase 1: Despacho Directo de Recursos Estáticos (`public/`)
* **Condición:** Se activa si el archivo o directorio solicitado **no existe** físicamente en la raíz del proyecto, pero **sí existe** dentro del directorio `public/` (por ejemplo: archivos CSS, JS, imágenes o enlaces simbólicos del *storage*).
* **Mecanismo:** 1. Calcula dinámicamente la ruta base del entorno mediante una comparación de variables de servidor (`%{REQUEST_URI}`).
  2. Verifica si el recurso existe bajo `public/$1`.
  3. Si es positivo, reescribe la petición internamente hacia `public/$1` y detiene la evaluación con la bandera `[L]` (*Last*). El cliente final nunca ve la palabra `/public/` en su navegador.

### Fase 2: Redirección al Front Controller (*Bridge*)
* **Condición:** Se ejecuta si la petición no corresponde a ningún archivo o directorio real en la raíz, ni tampoco a un recurso estático dentro de `public/` (es decir, rutas de la aplicación como `/login`, `/catalogo`, `/api/productos`).
* **Mecanismo:** Redirige de forma absoluta todo el tráfico hacia el archivo `index.php` ubicado en la raíz del proyecto. Este archivo actúa como el punto de entrada que inicializa los componentes esenciales de Laravel.

---
