## Modelos

El proyecto utiliza modelos de Eloquent en `app/Models/` para mapear de forma ordenada las tablas de la base de datos externa.

---

### `BancoCuenta`

Este modelo gestiona la información de las cuentas bancarias de las empresas que están registradas en el sistema.

* **¿Cómo funciona internamente?:** Se conecta a la tabla `te_cuentas_bancos`. Como es una base de datos externa, tiene desactivados los timestamps automáticos de Laravel (`created_at` y `updated_at`) y define qué campos se pueden llenar de forma masiva (Mass Assignment) a través del arreglo `$fillable`.
* **¿Qué campos maneja? (Propiedades principales):**
  - `cod_sistema`: Identificador del sistema.
  - `empresa`: Código de la empresa a la que pertenece la cuenta.
  - `descripcion`: Nombre descriptivo de la cuenta (ej. "Banco Pichincha Corriente").
  - `tipo` / `moneda` / `banco` / `cuenta`: Datos específicos de la entidad financiera.
  - `cta_contable`: Código de la cuenta contable asociada para la sincronización.
  - `formato_web` / `view_on_comprobante`: Banderas de control para visualización en el frontend y en los comprobantes.

### `CarritoModel`

Este modelo gestiona el ciclo de vida de los productos agregados a los carritos de compra de los usuarios en el sitio web. A diferencia de un modelo Eloquent estándar, utiliza consultas SQL directas sobre la conexión ODBC para interactuar con tablas del esquema de SQL Anywhere.

* **¿Cómo funciona internamente?:** Se conecta explícitamente a la tabla `DBA.pw_carrito_web` mediante la conexión `odbc`. Expone constantes de estado (`ESTATUS_ACTIVO = '1'`, `ESTATUS_PROCESANDO = '2'`) y utiliza consultas crudas (`DB::select`, `DB::selectOne`, `DB::insert`) optimizadas para la sintaxis de Sybase. Cuenta con un mapeador interno (`mapRowToInstance`) que toma los registros nativos de la base de datos, castea sus tipos de datos (como pasar precios a floats formateados o cantidades a enteros) e inyecta dinámicamente las URLs de las imágenes resolviendo si el producto se muestra en su presentación estándar o como una variante.

* **Métodos principales:**
  - `getCarritoByUser(string $codCliente)`: Recupera todos los ítems activos en el carrito de un usuario, realizando un `LEFT JOIN` con la tabla de presentaciones (`DBA.in_item_presentacion`) de la empresa actual.
  - `getItemByProducto(...)` / `getItemById(...)`: Buscan un ítem específico en el carrito utilizando la cláusula `SELECT TOP 1` propia de SQL Anywhere.
  - `getStockDisponible(...)`: Consulta de forma dinámica los saldos físicos del producto. Si posee una presentación especial, suma las existencias de la tabla `DBA.in_existencia_presentacion`; si es un producto estándar, consulta `DBA.in_existencia`. Permite filtrar opcionalmente por una ubicación o bodega específica.
  - `add(array $data)`: Inserta un nuevo ítem en la tabla del carrito forzando el estatus inicial en `'1'` (Activo).

### `Cliente`

Este modelo gestiona la información de los perfiles de los clientes en el sistema, conectando la plataforma web con la tabla maestra de clientes en el Core del negocio.

* **¿Cómo funciona internamente?:** Extiende directamente de Eloquent (`Model`), lo que permite usar todas las ventajas del ORM de Laravel, pero redirige las consultas a la base de datos externa mediante la conexión `odbc` apuntando a la tabla `DBA.in_cliente`. Define explícitamente el campo `codigo` como su llave primaria (Primary Key), desactiva los timestamps de Laravel, y restringe la escritura masiva únicamente a los campos declarados en su arreglo `$fillable`.

* **Relaciones integradas:**
  - `usuario()`: Define una relación inversa de tipo "pertenece a" (`belongsTo`) con el modelo `login_model`. Vincula la llave local `codigo` del cliente con la columna externa `pw_codigo` en la tabla de credenciales de acceso.

### `Empresa`

Este modelo gestiona los parámetros de configuración, datos de contacto e identidad corporativa de las empresas registradas en la plataforma.

