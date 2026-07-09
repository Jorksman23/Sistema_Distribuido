# Controlador:

## `CarritoController`

El controlador `CarritoController` gestiona el flujo completo del ciclo de vida del carrito de compras y el proceso de checkout (pago, facturación y descarga de comprobantes) dentro de la aplicación. Actúa como un intermediario entre las solicitudes HTTP del usuario y múltiples capas de lógica de negocio (Servicios y Repositorios).

## Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`, `Log`, `DB`), DomPDF Facade (`Pdf`).
* **Patrón de Diseño:** Inyección de dependencias en el constructor, desacoplamiento mediante arquitectura de Capa de Servicios y Repositorios.

---

## Inyección de Dependencias (Constructor)

El controlador inicializa las siguientes clases internas para delegar la lógica de negocio y el acceso a datos:

| Propiedad / Servicio | Tipo / Clase | Propósito |
| :--- | :--- | :--- |
| `$carrito` | `CarritoModel` | Modelo base de datos/ORM del carrito. |
| `$cartRepository` | `CartRepository` | Consultas optimizadas directas para persistencia del carrito. |
| `$cartService` | `CartService` | Reglas de negocio para añadir, actualizar y calcular totales del carrito. |
| `$checkoutService` | `CheckoutService` | Orquestación del paso previo al pago final y cálculo de totales de compra. |
| `$paymentMethodService`| `PaymentMethodService` | Obtención de formas de pago válidas de la empresa y cuentas de banco. |
| `$paymentService` | `PaymentService` | Procesamiento del flujo transaccional de pagos. |
| `$orderRepository` | `OrderRepository` | Acceso y lectura de datos históricos y actuales de órdenes/pedidos. |
| `$cxcAuxiliarProformaService` | `CxcAuxiliarProformaService` | Conector auxiliar de cuentas por cobrar para proformas/pedidos. |
| `$comprobanteService` | `ComprobanteService` | Gestión de visualización y almacenamiento de comprobantes físicos/digitales. |
| `$loginRepository` | `LoginRepository` | Recuperación de perfiles e información de usuarios autenticados. |

---

##  Métodos del Controlador

### 1. `index()`
Muestra la vista principal del carrito de compras con el desglose de productos y totales calculados.
* **Método HTTP:** `GET`
* **Flujo:**
  1. Recupera el `user_id` de la sesión activa como `$codCliente`.
  2. Invoca a `CartService::obtenerResumenCarrito()`.
  3. Retorna la vista `cart.index` inyectando el resumen.
* **Manejo de Errores:** En caso de fallo (`Throwable`), renderiza la vista `errors.500` detallando el mensaje de excepción.

---

### 2. `add(Request $request)`
Agrega un producto específico al carrito de compras del usuario autenticado.
* **Método HTTP:** `POST`
* **Validación de Datos:**
  * `codigo_item` (Requerido | String)
  * `nombre`, `pvp3`, `imagen` (Opcionales | String/Numeric)
  * `presentacion` (Opcional | Integer)
* **Respuestas:**
  * **AJAX / JSON:** Retorna un JSON indicando éxito (`success: true`) y el conteo actualizado de productos (`carrito_count`).
  * **Síncrona:** Redirige a la página anterior con un mensaje flash `success_cart`.
  * **Estatus de Error:** Captura excepciones devolviendo un código HTTP `422` en solicitudes JSON.

---

### 3. `update(Request $request)`
Actualiza la cantidad seleccionada de un ítem existente dentro del carrito.
* **Método HTTP:** `PUT` / `POST`
* **Validación de Datos:**
  * `id_item_web` (Requerido | Integer)
  * `cantidad` (Requerido | Mínimo: 1, Máximo: 99)
* **Flujo:** Limpia la caché de la sesión (`carrito_count`), invoca el servicio de actualización y recalcula de forma síncrona/asíncrona el `subtotal`, `iva`, `total` y la cantidad global de productos.

---

### 4. `remove(Request $request)`
Elimina un producto del carrito por su ID único web.
* **Método HTTP:** `DELETE` / `POST`
* **Validación de Datos:** `id_item_web` (Requerido | Integer)
* **Flujo:** Invoca a `CartService::eliminarProducto()` y devuelve el nuevo estado financiero recalculado del carrito de compras (subtotal, iva, total) para actualizar dinámicamente la interfaz gráfica.

