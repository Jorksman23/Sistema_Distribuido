# Sistema Distribuido

Proyecto en **Laravel 12** con conexión a **SQL Anywhere** mediante **ODBC 17**.  

##  Requisitos previos
- PHP >= 8.1
- Composer
- Laravel 12
- Driver ODBC 17 instalado y configurado
- Extensión `odbc` habilitada en PHP
    
## Instalación de dependencias
1. Instalar paquetes de Laravel:
   ```bash
   composer install
## Instalar dependencias de frontend
2. npm install
3. Verificar que la carpeta vendor/ se generó correctamente con Composer
   
## Instalar paquetes del servicio de correo y Pdf
- composer require barryvdh/laravel-dompdf:^3.1 getbrevo/brevo-php:^4.0 guzzlehttp/guzzle:^7.10 laravel/framework:^12.58 laravel/sanctum:^4.3 livewire/livewire:^4.3 yoramdelangen/laravel-pdo-odbc:^2.0 && composer require --dev fakerphp/faker:^1.24 laravel/pail:^1.2 laravel/pint:^1.29 laravel/sail:^1.58 laravel/tinker:^2.11 mockery/mockery:^1.6 nunomaduro/collision:^8.9 phpunit/phpunit:^11.5

## Configurar la conexion a la base de datos en el archivo .env
DB_CONNECTION=odbc
DB_DSN=SQLAnywhere_DSN
DB_USERNAME=usuario
DB_PASSWORD=contraseña

## Configuracion de empresa y servidor de imagenes en el archivo .env
La aplicación permite cambiar de empresa editando el archivo `.env`.
COMPANY_CODE=(Cambiar por el codigo de su empresa existente en su base de datos)
## Servidor de imagenes
IMAGE_SERVER_BASE_URL=(Direccion de su servidor de imagenes)
IMAGE_SERVER_PATH_PRODUCTS=(Palabra que va concatenado para acceder a la imagenes de  su servidor)

# Token de subida (si lo necesita)
IMAGE_SERVER_UPLOAD_TOKEN=(Subir imagen si desea)


## Helpers de empresa e imágenes

El proyecto incluye funciones auxiliares en `app/Helpers/CompanyHelper.php` para manejar la empresa actual y la construcción de URLs de imágenes.

### Funciones principales

- **currentCompany()**  
  Devuelve el código de la empresa actual desde la configuración (`COMPANY_CODE` en `.env`).

- **companyRuc()**  
  Obtiene el RUC de la empresa desde la base de datos `GE_EMPRESA` usando conexión ODBC a SQL Anywhere.  
  - Usa `cache()` para almacenar el resultado por 12 horas.  
  - Consulta con `SELECT TOP 1 ruc FROM GE_EMPRESA WHERE codigo = ?`.

- **companyImageBaseUrl()**  
  Construye la URL base para las imágenes de productos:  

{IMAGE_SERVER_BASE_URL}/{RUC}/{IMAGE_SERVER_PATH_PRODUCTS}/


- **productImageUrl($filename)**  
Devuelve la URL completa de la imagen de un producto.  
- Si el nombre de archivo está vacío, retorna `null`.

- **presentationImageUrl($filename, $codigoProducto = null)**  
Devuelve la URL de la imagen de una presentación de producto.  
- Si se pasa `$codigoProducto`, se añade como subcarpeta.  
- Ejemplo: `/RUC/producto/123/imagen.jpg`.

- **companyDefaultOrderType($tipo = 'proforma_web')**  
Retorna el código de documento por defecto según el tipo:  
- `proforma_web` → `TW`  
- `invoice` → `FC`

## Modelo: CarritoModel

El modelo `CarritoModel` gestiona la información del carrito de compras en la base de datos **SQL Anywhere** (tabla `DBA.pw_carrito_web`) usando conexión **ODBC**.

### Propiedades principales
- `id_item_web`, `codigo_item`, `nombre`, `costo_real`, `pvp3`, `cantidad`, `cod_cliente`
- `imagen`, `imagen_url`, `estatus`, `iva`, `presentacion`, `nombre_presentacion`, `ubicacion`

### Constantes
- `ESTATUS_ACTIVO = '1'`
- `ESTATUS_PROCESANDO = '2'`

### Métodos principales
- **getCarritoByUser($codCliente)** → devuelve todos los items activos del carrito de un cliente, incluyendo nombre de presentación e imagen.  
- **getItemByProducto($codCliente, $codigoItem, $presentacion)** → obtiene un producto específico del carrito.  
- **getItemById($idItemWeb, $codCliente)** → obtiene un item por su ID.  
- **getStockDisponible($codigoItem, $presentacion, $empresa, $ubicacion)** → calcula stock disponible desde tablas de existencia.  
- **add($data)** → inserta un nuevo producto en el carrito.  
- **mapRowToInstance($row)** → transforma una fila de la BD en una instancia del modelo, construyendo la URL de imagen según presentación o producto.

### Notas
- Usa `DB::connection('odbc')` para consultas en SQL Anywhere.  
- Integra helpers como `productImageUrl()` y `presentationImageUrl()` para construir URLs de imágenes.  

## Repositorio: CartRepository

El repositorio `CartRepository` encapsula el acceso directo a la tabla `DBA.pw_carrito_web` en **SQL Anywhere** usando conexión **ODBC**.  
Su objetivo es centralizar las operaciones CRUD del carrito de compras.

### Métodos principales
- **getByUser($codCliente)** → devuelve todos los items activos del carrito de un cliente.  
- **exists($codCliente, $codigoItem, $presentacion, $ubicacion)** → verifica si un producto ya existe en el carrito.  
- **delete($idItemWeb, $codCliente)** → elimina un producto específico del carrito.  
- **updateCantidad($idItemWeb, $codCliente, $cantidad)** → actualiza la cantidad de un producto en el carrito.  
- **vaciar($codCliente)** → elimina todos los productos activos del carrito de un cliente.  
- **getTotal($codCliente)** → calcula el total del carrito multiplicando precio (`pvp3`) por cantidad.  
- **count($codCliente)** → devuelve el número de items en el carrito.  
- **marcarComoProcesado($codCliente, $ordenId)** → cambia el estado del carrito a procesado (`estatus = '2'`) y asigna un ID de orden.

### Notas
- Todas las consultas se realizan con `DB::connection('odbc')`.  
- Usa `SELECT TOP 1` en lugar de `LIMIT`, ya que SQL Anywhere requiere esa sintaxis.  
- Este repositorio se utiliza desde el **CarritoController** y/o servicios para manejar la lógica de negocio.

##  Servicio: CheckoutService

El servicio `CheckoutService` centraliza la lógica de negocio del **checkout** del carrito de compras.  
Se apoya en el `CarritoModel` y el `CartRepository` para obtener datos y calcular totales.

### Dependencias
- **CarritoModel** → para obtener items del carrito.  
- **CartRepository** → para contar items y manejar operaciones básicas.  
- **DB::connection('odbc')** → para consultar parámetros de IVA en SQL Anywhere.  
- **Http::get()** → para consumir servicio externo de clientes.

### Métodos principales
- **obtenerCheckout($codCliente)**  
  - Devuelve items del carrito, subtotal, IVA, total y cantidad de productos.  
  - Calcula IVA dinámicamente según parámetros de la empresa (`GE_PARAMETROS`).  

- **obtenerDatosCliente($cedula)**  
  - Consulta servicio externo (`http://186.101.203.79:2001/persona/{cedula}`).  
  - Si faltan datos, consulta tablas locales (`in_cliente`, `pw_ge_usuarios`).  
  - Fusiona datos externos + locales para devolver información completa del cliente.  
  - Campos devueltos: `cedula_ruc`, `nombre`, `direccion`, `telefono`, `email`, `origen`.