* **¿Cómo funciona internamente?:** Extiende de Eloquent (`Model`) pero está adaptado para interactuar con el entorno heredado (Legacy). Se conecta mediante `odbc` a la tabla `ge_empresa` y define de forma estricta que su llave primaria (`codigo`) es de tipo `string` y no es autoincremental. Tiene los timestamps desactivados y restringe la edición masiva mediante el arreglo `$fillable` a campos específicos de contacto (correos, direcciones y teléfonos).

* **Métodos estáticos:**
  - `getNombre()`: Resuelve el nombre comercial de la empresa configurada para la aplicación. 
    * **¿Cómo funciona internamente?:** Toma el código de empresa actual desde la configuración (`app.company_code`) y le aplica un relleno de ceros a la izquierda usando `str_pad` para formatearlo siempre a un ancho fijo de 3 caracteres (por ejemplo, transforma `"1"` en `"001"`). Luego, ejecuta una consulta SQL directa con la sintaxis `SELECT TOP 1` sobre la tabla `DBA.ge_empresa`.
    * **¿Qué devuelve?:** Un `string` con el nombre de la empresa recuperada de la base de datos. Si no encuentra ningún registro que coincida con el código formateado, devuelve el nombre general de la aplicación configurado en el sistema (`config('app.name')`) como plan de respaldo.

### `FormaPago`

Este modelo gestiona los diferentes métodos y modalidades de pago (efectivo, transferencias, tarjetas, etc.) que el sistema tiene habilitados para las transacciones comerciales.

* **¿Cómo funciona internamente?:** Extiende de Eloquent (`Model`) para mapear la tabla `cxc_forma_pago`. Define explícitamente el campo `secuencia` como su llave primaria y mantiene desactivados los timestamps automáticos de Laravel (`created_at` y `updated_at`). Permite la asignación masiva a través del arreglo `$fillable` para columnas clave como el nombre de la forma de pago, la empresa, el tipo, la cuenta asociada y códigos de integración (como el formato XML de facturación electrónica o la cuenta bancaria del sistema).

* **Campos del modelo:**
  - `secuencia`: Llave primaria que identifica la configuración de pago.
  - `forma_pago` / `tipo`: Nombre descriptivo y clasificación interna del método de pago.
  - `empresa` / `cuenta`: Filtros de asignación para saber a qué entidad y cuenta contable/bancaria afecta la transacción.
  - `xml_forma_pago`: Código del método de pago homologado para la estructura XML (útil para la emisión de comprobantes electrónicos).
  - `cod_cuenta_banco`: Propiedad pública declarada en la clase que se incluye además en el `$fillable` para gestionar la relación lógica con los bancos de la aplicación.

### `login_model`

Este modelo implementa la interfaz de autenticación nativa de Laravel para permitir el inicio de sesión y manejo de sesiones de los usuarios utilizando los datos alojados en el servidor externo.

* **¿Cómo funciona internamente?:** En lugar de extender de Eloquent, implementa directamente el contrato `AuthenticatableContract` e incluye el trait `Authenticatable`. Esto le permite integrarse con el sistema de seguridad (Auth guard) de Laravel. Define que la conexión a utilizar es de tipo `odbc` y declara propiedades públicas para mapear manualmente las columnas del usuario sin depender del mapeo automático del ORM.

* **Métodos de autenticación:**
  - `getAuthIdentifierName()`:  
    * **¿Qué devuelve?:** Un `string` con el nombre del campo que actúa como identificador único de usuario (`'user_id'`).
  - `getAuthIdentifier()`:  
    * **¿Qué devuelve?:** El valor del identificador real del usuario en sesión (`$this->user_id`).
  - `getAuthPassword()`:  
    * **¿Qué devuelve?:** Un `string` con la contraseña cifrada recuperada del atributo `$this->contrasena`.
  - `getAuthPasswordName()`:  
    * **¿Qué devuelve?:** Un `string` con el nombre de la columna donde el sistema almacena la clave (`'contrasena'`).

* **Métodos estáticos:**
  - `mapRowToInstance($row)`:  
    * **¿Cómo funciona internamente?:** Instancia un nuevo objeto de la clase y transfiere de manera manual cada columna devuelta por la consulta de la base de datos externa a los atributos correspondientes (nombre, email, empresa, cédula, dirección, etc.).  
    * **¿Qué devuelve?:** Una nueva instancia de `login_model` completamente cargada con los datos del usuario, lista para ser autenticada por el framework.

### `Parametro`