---

### 5. `vaciar(Request $request)`
Elimina por completo todos los productos agregados al carrito de un usuario.
* **Método HTTP:** `POST`
* **Flujo:** Remueve los registros mediante `CartService::vaciarCarrito()`, destruye el contador de sesión y redirige o retorna confirmación JSON.

---

### 6. `pagar()`
Prepara la interfaz del Checkout recolectando la información requerida para efectuar la transacción monetaria.
* **Método HTTP:** `GET`
* **Flujo:**
  1. Verifica el checkout actual del cliente. Si no posee ítems, redirige al carrito con un error descriptivo.
  2. Extrae las formas de pago configuradas de manera global para la empresa actual (`currentCompany()`).
  3. Extrae la información del perfil del cliente desde el `LoginRepository`.
  4. Renderiza la plantilla `pedidos.pagar`.

---

### 7. `obtenerDatosCliente(Request $request, PaymentService $paymentService)`
Busca un cliente existente en el ERP interno o crea un registro temporal/definitivo para la facturación.
* **Método HTTP:** `GET` / `POST`
* **Respuesta:** Devuelve en formato JSON la estructura completa de los datos sanitizados del cliente recuperado desde el servicio externo.

---

### 8. `procesarPago(Request $request)`
Valida los datos de envío, facturación y método de entrega seleccionados para proceder al cierre del pedido.
* **Método HTTP:** `POST`
* **Campos Validados:** `tipo_pago`, `cedula`, `nombre`, `email`, `telefono`, `direccion`, `metodo_entrega` (Valores permitidos: 'R' o 'E'), `observacion` (Opcional).
* **Flujo:** Delega el payload sanitizado directamente al core transaccional `PaymentService::procesarPago()`.

---

### 9. `obtenerCuentaBanco(int $secuencia)`
Retorna las coordenadas bancarias de una forma de pago en específico si esta lo amerita (ej. Transferencias bancarias).
* **Método HTTP:** `GET`
* **Flujo:** * Consulta si el método requiere credenciales financieras de la empresa actual. 
  * Si no las requiere o no existe, devuelve una respuesta JSON de control descriptiva. En caso exitoso, envía: `descripcion`, `cuenta`, `tipo`, `cta_contable`.

---

### 10. `mostrarComprobante()`
Renderiza la vista o redirige hacia el flujo del comprobante basado en la información guardada transitoriamente en la sesión (`checkout_data`).

---

### 11. `guardarComprobante(Request $request)`
Procesa la carga del documento digital (imagen/recibo) de pago que el cliente adjunta para avalar una transferencia o depósito directo.
* **Método HTTP:** `POST`
* **Validación de Archivo:** Requerido | Formatos válidos: `jpg`, `jpeg`, `png` | Tamaño máximo: 5MB (`5120 KB`).
* **Lógica Interna:** Recupera el carrito guardado, el total del checkout, los parámetros de sesión de la empresa y reenvía el archivo binario a `ComprobanteService::guardarComprobante()` para su procesamiento físico e inserción en base de datos.

---

### 12. `descargarPedido($codigo)`
Genera dinámicamente un documento en formato **PDF** con el desglose exacto de los ítems de una orden de compra para el resguardo físico del cliente.
* **Método HTTP:** `GET`
* **Lógica de Impuestos y Multiempresa (ODBC SQL Anywhere / Sybase):**
  * Realiza consultas directas vía base de datos a `DBA.GE_PARAMETROS` (Parámetro `17`) para determinar el porcentaje nominal del IVA.
  * Realiza consultas directas a `DBA.web_ge_parametros` (Parámetro `248`) para determinar si la empresa trabaja con **IVA Incluido** (`S` / `N`).
  * **Algoritmo de Desglose Financiero:**
    * Si la empresa trabaja con IVA Incluido, el precio unitario del catálogo ya incorpora el impuesto; se calcula la base imponible y el IVA de manera inversa.
    * Si trabaja sin IVA Incluido, el impuesto se calcula y adiciona sobre el valor total bruto de la línea de producto.
* **Salida:** Retorna una respuesta de descarga forzada del archivo PDF con nomenclatura: `Pedido_{codigo}.pdf`.

---

