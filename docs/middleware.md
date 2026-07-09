## Middleware: 

## `AuthCustom`

El middleware `AuthCustom` se encarga de interceptar las peticiones HTTP entrantes para validar de forma estricta que el cliente posea una sesión activa en el sistema antes de permitirle el acceso a rutas protegidas.

##  Información General

* **Namespace:** `App\Http\Middleware`
* **Dependencias Principales:** Laravel Framework (`Request`, `Closure`).
* **Propósito:** Control de acceso y protección de rutas (*Guard* personalizado basado en sesiones nativas).

---

##  Funcionamiento del Método `handle`

El método `handle` actúa como una capa de filtrado previa a la ejecución de los controladores. Su lógica evalúa el estado del cliente en dos escenarios posibles:

### 1. Usuario No Autenticado
* **Condición:** Si la función de asistencia `session()->has('user_id')` retorna `false` (lo que significa que la clave `user_id` no existe en el almacenamiento de sesiones del servidor).
* **Acción:** Interrumpe inmediatamente el ciclo de la petición y genera una respuesta de redirección absoluta hacia la ruta de inicio de sesión (`/login`).

### 2. Usuario Autenticado
* **Condición:** Si la clave `user_id` se encuentra presente en la sesión activa del navegador.
* **Acción:** Permite que la solicitud continúe su flujo normal hacia el siguiente eslabón de la aplicación (el siguiente middleware o el controlador final) mediante el llamado a `$next($request)`.

---

## `AuthToken`

El middleware `AuthToken` es el encargado de interceptar y validar las peticiones dirigidas a los endpoints de la API del sistema. Implementa una estrategia de autenticación basada en tokens estáticos (*Bearer Token*) consultando directamente sobre el motor de base de datos a través de la conexión dedicada.

##  Información General

* **Namespace:** `App\Http\Middleware`
* **Dependencias Principales:** Laravel Framework (`Request`, `Closure`, `DB`).
* **Propósito:** Protección de rutas API mediante autenticación de tokens a nivel de cabecera HTTP.

---

##  Funcionamiento del Método `handle`

Este middleware analiza y procesa las peticiones entrantes bajo el siguiente pipeline técnico:

### 1. Extracción e Inspección de Cabecera
* Captura el valor del header `Authorization`.
* Valida de forma estricta que la cabecera exista y que comience exactamente con el prefijo `Bearer `. Si no cumple esta estructura, aborta retornando una respuesta JSON con un estado **401 Unauthorized** (`'error' => 'Token requerido'`).

### 2. Sanitización y Consulta en Base de Datos (ODBC)
* Remueve el prefijo `Bearer ` para aislar la cadena limpia del token (`$token`).
* Ejecuta una consulta SQL cruda parametrizada y optimizada (`TOP 1`) utilizando la conexión `odbc` hacia la tabla `DBA.pw_ge_usuarios` para verificar si algún registro posee dicho `api_token`.
* Si la base de datos no retorna ningún resultado, intercepta el flujo y responde con un JSON de estado **401 Unauthorized** (`'error' => 'Token inválido'`).

### 3. Inyección Contextual y Continuidad
* Al localizar un usuario válido, almacena el objeto resultante de la base de datos (`user_id`, `nombre`, `email`) dentro del saco de propiedades de la petición HTTP mediante `$request->attributes->set('user', $user)`.
* Cede el control al siguiente componente mediante `$next($request)`.

---