Este modelo gestiona las variables globales, configuraciones internas y parámetros operacionales de la plataforma web.

* **¿Cómo funciona internamente?:** Extiende de Eloquent (`Model`) para mapear la tabla `pw_parametros`. Define explícitamente el campo `codigo` como su llave primaria (Primary Key) y mantiene desactivados los timestamps automáticos de Laravel (`created_at` y `updated_at`). Habilita la asignación masiva a través del arreglo `$fillable` únicamente para las columnas de control, descripción, detalles específicos y el identificador de la empresa.

* **Campos del modelo:**
  - `codigo`: Llave primaria que identifica de forma única cada parámetro del sistema.
  - `parametro`: Nombre clave de la variable de configuración (ej. `"IVA_PORCENTAJE"` o `"URL_TIENDA"`).
  - `descripcion`: Resumen corto sobre el propósito de la variable.
  - `detalle`: El valor real o la configuración asignada al parámetro.
  - `empresa`: Código de la empresa asociada a la que aplica dicha configuración.

### `PasswordReset`

Este modelo gestiona los tokens de seguridad generados para los procesos de recuperación y restablecimiento de contraseñas de los usuarios.

* **¿Cómo funciona internamente?:** Extiende de Eloquent (`Model`) para interactuar con la tabla `pw_password_resets`. Al trabajar con una estructura de base de datos externa, define una llave primaria compuesta basada en los campos `['empresa', 'codigo']`, lo que obliga a desactivar la propiedad de auto-incremento (`$incrementing = false`). También desactiva los timestamps automáticos de Laravel ya que la fecha de creación se maneja de forma manual a través del campo `created_at` dentro del arreglo `$fillable`.

* **Campos del modelo:**
  - `empresa`: Código de la empresa a la que pertenece el usuario que solicita el cambio.
  - `codigo`: Identificador o código interno del usuario asociado a la solicitud.
  - `email`: Correo electrónico a donde se envía el enlace de recuperación.
  - `token_hash`: Hash de seguridad único generado para validar la autenticidad y vigencia de la solicitud.
  - `created_at`: Fecha y hora exacta en la que se creó el token para controlar su tiempo de expiración de forma manual.

### `ProductPresentation`

Este modelo gestiona las diferentes variantes o presentaciones de los productos (por ejemplo: tallas, colores o empaques), controlando la disponibilidad de sus inventarios individuales y resolviendo la codificación de caracteres heredada desde la base de datos externa.

* **¿Cómo funciona internamente?:** Actúa de forma híbrida mediante consultas crudas utilizando el driver `odbc` sobre la tabla `DBA.in_item_presentacion`. Declara atributos públicos para mapear las características físicas de las variantes y expone una lógica avanzada para calcular inventarios dependiendo de si el usuario en el sitio web ha seleccionado una ubicación (bodega) física o si navega en la tienda pública como invitado.

* **Métodos principales:**

  - `getByProduct(string $codigoProducto, string $empresa = null, ?int $limit = null, string $ubicacion = '')`:  
    * **¿Cómo funciona internamente?:** Primero extrae los datos del producto base usando un repositorio y el modelo `ProductsModel`. Luego evalúa la procedencia del usuario: si se pasa una ubicación específica (usuario logueado), altera dinámicamente la consulta para filtrar existencias (`SUM(e.cantidad)`) únicamente en esa sucursal; si está vacío (visitante), genera un subquery que acumula los stocks de todas las bodegas que tienen la bandera `view_on_tienda = 'S'`. Finalmente, calcula un stock consolidado total cruzando datos entre tablas estándar y de variantes.
    * **¿Qué devuelve?:** Un `array` asociativo que unifica la ficha técnica del producto base junto con un sub-arreglo indexado (`'presentaciones'`) que contiene los objetos instanciados de cada variante con existencias disponibles mayores a cero.

  - `mapRowToInstance($row, string $codigoProducto)`:  
    * **¿Cómo funciona internamente?:** Transfiere de manera manual cada columna de la fila devuelta por la base de datos a una nueva instancia de la clase. Invoca al método de limpieza de texto para el campo `nombre` y procesa el helper global `presentationImageUrl()` para adjuntar la URL pública de la imagen de la variante.
    * **¿Qué devuelve?:** Una instancia limpia y tipada del objeto `ProductPresentation`.

  - `cleanString(?string $value)`:  
    * **¿Cómo funciona internamente?:** Verifica si el texto recibido está vacío o nulo. Reemplaza caracteres rotos (``), saltos de línea y tabulaciones por espacios en blanco. Posteriormente, intenta forzar la conversión de codificación de caracteres desde el formato heredado `Windows-1252` hacia el estándar `UTF-8` usando las funciones `@mb_convert_encoding` o `@iconv` como plan de respaldo en caso de fallo.
    * **¿Qué devuelve?:** Un `string` completamente limpio, sanitizado y con caracteres legibles para los navegadores de internet.