### 13. `retornoNuvei(Request $request)`
Punto de aterrizaje en el frontend (*Redirección Visual*) para los clientes que culminan un proceso de pago con tarjetas de crédito/débito en la pasarela externa **Nuvei**.
* **Método HTTP:** `GET`
* **Nota Arquitectónica:** Este método solo tiene fines informativos para la interfaz de usuario (`pending`, `success`, `failure`, `review`). La lógica dura de aprobación y la confirmación contable y de stock de la orden se procesa de forma segura a través de un **Webhook** asíncrono (Server-to-Server).


## `HomeController`

El controlador `HomeController` gestiona la lógica de la página de inicio pública del sitio web. Se encarga de recopilar y estructurar los datos del catálogo comercial principal, separando los productos tradicionales de aquellos designados para el carrusel de destacados, segmentándolos por la empresa configurada en el sistema.

##  Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`), Repositorio de Productos, Modelo de Productos.
* **Patrón de Diseño:** Instanciación directa en el método de controladores para desacoplamiento e hidratación de colecciones tipadas (Data Mapping).

---

##  Arquitectura de Datos y Mapeo

A diferencia de otros controladores que inyectan dependencias de manera global, este método inicializa internamente los siguientes componentes:

* **`ProductRepository`**: Encargado de realizar las consultas SQL o llamadas al ORM optimizadas para extraer productos activos y destacados de la base de datos de la empresa.
* **`ProductsModel`**: Utilizado no solo como entidad, sino como mapeador a través del método `mapRowToInstance($r)`. Este método transforma las filas planas devueltas por el repositorio (normalmente arrays o colecciones genéricas de la base de datos) en instancias de objetos del modelo de Laravel bien estructuradas.

---

##  Métodos del Controlador

### 1. `homeConCarrusel(Request $request)`
Prepara los datos esenciales requeridos para construir la Landing Page o pantalla de inicio de la aplicación, cargando productos destacados en formato carrusel y un listado reducido de productos activos generales.

* **Método HTTP:** `GET`
* **Flujo Técnico Interno:**
  1. **Identificación de Empresa:** Obtiene el código de la organización desde los archivos de configuración usando `config('app.company_code', '?')`. Si no existe, asigna un valor por defecto (`'?'`).
  2. **Consulta de Productos Activos:** Llama a `$repository->getActiveProducts(4, $empresa)` para extraer un máximo de **4 productos** vigentes de la empresa.
  3. **Consulta de Carrusel:** Llama a `$repository->getProductosDestacados(16, $empresa)` para extraer hasta **16 productos** designados específicamente como destacados para el carrusel publicitario.
  4. **Hidratación/Mapeo:** Utiliza la función nativa de PHP `array_map` junto con una función flecha (`fn($r)`) para convertir cada fila de datos de ambas consultas en un objeto interactivo del `ProductsModel`.
  5. **Renderizado:** Retorna la vista `home.home` enviando las variables de control del negocio.

* **Variables Inyectadas a la Vista:**

| Variable | Tipo de Datos | Descripción |
| :--- | :--- | :--- |
| `'empresa'` | `String` | Código identificador de la empresa actual (Multiempresa). |
| `'productos'` | `Array (ProductsModel[])` | Colección filtrada e hidratada de hasta 4 productos activos principales. |
| `'carrusel'` | `Array (ProductsModel[])` | Colección filtrada e hidratada de hasta 16 productos para el banner dinámico. |

---

## Consideraciones de Optimización

1. **Límites de Carga (Hardcoded):** El método limita de forma fija la carga de datos en memoria (4 ítems para la grilla base y 16 para el carrusel). Esto previene problemas de desbordamiento de memoria RAM en la aplicación ante catálogos masivos.
2. **Contexto Multiempresa:** La consulta es 100% dependiente del archivo de configuración del entorno (`app.company_code`), garantizando que la página de inicio cambie de manera dinámica según el despliegue de la marca.


## `LoginController`

El controlador `LoginController` centraliza y gestiona el ciclo de vida de la autenticación de usuarios, el registro de nuevas cuentas con verificación por correo electrónico (vía Brevo Mailer) y la desconexión segura del sistema.

##  Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`, `Hash`, `URL`, `Log`), `BrevoMailer` para notificaciones e infraestructura de Repositorios.
* **Patrón de Diseño:** Inyección de dependencias en el constructor y uso de repositorios para aislar la persistencia de datos.