### Notas
- Usa `SELECT TOP 1` en lugar de `LIMIT` por compatibilidad con SQL Anywhere.  
- Maneja errores con `try/catch` y devuelve mensajes claros en caso de fallo.  
- Este servicio es utilizado por el **CarritoController** para responder al frontend.

##  Controlador: CarritoController

El `CarritoController` coordina la lógica del **carrito de compras**, conectando el `CarritoModel`, `CartRepository`, `CheckoutService`, `PaymentService` y las vistas Blade.

### Dependencias inyectadas
- **CarritoModel** → acceso a datos del carrito.
- **CartRepository** → operaciones CRUD sobre la tabla `pw_carrito_web`.
- **CartService** → lógica de negocio del carrito (resumen, agregar, actualizar, eliminar).
- **CheckoutService** → cálculo de totales, IVA y datos del cliente.
- **PaymentMethodService** → gestión de formas de pago.
- **PaymentService** → procesamiento de pagos.
- **OrderRepository** → acceso a órdenes y sus items.
- **CxcAuxiliarProformaService** → registro auxiliar de proformas.
- **ComprobanteService** → manejo de comprobantes de pago.
- **Barryvdh\DomPDF** → generación de PDFs de pedidos.

### Métodos principales
- **index()** → muestra el carrito del cliente en la vista `cart.index`.  
- **add(Request $request)** → agrega un producto desde el catálogo al carrito.  
- **update(Request $request)** → actualiza la cantidad de un producto en el carrito.  
- **remove(Request $request)** → elimina un producto específico del carrito.  
- **vaciar()** → vacía el carrito completo del cliente.  
- **pagar()** → prepara el checkout y muestra las formas de pago en la vista `pedidos.pagar`.  
- **obtenerDatosCliente(Request $request)** → devuelve datos del cliente (fusionando servicio externo + BD local).  
- **procesarPago(Request $request)** → valida datos y delega el procesamiento al `PaymentService`.  
- **obtenerCuentaBanco($secuencia)** → obtiene datos bancarios asociados a una forma de pago.  
- **mostrarComprobante()** → muestra comprobante de pago desde sesión.  
- **guardarComprobante(Request $request)** → valida y guarda comprobante de pago en BD y servidor.  
- **descargarPedido($codigo)** → genera y descarga PDF del pedido con items y forma de pago.