### `ProductsModel`

Este modelo gestiona los datos de los productos base en el catálogo de la tienda, encargándose de la estandarización de precios, la resolución de sus imágenes y la persistencia de cambios en el inventario del Core.

* **¿Cómo funciona internamente?:** Trabaja como un modelo personalizado sobre la conexión `odbc` para interactuar con la tabla `DBA.in_item`. Define propiedades públicas para estructurar la información del producto (precios, stock, categorías, etc.) y no depende del ciclo de vida tradicional de Eloquent, ejecutando sentencias SQL puras tanto para la lectura (mediante su mapeador) como para la escritura.

* **Métodos principales:**

  - `mapRowToInstance($row)`:  
    * **¿Cómo funciona internamente?:** Toma el objeto crudo devuelto por una consulta a la base de datos y lo transfiere a una nueva instancia tipada. Durante este proceso, formatea el precio (`pvp1`) a un `float` de dos decimales fijos, asegura que el indicador de IVA tenga un valor por defecto (`'N'`), evalúa si cuenta con variantes convirtiendo el campo a un booleano, e invoca al helper global `productImageUrl()` para adjuntar la URL pública del recurso multimedia.
    * **¿Qué devuelve?:** Una instancia limpia de `ProductsModel` lista para ser enviada al frontend o consumida por controladores.

  - `cleanString(?string $value)`:  
    * **¿Cómo funciona internamente?:** Comprueba si el texto de entrada está vacío o es nulo. Filtra y limpia caracteres extraños (como signos de interrogación huérfanos), retornos de carro y tabulaciones cambiándolos por espacios. Posteriormente, intenta decodificar el formato heredado `Windows-1252` hacia codificación universal `UTF-8` utilizando `@mb_convert_encoding` o `@iconv` como alternativa segura.
    * **¿Qué devuelve?:** Un `string` sanitizado, sin espacios redundantes en los extremos y legible.

  - `updateProduct($codigo, $empresa, $data)`:  
    * **¿Cómo funciona internamente?:** Ejecuta una sentencia cruda de actualización `UPDATE` sobre la tabla maestra `DBA.in_item` apuntando a la empresa y código del producto pasados por argumento. Modifica de forma directa las columnas `descripcion1`, `pvp1` y `stock` mapeándolos desde el arreglo `$data`.
    * **¿Qué devuelve?:** Un entero (`int`) que representa la cantidad de filas afectadas por la consulta SQL en la base de datos.

### `WishListModel`

Este modelo gestiona la lista de deseos o productos favoritos de los usuarios, permitiéndoles guardar ítems del catálogo para consultas o compras futuras.

* **¿Cómo funciona internamente?:** Trabaja como una clase personalizada estructurada para la conexión `odbc` apuntando a la tabla `DBA.pw_wishlist`. Define un conjunto de propiedades públicas que representan los atributos del recurso guardado (códigos, detalles del producto, precios, stock y fechas). No hace uso del ORM de Eloquent y se apoya en un mapeador manual para transformar los registros de la base de datos externa en objetos de la aplicación.

* **Métodos principales:**

  - `mapRowToInstance($row)`:  
    * **¿Cómo funciona internamente?:** Recibe la fila cruda de la base de datos y transfiere manualmente cada columna a los atributos públicos de una nueva instancia. Reutiliza el método estático `cleanString()` del modelo `ProductsModel` para garantizar la correcta codificación del nombre del producto, castea el inventario a un valor decimal (`float`), formatea el precio (`pvp3`) a dos decimales y convierte la bandera de variantes en un valor booleano. Además, procesa el helper global `productImageUrl()` para estructurar el enlace absoluto de la imagen del ítem.
    * **¿Qué devuelve?:** Una instancia completamente cargada del objeto `WishListModel`, lista para ser expuesta en la interfaz de la cuenta del usuario o APIs del catálogo.