---

## Inyección de Dependencias (Constructor)

El controlador inicializa los componentes necesarios para validar identidades y despachar notificaciones transaccionales:

* **`LoginRepository`** (Asignado a `$this->model`): Repositorio encargado de interactuar con las tablas de usuarios, búsquedas por correo y creación de cuentas.
* **`BrevoMailer`** (Asignado a `$this->mailer`): Servicio externo integrado para el envío de correos electrónicos en formato HTML (Mailing API).

---

## Métodos del Controlador

### 1. `showLogin()`
Muestra la interfaz del formulario de inicio de sesión cargando las bodegas/sucursales disponibles.
* **Método HTTP:** `GET`
* **Flujo:** Invoca a `ProductRepository::getUbicaciones()` filtrando por la empresa activa (`currentCompany()`) para rellenar las opciones de sucursal en la vista `auth.login`.

---

### 2. `showRegister()`
Renderiza la vista pública de creación de cuentas para clientes nuevos.
* **Método HTTP:** `GET`
* **Retorno:** Vista `auth.register`.

---

### 3. `login(Request $request)`
Autentica y valida el acceso de un usuario al portal comercial aplicando políticas corporativas multiempresa.
* **Método HTTP:** `POST`
* **Campos Validados:** `email`, `password`, `ubicacion` (Requeridos).
* **Reglas de Negocio Aplicadas (Filtros de Bloqueo):**
  1. **Credenciales:** Valida la existencia del email y comprueba el Hash de la contraseña (`Hash::check`).
  2. **Estado:** Si el campo `estado` no es igual a `'A'` (Activo), deniega el acceso.
  3. **Multiempresa:** Verifica que el registro coincida con el código de la empresa configurada (`config('app.company_code')`).
  4. **Verificación de Correo:** Valida que el campo `email_verified_at` no sea nulo.
* **Datos Almacenados en Sesión tras Éxito:** `user_id`, `nombre`, `email`, `pw_codigo`, `cod_cliente`, y `ubicacion_seleccionada`.

---

### 4. `register(Request $request)`
Registra un nuevo prospecto en la base de datos, encripta sus credenciales y gatilla el flujo de verificación asíncrona.
* **Método HTTP:** `POST`
* **Campos Validados:** `nombre` (Requerido), `email` (Requerido/Formato email), `password` (Requerido/Mínimo 6 caracteres).
* **Flujo Técnico Interno:**
  1. Comprueba si el correo electrónico ya se encuentra registrado.
  2. Invoca a `LoginRepository::createUser()` inyectando la contraseña encriptada y asignando el código de la empresa activa.
  3. **URL Firmada:** Genera un token seguro y temporal mediante `URL::temporarySignedRoute()` con una validez estricta de **60 minutos**.
  4. Renderiza la plantilla HTML de correo `email.verify` y despacha el correo mediante `$this->mailer->sendEmail()`.
  5. Preserva el email en la sesión y redirige hacia la ruta informativa `verification.notice`.

---

### 5. `resendVerification(Request $request)`
Permite al usuario solicitar manualmente un reenvío del enlace de verificación en caso de pérdida o expiración del token original.
* **Método HTTP:** `POST` / `GET`
* **Flujo:** Extrae el correo guardado transitoriamente en la sesión, revalida la existencia del usuario, regenera una nueva URL firmada por otros 60 minutos y procesa un nuevo envío por medio de Brevo.
* **Trazabilidad:** Escribe bitácoras detalladas en el archivo de logs tanto al iniciar el proceso como al recibir la respuesta de la API de Brevo.

---

### 6. `logout()`
Destruye la sesión del usuario de manera segura garantizando la consistencia del carrito.
* **Método HTTP:** `POST` / `GET`
* **Garantía del Estado del Carrito:** Antes de limpiar la sesión, el método recupera el identificador del cliente e invoca a `CartRepository::vaciar()` para limpiar la base de datos temporal del carrito (previniendo que queden productos reservados u obsoletos en la base de datos).
* **Finalización:** Ejecuta un `session()->flush()` para borrar de forma absoluta las variables y cookies del lado del servidor y redirige a `/login`.

---

## `PasswordController`