### Notas
- Maneja validaciones de entrada con `Request->validate()`.  
- Usa `session('user_id')` para identificar al cliente actual.  
- Maneja errores con `try/catch` y devuelve vistas de error (`errors.500`) cuando es necesario.  
- Genera comprobantes y pedidos en PDF usando **DomPDF**.

## Servicio: ComprobanteService

El servicio `ComprobanteService` gestiona la lógica relacionada con los **comprobantes de pago** dentro del flujo del carrito.  
Se integra con repositorios y servicios para registrar órdenes, adjuntos y movimientos auxiliares.

### Dependencias
- **CartRepository** → marcar carrito como procesado.  
- **OrderRepository** → generar código de orden y obtener items.  
- **PaymentMethodService** → obtener formas de pago y cuentas bancarias.  
- **CxcAuxiliarProformaService** → registrar auxiliar de proformas.  
- **DB::connection('odbc')** → operaciones en SQL Anywhere.  
- **Storage** → guardar archivos de comprobantes en disco.

### Métodos principales
- **mostrarComprobante($checkoutData)**  
  - Muestra la vista `pedidos.comprobante` con la forma de pago y cuenta bancaria.  
  - Si no hay datos de pago, redirige a `pedidos.pagar`.

- **guardarComprobante($archivo, $checkoutData, $codCliente, $empresa, $items, $granTotal)**  
  - Genera un código de orden y guarda la información en `PW_ORDENES_WEB`.  
  - Registra auxiliar proforma con `CxcAuxiliarProformaService`.  
  - Guarda el archivo en `storage/app/public/comprobantes/{empresa}`.  
  - Inserta registros en `PW_ADJUNTO_WEB` y `PW_HISTORICO_PEDIDO`.  
  - Marca el carrito como procesado (`estatus = '2'`).  
  - Maneja transacciones con `beginTransaction()` y `commit()`.  
  - En caso de error, hace `rollBack()` y elimina el archivo subido.

### Notas
- Usa `companyDefaultOrderType('invoice')` para definir tipo de documento.  
- Limpia variables de sesión (`checkout_data`, `carrito_count`, `carrito_ubicacion`) al finalizar.  
- Devuelve mensajes claros de éxito o error al usuario.

##  Vista: carrito.blade.php

La vista `carrito.blade.php` es la interfaz principal del **carrito de compras**.  
Se encuentra en `resources/views/cart/index.blade.php` y recibe datos desde el `CarritoController`.

### Contenido principal
- **Encabezado** → título *Carrito de compras*, contador de productos y botón *Volver*.  
- **Mensajes de estado** → muestra alertas de éxito (`success_cart`) y errores (`errors`).  
- **Estado vacío** → si no hay productos, muestra mensaje y botón para ir al catálogo.  
- **Lista de productos** → renderiza cada item con:
  - Imagen (`$item->imagen_url`)  
  - Nombre y presentación  
  - Precio unitario y controles de cantidad (+ / −)  
  - Botón para eliminar producto  
- **Acciones globales** → botón para vaciar el carrito completo.  
- **Resumen de compra** → subtotal, IVA y total calculados con datos del `CheckoutService`.  
- **Botones de navegación**:
  - *Proceder con el pago* → redirige a `pedidos.pagar`.  
  - *Seguir comprando* → redirige al catálogo.

### Notas
- Usa **Tailwind CSS** para estilos responsivos y modernos.  
- Integra formularios con `@csrf` y métodos HTTP (`PUT`, `DELETE`) para actualizar/eliminar items.  
- Controla errores y estados vacíos directamente en la vista.  
- Se apoya en variables pasadas desde el controlador: `$items`, `$count`, `$subtotal`, `$iva`, `$total`.