El controlador `PasswordController` es el encargado de gestionar el flujo completo de recuperación y restablecimiento de contraseñas de los usuarios del sistema. Delega la lógica de negocio pesada (como la creación de tokens y el envío de notificaciones) a un servicio especializado.

##  Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`), `PasswordResetService`.
* **Patrón de Diseño:** Inyección de dependencias en el constructor mediante promoción de propiedades (PHP 8+) y desacoplamiento mediante Capa de Servicios.

---

## Inyección de Dependencias (Constructor)

El controlador inyecta directamente el siguiente servicio en su inicialización:

* **`PasswordResetService`** (Asignado automáticamente a `$this->resetService`): Centraliza la lógica de validación de tokens de recuperación, expiraciones y persistencia física del cambio de credenciales en la base de datos.

---

## Métodos del Controlador

### 1. `requestForm()`
Muestra la vista inicial donde el usuario puede ingresar su correo electrónico para solicitar un cambio de contraseña.
* **Método HTTP:** `GET`
* **Retorno:** Vista `auth.Password_cambio`.

---

### 2. `sendLink(Request $request)`
Valida la solicitud del usuario e intenta despachar un enlace de recuperación único a su bandeja de entrada.
* **Método HTTP:** `POST`
* **Campos Validados:** `email` (Requerido / Formato de correo válido).
* **Flujo:** 1. Ejecuta la validación del correo.
  2. Llama al método del core `PasswordResetService::sendResetLink()`.
  3. Si el proceso es exitoso, redirige a la ruta intermedia confirmando el envío (`password.sent`).
  4. Si el servicio falla o no localiza el destinatario, regresa a la pantalla previa inyectando un mensaje de error en el campo `email`.

---

### 3. `sent()`
Muestra una interfaz informativa estática que confirma al usuario que el correo de recuperación fue enviado con éxito.
* **Método HTTP:** `GET`
* **Retorno:** Vista `auth.password_sent`.

---

### 4. `showResetForm(string $token, Request $request)`
Renderiza el formulario final donde el usuario introduce su nueva contraseña. Se activa cuando el usuario hace clic en el enlace recibido en su correo electrónico.
* **Método HTTP:** `GET`
* **Parámetros de URL:** `token` (Inyectado directamente desde la ruta de manera posicional).
* **Flujo:** Extrae el token de la URL y el parámetro `email` adjunto en la Query String de la solicitud HTTP para cargarlos directamente como datos ocultos (`hidden inputs`) en la vista `auth.reset_password`.

---

### 5. `reset(Request $request)`
Procesa el formulario, valida el nuevo password y efectúa el cambio definitivo de credenciales si el token es legítimo.
* **Método HTTP:** `POST`
* **Campos Validados:**
  * `email` (Requerido | Formato Email).
  * `token` (Requerido).
  * `password` (Requerido | Mínimo 8 caracteres | Debe incluir confirmación mediante el campo `password_confirmation`).
* **Flujo:**
  1. Aplica las reglas de validación en los inputs del formulario.
  2. Invoca el método transaccional `PasswordResetService::resetWithToken()`.
  3. Si la respuesta es exitosa (`true`), redirige al login del portal adjuntando un mensaje flash de éxito (`success`).
  4. Si el token falló, expiró o fue manipulado, retorna al formulario con un mensaje de error descriptivo en el campo `token`.

---

## Consideraciones de Seguridad

1. **Confirmación de Contraseña (`confirmed`):** El validador exige de manera estricta que exista un campo llamado `password_confirmation` en el frontend que coincida bit a bit con el nuevo `password`, mitigando errores tipográficos del cliente.
2. **Abstracción del Token:** El controlador no conoce el algoritmo de encriptación o almacenamiento del token (ej. si está en formato SHA-256 o en una tabla temporal de Laravel), delegando al 100% esa responsabilidad de verificación al `$resetService`.


## `ProductsController`

El controlador `ProductsController` gestiona la visualización del catálogo comercial público, la ficha detallada de cada producto con sus respectivas variantes/presentaciones y la lógica de filtrado dinámico junto con la paginación.

##  Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`), `ProductService`, `ProductRepository`, `Throwable`.
* **Patrón de Diseño:** Instanciación directa de servicios y repositorios dentro de los métodos para procesamiento dinámico de catálogos e hidratación bajo demanda.

---

##  Métodos del Controlador

### 1. `show(Request $request, string $codigo)`
Renderiza la vista de detalle de un producto específico, inyectando todas sus presentaciones comerciales disponibles y una lista de sugerencias de productos relacionados.

* **Método HTTP:** `GET`
* **Parámetros de URL:** `codigo` (String - Identificador único del producto).
* **Flujo Técnico Interno:**
  1. **Determinación del Contexto:** Captura el código de la empresa desde la Query String (`?empresa=...`). Si no viene informado, hereda la sesión activa con `currentCompany()`.
  2. **Lectura de Datos:** Instancia `ProductService` y ejecuta `getProductWithPresentations()` para estructurar el producto y sus variantes de empaque/presentación.
  3. **Control 404:** Si el array del producto retorna vacío, aborta el flujo renderizando la vista de control `errores.404`.
  4. **Productos Relacionados:** Si el producto base pertenece a una categoría o grupo (`!empty($producto['grupo'])`), invoca a `ProductRepository::getRelacionados()` limitando el resultado a **20 productos del mismo grupo**, excluyendo el ítem actual y filtrando por la `ubicacion_seleccionada` guardada en la sesión.
* **Manejo de Errores:** Cualquier fallo imprevisto interceptado por el bloque `catch` redirige al usuario a la raíz del catálogo (`catalogo.index`) exponiendo un mensaje flash con la excepción.

---

### 2. `index(Request $request)`
Construye la vista principal del catálogo de productos, aplicando filtros combinados y controlando los estados de la paginación.

* **Método HTTP:** `GET`
* **Flujo Técnico Interno:**
  1. **Inicialización de Paginación:** Captura el parámetro numérico `page`. Se asegura de que el valor mínimo sea 1 utilizando la función `max(1, ...)`.
  2. **Construcción de Matriz de Filtros:** Sanitiza y mapea los parámetros de búsqueda de la siguiente manera:

| Clave de Filtro | Origen del Parámetro / Request | Tipo de Dato | Propósito |
| :--- | :--- | :--- | :--- |
| `'search'` | `q` (Query String) | `String (Trimmed)` | Palabra clave o término de búsqueda textual. |
| `'grupo'` | `grupo` (Query String) | `String (Trimmed)` | Identificador del grupo de productos. |
| `'linea'` | `linea` (Query String) | `String (Trimmed)` | Identificador de línea o subcategoría. |
| `'ubicacion'` | `ubicacion_seleccionada` (Sesión) | `String` | Código de sucursal/bodega del usuario. |
| `'precioMin'` | `precio_min` (Query String) | `Float` | Límite inferior de rango de precio (Defecto: 0). |
| `'precioMax'` | `precio_max` (Query String) | `Float` | Límite superior de rango de precio (Defecto: 0). |
| `'orden'` | `orden` (Query String) | `String` | Criterio de ordenamiento (Defecto: 'codigo'). |

  3. **Procesamiento del Servicio:** Ejecuta `ProductService::getCatalogo()`, enviando los filtros estructurados, la página actual y la empresa.
  4. **Unión de Filtros Estáticos (Array Merge):** Al retornar la vista `catalogo.catalogo`, utiliza el operador `+` para fusionar el payload de los productos con el método `$service->getFiltros($empresa)`, encargada de proveer las listas de marcas, líneas y grupos que llenarán los selectores (*dropdowns*) de la interfaz.

* **Manejo de Errores:** En caso de fallas críticas en las consultas SQL u ODBC del catálogo, el bloque `Throwable` intercepta el error y redirige a la vista `errors.500`.

---

## Características Especiales de Arquitectura

1. **Persistencia Basada en la Ubicación:** El catálogo es altamente dependiente de la variable de sesión `ubicacion_seleccionada`. Esto garantiza que los filtros de existencias (stock) y la visualización de los productos relacionados correspondan exactamente a la sucursal física elegida por el cliente durante el Login.
2. **Fusión Dinámica de Datos (`+ $service->getFiltros`)**: Optimiza el renderizado al traer en una sola petición tanto los registros paginados como las estructuras necesarias para armar los filtros dinámicos laterales de la interfaz web.


## `ProfileController`

El controlador `ProfileController` gestiona la sección privada del perfil del cliente autenticado. Sus responsabilidades incluyen la exposición de la información personal, la actualización de datos básicos de contacto, la modificación segura de credenciales y la consulta histórica paginada de sus pedidos.

##  Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`, `Hash`, `DB`, `Log`), `LengthAwarePaginator` para el control de paginación manual.
* **Patrón de Diseño:** Arquitectura basada en repositorios para la gestión de usuarios y consultas directas via Query Builder/ODBC para el histórico transaccional.

---

##  Inyección de Dependencias (Constructor)

El controlador utiliza un repositorio centralizado de accesos:

* **`LoginRepository`** (Asignado a `$this->model`): Provee métodos abstractos para interactuar con la entidad de usuarios (`findById`, `updateUser`, `updatePassword`).

---

##  Métodos del Controlador

### 1. `show(Request $request)`
Renderiza la pantalla principal del perfil con la información del usuario en sesión.
* **Método HTTP:** `GET`
* **Flujo:**
  1. Extrae el `user_id` de la sesión activa. Si es inexistente, redirige al `login`.
  2. Busca el registro del usuario en la base de datos a través de `$this->model->findById()`. Si el usuario no es localizado, destruye implícitamente el acceso enviándolo al login.
  3. Devuelve la vista `profile.show`.

---

### 2. `orders(Request $request)`
Construye el historial de compras del usuario aplicando paginación manual sobre colecciones crudas provenientes de un motor ODBC.
* **Método HTTP:** `GET`
* **Conexión SQL (ODBC Sybase / SQL Anywhere):** Ejecuta una consulta SQL nativa directa sobre la tabla corporativa `DBA.PW_ORDENES_WEB` filtrando estrictamente por la combinación de la **empresa activa** (`currentCompany()`) y el **usuario en sesión**.
* **Estructura de la Paginación Manual:**
  * Debido a que se utiliza una consulta SQL cruda (`DB::select`), los datos se encapsulan inicialmente en un objeto `collect()`.
  * **Registros por página:** Fijo a `8` elementos (`$perPage = 8`).
  * Usa la fachada `LengthAwarePaginator::resolveCurrentPage()` para interceptar el parámetro `?page` de la URL.
  * Segmenta los datos de la colección usando el método nativo de colecciones de Laravel `forPage($currentPage, $perPage)`.
  * Preserva los parámetros de búsqueda o filtros adjuntos en la Query String por medio de las llaves contextuales `path` y `query`.
* **Trazabilidad:** Imprime un registro en logs (`Log::info`) detallando el conteo de pedidos recuperados para auditoría de rendimiento.
* **Retorno:** Vista `profile.orders`.

---

### 3. `update(Request $request)`
Actualiza la información básica de contacto del cliente en la base de datos corporativa.
* **Método HTTP:** `POST` / `PUT`
* **Campos Validados:**
  * `nombre` (Requerido | String | Máximo: 255 caracteres).
  * `telefono` (Opcional | String | Máximo: 20 caracteres).
  * `direccion` (Opcional | String | Máximo: 255 caracteres).
* **Sincronización de Estado:** Al confirmar el cambio en la base de datos a través del repositorio, actualiza de forma inmediata la variable global de sesión `session(['nombre' => $request->nombre])` para reflejar el cambio en los componentes visuales del frontend (Navbar/Layouts) sin forzar un re-login.

---

### 4. `updatePassword(Request $request)`
Procesa la actualización e interceptación segura del cambio de contraseña.
* **Método HTTP:** `POST`
* **Campos Validados:**
  * `current` (Requerido - Contraseña actual del usuario).
  * `password` (Requerido | Mínimo: 6 caracteres | Confirmado mediante el campo duplicado).
  * `password_confirmation` (Requerido).
* **Mecanismo de Seguridad:**
  * Recupera el objeto del usuario desde la persistencia.
  * Valida mediante `Hash::check()` que la contraseña introducida en el input `current` coincida rigurosamente con el hash almacenado en la columna `contrasena`. De no coincidir, aborta devolviendo un mensaje de error explícito.
  * Cifra el nuevo password utilizando el algoritmo estándar del sistema a través de `Hash::make()` y actualiza el registro.
* **Retorno:** Redirige a `profile.show` inyectando la variable flash `success_pass`.

---

##  Características Técnicas y Buenas Prácticas

1. **Aislamiento en Consultas Históricas:** La consulta de órdenes se realiza mediante una conexión explícita de lectura ODBC (`DB::connection('odbc')`), lo que deslinda al motor transaccional primario de Laravel de lecturas pesadas asociadas a reportería o históricos de facturas.
2. **Seguridad en Capas:** Todas las mutaciones de datos del perfil validan de forma explícita e inicial la existencia de la variable de sesión `user_id` antes de evaluar el payload de la petición, mitigando vulnerabilidades de inyección de parámetros de terceros.


## `VerificationController`

El controlador `VerificationController` gestiona el flujo crítico de activación y validación de cuentas de usuario mediante enlaces firmados enviados por correo electrónico. Verifica la integridad de los tokens de seguridad y actualiza el estado de verificación de manera directa en el motor de base de datos corporativo.

##  Información General

* **Namespace:** `App\Http\Controllers`
* **Dependencias Principales:** Laravel Framework (`Request`, `DB`), `Illuminate\Support\Carbon` para la gestión de marcas de tiempo del sistema.
* **Patrón de Diseño:** Mutación directa de datos sin ORM intermedio utilizando Query Builder nativo sobre una conexión dedicada (`odbc`).

---

##  Métodos del Controlador

### 1. `verify(Request $request, $id, $hash)`
Procesa el enlace en el que el cliente hace clic desde su correo electrónico para confirmar la legitimidad de su dirección de email.

* **Método HTTP:** `GET` *(Ruta típicamente registrada bajo un middleware de firmas URL temporales o rutas firmadas de Laravel).*
* **Parámetros de URL:**
  * `id`: Identificador único secuencial del usuario (`user_id`).
  * `hash`: Firma o Hash criptográfico inyectado en el enlace para contrastar la identidad.

#### Flujo Lógico Interno

1. **Búsqueda en Base de Datos (ODBC):** Realiza una consulta directa y optimizada mediante `selectOne` aplicando la sintaxis `TOP 1` sobre la tabla corporativa `DBA.pw_ge_usuarios` filtrando por el `$id`.
2. **Validación de Integridad:** Compara el parámetro `$hash` recibido en la petición contra el resultado de aplicar la función criptográfica nativa `sha1()` al correo guardado en el registro (`sha1($user->email)`). Si el usuario no existe o el hash es incorrecto, aborta y redirige al login con un mensaje de enlace inválido.
3. **Control de Doble Verificación:** Evalúa si la columna `email_verified_at` ya posee una fecha registrada. Si no es nula, interrumpe el flujo y redirige al login indicando que la cuenta ya se encontraba activa.
4. **Persistencia del Estado:** Si pasa las pruebas previas, ejecuta una sentencia `UPDATE` por medio de la conexión ODBC asignando la marca de tiempo exacta del servidor mediante `Carbon::now()`.

#### Matriz de Redirecciones y Mensajes Flash

| Escenario de Entrada | Condición Evaluada | Destino de Redirección | Tipo de Alerta / Llave | Mensaje Retornado |
| :--- | :--- | :--- | :--- | :--- |
| **Falla de Token / ID** | El usuario no existe o el hash no coincide con `sha1($email)`. | `/login` | `withErrors(['email'])` | *"Enlace inválido o expirado."* |
| **Verificación Duplicada**| La columna `email_verified_at` no es `NULL`. | `/login` | `with('success')` | *"Tu correo ya estaba verificado."* |
| **Activación Exitosa** | El hash es correcto y el registro estaba pendiente. | `/login` | `with('success')` | *"Correo verificado correctamente. Ya puedes iniciar sesión."* |

---

##  Consideraciones de Seguridad y Arquitectura

* **Aislamiento en Consultas:** Al interactuar mediante `DB::connection('odbc')`, el sistema garantiza la compatibilidad con el esquema corporativo transaccional externo (`DBA`), abstrayendo a los modelos tradicionales de Eloquent de tareas operativas de infraestructura.
* **Mitigación de Spoofing / Fuerza Bruta:** Al requerir de forma estricta el match entre el `$id` y el `sha1($email)`, se bloquean ataques automatizados en los cuales un tercero intente activar cuentas modificando de forma secuencial los IDs de la URL, ya que le sería imposible adivinar el hash SHA-1 del correo vinculado a dicho registro.
