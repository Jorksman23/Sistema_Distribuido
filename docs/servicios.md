## Services

El proyecto implementa una capa de **Servicios** dedicada para separar la lógica de negocio y las integraciones con proveedores externos de los controladores y modelos del sistema.

### ¿Por qué el proyecto utiliza Services?
* **Desacoplamiento e Integración Externa:** Aísla el consumo de APIs de terceros (como pasarelas de pago o plataformas de mensajería) del resto del sistema. Esto evita que los controladores dependan directamente de librerías de clientes HTTP como Guzzle.
* **Transición Transparente de Entornos:** Facilita que el paso de un entorno de desarrollo local a un servidor de producción con un dominio corporativo propio sea completamente transparente. Solo se requiere modificar las credenciales en el archivo `.env` sin alterar el código fuente.
* **Reutilización y Mantenimiento:** Centraliza procesos globales (como el envío de notificaciones, confirmaciones de órdenes o tokens de reseteo). Si en el futuro se decide cambiar de proveedor de mensajería, el cambio se realiza en un único archivo sin afectar el flujo de la aplicación.

---

### `BrevoMailer`

El servicio `BrevoMailer` gestiona el envío de correos electrónicos transaccionales integrando la API REST v3 de **Brevo** mediante el cliente HTTP **Guzzle**. Actualmente se encuentra parametrizado para pruebas locales mediante variables de entorno, quedando preparado para el escalado a producción con dominio corporativo propio.

### Características técnicas
* **Cliente HTTP:** Utiliza `GuzzleHttp\Client` inicializado con la URL base de Brevo y las cabeceras de autenticación requeridas.
* **Seguridad:** Centraliza las credenciales críticas y los datos del remitente a través del archivo de configuración de entorno `.env`.
* **Estrategia de Entorno:** Permite alternar entre pruebas y producción mapeando las variables del remitente del sistema de forma dinámica.

### Métodos principales

* **__construct()** Inicializa la instancia de Guzzle configurando la URI base de la API (`https://api.brevo.com/v3/`) e inyectando las cabeceras globales obligatorias, incluyendo la clave de acceso privada `api-key` recuperada mediante `env('BREVO_API_KEY')`.

* **sendEmail(string $to, string $subject, string $htmlContent)** Realiza una petición `POST` al endpoint `smtp/email`. Estructura el payload JSON con la información del remitente (`sender`), la dirección del destinatario (`to`), el asunto del mensaje (`subject`) y el cuerpo estructurado en formato web (`htmlContent`). Procesa la respuesta de la API de Brevo y retorna un arreglo asociativo con el estado del envío.

### `CartService`

El servicio `CartService` centraliza e instrumenta la lógica de negocio del carrito de compras. Actúa como capa intermedia coordinando validaciones complejas de control de existencias físicas por ubicación, mapeo de precios y el desglose de impuestos dinámicos multiempresa.

### Características técnicas
* **Control de Stock en Tiempo Real:** Realiza validaciones estrictas cruzando las cantidades agregadas con la disponibilidad real del producto según la sucursal seleccionada en la sesión activa.
* **Cálculo de Impuestos Dinámico:** Consulta parámetros dinámicos de configuración corporativa de la base de datos externa para determinar la tasa impositiva aplicable y el algoritmo de desglose de IVA (con IVA incluido o adicionado encima).

### Métodos principales

* **__construct(CarritoModel $carrito, CartRepository $cartRepository)** Inyecta las dependencias del modelo de carrito y el repositorio de control para interactuar con la persistencia de datos.

* **agregarProducto(array $data, string $codCliente)** Añade un artículo al carrito de un usuario. Extrae la ubicación seleccionada de la sesión y verifica si el ítem (considerando su presentación o variante) ya existe en la cesta:
  * **Si ya existe:** Consulta el stock real disponible en la sucursal activa. Si la cantidad actual iguala o supera el inventario, dispara una excepción; en caso contrario, incrementa la cantidad en `+1`.
  * **Si es nuevo:** Consulta el catálogo maestro de productos para recuperar descripciones, precios por defecto (`pvp1`) e imágenes secundarias, insertando el registro inicializado con cantidad `1`.

* **actualizarCantidad(int $idItemWeb, int $cantidad, string $codCliente)** Modifica directamente el volumen de compra de un registro específico en el carrito. Evalúa las existencias físicas en la sucursal actual y restringe la actualización disparando una excepción en caso de que el valor solicitado exceda el stock real en percha.

* **eliminarProducto(int $idItemWeb, string $codCliente)** Remueve de manera unívoca un artículo de la cesta del usuario utilizando su ID de control secuencial.

* **vaciarCarrito(string $codCliente)** Limpia por completo todos los productos activos asociados al identificador del cliente en sesión.

* **obtenerResumenCarrito(string $codCliente)** Genera el balance contable final y estados financieros del carrito de compras. Realiza consultas dinámicas multiempresa sobre las tablas de control del motor:
  * **Porcentaje de IVA:** Recupera el valor impositivo activo desde la tabla `DBA.GE_PARAMETROS` (código `17`).
  * **Modo de Trabajo con IVA:** Consulta la bandera en `DBA.web_ge_parametros` (código `248`) para procesar las operaciones comerciales bajo dos modalidades:
    * **`S` (No incluye IVA):** Calcula el subtotal neto y le suma la tasa del impuesto encima.
    * **`N` (IVA Incluido):** Aplica la fórmula de desglose de tasas inversas para extraer el subtotal y el impuesto proporcional correspondiente del precio bruto.
  
  Retorna una matriz estructurada con el arreglo de ítems procesados, subtotal, IVA acumulado, total bruto final formateado a dos decimales y contadores globales.

### `CheckoutService`

El servicio `CheckoutService` coordina la fase final de la compra web preparando los datos del pedido y procesando las obligaciones tributarias de manera idéntica al motor del carrito de compras para blindar la consistencia financiera antes de la facturación.

### Características técnicas
* **Centralización de Totales:** Recopila las colecciones de productos y contadores en un único punto antes de iniciar la transacción de pago.
* **Algoritmo de Impuestos Homologado:** Replica el esquema dinámico multiempresa de desgloses y recargos impositivos sobre la base de datos externa Sybase/SQL Anywhere para evitar discrepancias de centavos entre lo que ve el cliente y lo que almacena el ERP.

### Métodos principales

* **__construct(CarritoModel $carrito, CartRepository $cartRepository)** Inyecta las dependencias necesarias para leer los registros actuales de la compra y auditar las cantidades activas del usuario.

* **obtenerCheckout(string $codCliente)** Procesa, consolida y retorna el balance financiero detallado para la pantalla de pago del cliente. Ejecuta las siguientes acciones lógicas:
  * **Carga de Datos:** Recupera los ítems de la cesta y consulta la cantidad exacta de registros activos a través del repositorio.
  * **Auditoría de Parámetros Multiempresa:** Extrae mediante la conexión ODBC el porcentaje impositivo vigente (`DBA.GE_PARAMETROS`, código `17`) y la bandera de comportamiento fiscal (`DBA.web_ge_parametros`, código `248`).
  * **Evaluación de Escenarios Fiscales:** Evalúa fila por fila aplicando las siguientes reglas de negocio:
    * **Caso de Recargo Impositivo (`248 = 'S'` y el ítem grava IVA):** El precio de venta no incluye impuestos, por lo que acumula el subtotal neto y calcula la tasa correspondiente sumándola de forma directa encima del valor de la línea.
    * **Caso de Desglose Integrado (Cualquier otra combinación):** El precio base ya contiene el IVA integrado. Aplica la división inversa para obtener la base imponible neta y extrae el valor del impuesto remanente.
  
  Retorna una matriz estructurada con la colección de ítems, el subtotal neto, el total de IVA calculado, el importe total definitivo y las configuraciones de auditoría formateadas con precisión matemática a dos decimales.

### `ComprobanteService`

El servicio `ComprobanteService` gestiona el procesamiento, la validación, la codificación y el almacenamiento de los comprobantes de depósito o transferencia bancaria cargados por los clientes en la plataforma. Orquesta integraciones con un servidor de almacenamiento externo mediante API y maneja transacciones atómicas con sistemas de contingencia local frente a fallos de red.

### Características técnicas
* **Procesamiento de Binarios:** Realiza validaciones estrictas sobre los archivos subidos, abstrayendo sus propiedades MIME para su conversión e inyección en un payload codificado en Base64.
* **Consistencia Transaccional (ACID):** Envuelve el registro del adjunto, la actualización de estados históricos de la orden y la mutación del carrito de compras dentro de una transacción de base de datos (`DBA.PW_ADJUNTO_WEB`, `DBA.PW_HISTORICO_PEDIDO`).
* **Mecanismo de Resiliencia (Fallback):** Implementa un bloque `try/catch` que, en caso de fallar la comunicación HTTP con la API remota, ejecuta un rollback en la base de datos y almacena el archivo binario en el disco local (`storage`) para prevenir la pérdida del documento del cliente.

### Métodos principales

* **__construct(...)** Inyecta las dependencias de los repositorios de carritos y órdenes, así como los servicios complementarios de métodos de pago y proformas para gestionar la metadata financiera asociada al comprobante.

* **mostrarComprobante(array $checkoutData)** Valida los datos de facturación actuales. Consulta dinámicamente las entidades bancarias y de pasarelas cruzando los identificadores corporativos (`currentCompany`) a fin de retornar las cuentas de destino exactas para renderizar la vista de instrucciones de pago (`pedidos.comprobante`).

* **guardarComprobante($archivo, array $checkoutData, string $codCliente, string $empresa, $items, $granTotal)** Orquesta el flujo de guardado físico y digital del documento de transferencia. Ejecuta de forma estructurada las siguientes operaciones lógicas:
  1. **Auditoría de Sesión:** Verifica la existencia de las variables de estado `codigo_orden` y `documento`.
  2. **Sanitización y Codificación:** Valida la integridad del binario, mapea su tipo MIME original y codifica su contenido en una cadena formateada en `data:{mime};base64,`.
  3. **Consumo de API Externa:** Despacha una solicitud `POST` mediante el cliente HTTP de Laravel al microservicio remoto inyectando el payload estructurado con el binario base64, el RUC de la empresa y la referencia de auditoría.
  4. **Persistencia e Historial:** Tras una respuesta exitosa, utiliza la metadata devuelta (`url` y `name`) para poblar la tabla de adjuntos vinculados a la orden web e inserta un nuevo hito en la línea de tiempo del pedido (`cod_estado = '2'`).
  5. **Cierre de Ciclo de Compra:** Invoca al repositorio de carrito para marcar los productos comprados como procesados e invalida los datos temporales guardados en la sesión web para reventar el flujo del checkout, redirigiendo al perfil histórico del cliente.

### `CxcAuxiliarProformaService`

El servicio `CxcAuxiliarProformaService` administra los registros financieros preventivos o asientos de control auxiliar de cuentas por cobrar vinculados a las proformas u órdenes de compra generadas desde la tienda virtual.

### Características técnicas
* **Conexión:** Interactúa directamente con el motor externo mediante la conexión configurada `odbc`.
* **Automatización de Fechas:** Inyecta marcas de tiempo del servidor para homologar las fechas de emisión (`fechae`) y vencimiento (`fechav`) de los movimientos financieros al día en curso.
* **Integración del ERP:** Establece valores por defecto rígidos exigidos por las llaves foráneas y el diseño relacional del módulo de finanzas del ERP (como el tipo de documento estático `'TW'` y la cuenta de banco inicializada en `0`).

### Métodos principales

* **registrar(string $documento, int $formaPago, float $valor, string $empresa, ?float $anticipo = null, ?string $observacion = null)** Inserta de manera directa un registro detallado del cobro provisional o abono en la tabla transaccional `DBA.CXC_AUXILIAR_PROFORMA`. El método acepta parámetros opcionales flexibles para registrar importes parciales en calidad de `$anticipo` y notas comerciales de auditoría en `$observacion`, asociando el movimiento de forma unívoca al código corporativo de la sucursal emisora.

### `NuveiService`

El servicio `NuveiService` abstrae e integra las operaciones de la pasarela de pagos internacional **Nuvei** (anteriormente conocida como **Paymentez**). Su función principal es negociar de forma segura la creación de enlaces de pago dinámicos (*Link to Pay*) a través del cliente HTTP de Laravel.

### Características técnicas
* **Seguridad Algorítmica:** Implementa el estándar de firma digital exigido por la API oficial de Paymentez, encadenando marcas de tiempo, claves de servidor cifradas mediante SHA-256 y empaquetamiento en Base64.
* **Tolerancia a Fallos y Monitoreo:** Define tiempos de espera máximos estrictos (`timeout` de 20 segundos) e instrumenta el registro de auditoría (`Log::info` / `Log::error`) para rastrear respuestas inesperadas y excepciones de red sin comprometer ni exponer datos sensibles del comercio.

### Métodos principales

* **__construct()** Hidrata la instancia de la clase mapeando la matriz de configuraciones del archivo `config/payments.php`. Establece por defecto las credenciales, los tiempos de expiración del enlace y la URL de inicialización de pruebas en el entorno de Staging si no son definidos explícitamente en el entorno.

* **generarAuthToken()** *(Protegido)* Construye el token de autenticación dinámico requerido en la cabecera `Auth-Token` de cada petición. El algoritmo opera bajo el siguiente flujo:
  1. Captura la marca de tiempo Unix actual en segundos.
  2. Concatena la clave privada de servidor (`serverKey`) junto al timestamp.
  3. Encripta la cadena resultante aplicando el algoritmo hash `sha256` en formato hexadecimal.
  4. Consolida y retorna la cadena codificada bajo el estándar `base64_encode("client_id;timestamp;hash")`.

* **generarLinkPago(array $orderData, array $userData, array $urls)** Motor de despacho de órdenes de cobro. Construye un payload JSON jerárquico dividiéndolo en tres bloques de control obligatorio:
  * **`user`:** Datos de identificación y contacto del cliente comprador.
  * **`order`:** Metadata contable de la orden (`dev_reference`), aplicando la función `round()` a los montos de transacciones, IVA y bases imponibles para asegurar la precisión matemática requerida por la pasarela de pagos.
  * **`configuration`:** Reglas de comportamiento del enlace, inyectando métodos de pago permitidos (`All`), tiempos de expiración y las rutas URL de redirección final de la interfaz (`success`, `failure`, `pending`, `review`).

  Realiza la petición mediante un método `POST` inyectando las cabeceras dinámicas. Si la pasarela responde de manera exitosa, parsea la estructura para extraer el enlace de pago web (`payment_url`) y el identificador único interno de Nuvei (`order_id`). En caso de error en la red o de validación por parte del servidor externo, intercepta el flujo mediante un bloque `Throwable` para registrar la excepción y devolver una matriz estructurada con el detalle del fallo.

###  `OrderApprovalService`

El servicio `OrderApprovalService` es un componente de simulación local diseñado con fines de desarrollo y pruebas QA. Permite emular el comportamiento real del ERP corporativo de producción para forzar el cambio de estados en los pedidos y ejecutar el descuento de inventarios de forma controlada en el entorno local.

### Características técnicas
* **Simulación Local:** Replica de forma manual la lógica del motor de producción, permitiendo a los desarrolladores visualizar la afectación de tablas y flujos lógicos sin depender de los procesos automatizados y programados en el core del sistema central.
* **Control de Variantes:** Evalúa dinámicamente el origen del stock del producto para discernir entre un artículo plano o variantes específicas mediante presentaciones, alterando la tabla correspondiente.
* **Seguridad Transaccional:** Ejecuta las mutaciones de inventario y cambios de estado de forma atómica (`DB::beginTransaction`), revirtiendo cualquier cambio parcial en caso de presentarse errores críticos.

### Métodos principales

* **__construct(PaymentService $paymentService, ProformaGenerator $proforma)** Inyecta las dependencias necesarias para la recuperación estructurada de órdenes web y la gestión de proformas comerciales complementarias.

* **aprobar(string $codigo, string $empresa)** Ejecuta el proceso simulado de aprobación de un pedido, mutando las existencias físicas del catálogo local. Realiza las siguientes operaciones secuenciales:
  1. **Validación de Estado:** Recupera la orden y verifica que exista y que no haya sido aprobada previamente (`estatus === '2'`).
  2. **Lectura de Detalles:** Consulta los ítems asociados al pedido en `DBA.pw_carrito_web` cuyo estado sea procesado (`estatus = '2'`), extrayendo códigos, cantidades, presentaciones y ubicaciones.
  3. **Afectación de Stock:** Itera sobre cada ítem y descuenta las existencias:
     * **Si posee variante (`presentacion > 0`):** Reduce la cantidad directamente en la tabla de variantes `DBA.in_existencia_presentacion`.
     * **Si es artículo simple:** Afecta directamente la tabla maestra de existencias generales `DBA.in_existencia`.
  4. **Cierre de Orden:** Actualiza el estado del pedido a aprobado (`estatus = '2'`) en `DBA.PW_ORDENES_WEB` e inyecta un nuevo hito en el registro cronológico `DBA.PW_HISTORICO_PEDIDO` (`cod_estado = '3'`). Retorna el número de documento procesado tras realizar el commit.

* **rechazar(string $codigo, string $empresa, string $motivo)** Cancela el flujo de aprobación local de un pedido. Omite el descuento de inventario e inserta una entrada de rechazo en la línea de tiempo de la orden (`DBA.PW_HISTORICO_PEDIDO`) con el estado inicial (`cod_estado = '1'`), adjuntando en el campo de observaciones el motivo técnico o comercial proporcionado por el desarrollador.

### `PasswordResetService`

El servicio `PasswordResetService` orquesta de forma segura los flujos de recuperación y restauración de credenciales de acceso para los clientes de la tienda virtual, aislando la lógica criptográfica y el despacho de notificaciones de las capas externas.

### Características técnicas
* **Criptografía de un Solo Sentido:** Utiliza hashing simétrico `SHA-256` para persistir los tokens en la base de datos externa (`pw_password_resets`), evitando almacenar los identificadores en texto plano y mitigando ataques de secuestro de sesión o inyección.
* **Caducidad Controlada:** Inyecta marcas de tiempo dinámicas mediante la librería `Carbon` para restringir la validez del token de reseteo a una ventana estricta de 1 hora.
* **Integración Omnicanal de Notificaciones:** Coordina el motor de renderizado de Laravel para inyectar plantillas dinámicas en formato Blade (`view()->render()`) y despacharlas como HTML plano interactivo a través de la API externa del servicio `BrevoMailer`.

### Métodos principales

* **__construct(ClientRepository $clients, BrevoMailer $mailer)** Inyecta mediante promoción de propiedades las dependencias del repositorio de clientes y el servicio de despacho de correos transaccionales.

* **sendResetLink(string $email)** Administra el inicio de la solicitud de recuperación. Evalúa la existencia del usuario mediante el repositorio; si el correo es válido, genera una cadena aleatoria de 60 caracteres (`Str::random`) y persiste su equivalente en hash junto a una expiración calculada a `+1 hour`. Construye la URL estructurada de redirección inyectando los parámetros por GET, compila la vista HTML en memoria y despacha la petición HTTP a Brevo, retornando un booleano basado en la confirmación del `messageId`.

* **resetWithToken(string $email, string $token, string $password)** Completa el ciclo de restauración de credenciales. Ejecuta tres capas de control secuencial:
  1. **Validación:** Invoca al repositorio para certificar que el hash del token coincida, corresponda al email proporcionado y no se encuentre expirado.
  2. **Actualización Sincronizada:** Recupera el registro maestro e invoca la mutación aplicando el algoritmo de encriptación `bcrypt` sobre la nueva contraseña, afectando en paralelo las entidades relacionales de la base de datos externa (`in_cliente` y `ge_usuario`).
  3. **Saneamiento:** Remueve de manera definitiva el token utilizado para anular cualquier reuso del enlace por vectores maliciosos, devolviendo un estado exitoso.

###  `PaymentMethodService`

El servicio `PaymentMethodService` centraliza las consultas y validaciones relativas a las pasarelas, canales de recaudación y cuentas financieras corporativas autorizadas para la recaudación del comercio electrónico.

### Características técnicas
* **Conexión:** Consume directamente datos crudos del motor central a través de la conexión `odbc`.
* **Filtrado Comercial Parametrizado:** Restringe el catálogo total de cobros del ERP para recuperar únicamente aquellos configurados específicamente con la bandera de visualización en canales digitales (`view_on_tienda = 'S'`).
* **Abstracción Cruzada (Módulos):** Relaciona de manera limpia entidades de cuentas por cobrar (`cxc_forma_pago`), tesorería (`te_cuentas_bancos`) y la gestión de pedidos web (`PW_ORDENES_WEB`) bajo entornos multiempresa.

### Métodos principales

* **obtenerFormasPago(string $empresa)** Recupera la colección completa de métodos y pasarelas de pago habilitadas para la tienda virtual de una sucursal específica. Retorna un arreglo de objetos ordenados jerárquicamente por el campo de prioridad comercial `secuencia`.

* **obtenerFormaPago(int $secuencia, string $empresa)** Consulta y extrae unívocamente un registro de cobro del catálogo maestro cruzando el identificador único de la secuencia con el código corporativo. Aplica la cláusula `TOP 1` para optimizar la velocidad de respuesta del motor ODBC.

* **obtenerCuentaBanco($formaPago, string $empresa)** Resuelve los detalles logísticos y financieros de la entidad bancaria asociada a una modalidad de pago en particular (utilizado para desplegar datos de transferencias o depósitos). Evalúa la existencia del objeto y su propiedad `cod_cuenta_banco`; si es válido, consulta la tabla de tesorería `DBA.te_cuentas_bancos` para extraer números de cuenta, tipos, descripciones y cuentas contables de auditoría interna.

* **obtenerOrden(string $codigo, string $empresa)** Actúa como un puente de consulta rápida para auditar la metadata base de un pedido web alojado en la tabla `DBA.PW_ORDENES_WEB`, validando su coincidencia con el código del registro y la firma corporativa.

###  `PaymentService`

El servicio `PaymentService` actúa como el orquestador principal del proceso de finalización de compra (*Checkout*) de la tienda virtual. Su función es coordinar la persistencia de las órdenes, la interacción con pasarelas externas y la sincronización de la base de datos relacional mediante conexiones ODBC, distribuyendo el flujo operativo según el método de pago seleccionado.

### Características técnicas
* **Arquitectura de Decisiones:** Evalúa dinámicamente la secuencia del método de cobro comparándolo contra los archivos de configuración nativos del sistema (`config/payments.php`).
* **Robustez Transaccional:** Envuelve la creación de pedidos, generación de históricos y registros contables auxiliares en bloques atómicos controlados (`DB::beginTransaction`), garantizando la reversión total (*Rollback*) ante anomalías de red o fallos de persistencia.
* **Integración del Padrón de Clientes:** Implementa una estrategia híbrida que consulta servicios API externos de identificación para actualizar o dar de alta usuarios de forma automatizada en el maestro de clientes del ERP.

### Métodos principales

* **procesarPago(array $data, string $codCliente, string $empresa)** Es la compuerta de entrada del flujo de facturación. Resuelve la validez del método de pago, almacena temporalmente la información del formulario en la sesión global (`checkout_data`) y enruta la transacción hacia uno de los tres subprocesos: `procesarPagoEfectivo` (reserva en tienda), `procesarPagoNuvei` (pasarela de pagos) o `procesarPagoTransferencia` (comprobante manual).

* **procesarPagoEfectivo(array $checkoutData, string $codClienteFactura, string $codClienteCarrito, string $empresa)** Procesa órdenes cuyo cobro final se realizará de forma presencial. Valida las existencias en el carrito, registra el pedido en `DBA.PW_ORDENES_WEB` con estado inicial (`estatus = '1'`), invoca la generación de la proforma contable, asienta el registro auxiliar preventivo en `DBA.CXC_AUXILIAR_PROFORMA`, añade un hito en el historial y marca el carrito como procesado.

* **procesarPagoTransferencia(array $checkoutData, string $codClienteFactura, string $codClienteCarrito, string $empresa)** Sigue una estructura homóloga al flujo en efectivo (`estatus = '1'`), pero añade la resolución dinámica del banco receptor. Utiliza el método `obtenerCuentaBanco` para extraer la descripción financiera de la entidad de destino, la inyecta en la glosa del registro auxiliar de cuentas por cobrar y redirige al cliente a la vista de carga de comprobante.

* **procesarPagoNuvei(array $checkoutData, string $codClienteFactura, string $codClienteCarrito, string $empresa)** Orquesta la integración con la API de tarjetas de crédito. Persiste la orden en estado pendiente (`estatus = '1'`) junto con la proforma. Tras el commit, procesa los nombres del cliente para acoplarlos al esquema obligatorio de Nuvei, calcula el desglose impositivo y solicita el enlace seguro llamando a `NuveiService` para desviar al usuario mediante `redirect()->away()`.

* **obtenerOCrearCliente(array $data, string $empresa)** Garantiza la consistencia del padrón de clientes mediante un mecanismo *Upsert*: envía una petición `GET` temporizada a un microservicio externo (`/persona/{cedula}`) y busca el registro en `DBA.in_cliente`. Si existe, actualiza los campos de contacto usando la mezcla de datos; si no existe, ejecuta un cálculo de bloqueo agregativo de códigos (`COALESCE(MAX(CAST(...)))`) e inserta el nuevo registro con estado activo (`'A'`).

* **obtenerOrden(string $codigo, string $empresa)** Método de lectura directa sobre la conexión ODBC diseñado para consultar registros específicos de la tabla corporativa `DBA.PW_ORDENES_WEB`.


### `ProductService`

El servicio `ProductService` centraliza la lógica de negocio orientada a la gestión, filtrado, paginación y despliegue del catálogo de artículos y variantes comerciales disponibles en la plataforma de comercio electrónico.

### Características técnicas
* **Paginación y Consistencia:** Administra volúmenes extensos de inventario fijando un estándar de entrega segmentada (bloques de 40 productos por página) para optimizar los tiempos de carga en la interfaz del cliente.
* **Mapeo Orientado a Objetos:** Transforma los registros en bruto (*rows*) provenientes del repositorio de datos en instancias de modelo fuertemente tipadas mediante transformaciones iterativas (`array_map`).
* **Soporte Multiempresa y Multiorigen:** Segmenta las búsquedas de productos, destacados y estructuras lógicas de filtrado de manera aislada basándose en el identificador corporativo de la empresa solicitante.

### Métodos principales

* **__construct()** Inicializa de forma directa los componentes maestros de consulta y tipado, instanciando los modelos `ProductsModel` y `ProductPresentation`, así como la capa de persistencia empaquetada en `ProductRepository`.

* **getCatalogo(array $filters, int $page, string $empresa)** Resuelve la carga dinámica de la tienda virtual. Invoca al repositorio enviando el índice de página, el límite estático de registros y un mapa sanitizado de filtros (búsqueda de texto, grupo, línea, ubicación de stock, rangos de precio mínimo/máximo y ordenamiento). Retorna una estructura homologada para paginación nativa que incluye el conteo total, límites por página, páginas actuales y cálculo de última página, convirtiendo las filas de la base de datos en instancias del modelo web.

* **getProductWithPresentations(string $codigo, string $empresa)** Actúa como el resolvedor de variantes para fichas técnicas detalladas. Delega en el modelo `ProductPresentation` la extracción completa del subcatálogo de ítems con presentaciones y empaques específicos asociados al identificador único del artículo maestro.

* **getDestacados(string $empresa, int $limit = 20)** Provee la colección de ítems priorizados para alimentar elementos dinámicos de mercadeo visual como carruseles o secciones promocionales en la página de inicio. Recupera los registros bajo un umbral de límite predeterminado y les aplica el transformador de hidratación de objetos.

* **getFiltros(string $empresa)** Extrae los metadatos estructurales de clasificación comercial del inventario ERP. Construye un arreglo asociativo bidimensional que agrupa de forma unificada la totalidad de los "grupos" y "líneas" de productos registrados y activos para la sucursal indicada.

### `ProformaGenerator`

El servicio `ProformaGenerator` es el encargado de la traducción y el asentamiento de los pedidos del ecosistema web hacia las estructuras de pre-facturación del ERP institucional, gestionando la conversión de ítems del carrito en documentos válidos de proforma.

### Características técnicas
* **Integridad Atómica:** Ejecuta el proceso de generación bajo un entorno transaccional (`DB::beginTransaction`), revirtiendo cualquier inserción intermedia si ocurre un fallo en la asignación de folios, desgloses o enlaces web.
* **Resolución Dinámica de Stock:** Incorpora un motor de búsqueda fallback que localiza bodegas o ubicaciones físicas basándose de forma jerárquica en la disponibilidad volumétrica actual de las existencias (con soporte para empaques estándar o variantes por presentación).
* **Firma e Historial Relacional:** Vincula de forma cruzada la proforma generada con la orden original de la plataforma mediante la inyección del número del documento en los metadatos de control web (`n_documento`, `n_confirmacion`, `pt_referencia_compra`).

### Métodos principales

* **generarDesdeOrden($orden, array $itemsCarrito, string $empresa)** Actúa como el punto de entrada maestro del servicio. Setea la firma de la empresa emisora, abre la transacción ODBC, calcula el folio numérico correlativo y procesa de forma secuencial la cabecera, los movimientos detallados de inventario y la actualización de la orden web. En caso de éxito, retorna el código del documento; ante cualquier excepción, efectúa un `rollBack` y reporta el error en los registros del sistema (`Log::error`).

* **generarNumeroDocumento()** Consulta el histórico de pre-facturación en `DBA.IN_CABECERA_PROFORMA` filtrando por el tipo de documento exclusivo para transacciones digitales (`TW`) y la sucursal activa. Extrae el identificador máximo mediante casteo explícito (`MAX(CAST(...))`) e incrementa en una unidad el valor resultante para prevenir colisiones de folios.

* **crearCabeceraProforma($orden, string $documento, array $itemsCarrito)** Evalúa el acumulado financiero bruto de los productos basándose en el precio asignado de lista (`pvp3`). Inserta el registro maestro en la tabla de cabeceras asociándolo al vendedor predeterminado, fijando el estado inicial activo (`'A'`) e inyectando las observaciones o comentarios ingresados por el usuario final durante el checkout.

* **crearMovimientosProforma(string $documento, array $itemsCarrito)** Itera sobre la colección de ítems para construir el desglose físico del pedido en `DBA.IN_MOVIMIENTO_PROFORMA`. Prioriza la ubicación de stock provista en la sesión del carrito; si se encuentra vacía, invoca el resolvedor automático de bodegas y detiene el flujo arrojando una excepción controlada si el inventario global es insuficiente para satisfacer la demanda del artículo.

* **buscarUbicacionConStock(string $codigoItem, int $presentacion, int $cantidadNecesaria)** Ejecuta la consulta de disponibilidad en la base de datos dividiendo la lógica según la naturaleza del empaque:
  * **Con Presentación (`> 0`):** Cruza las existencias específicas en `DBA.in_existencia_presentacion` ordenando de forma descendente por volumen físico para seleccionar la bodega con mayor stock disponible (`TOP 1`).
  * **Stock Normal:** Consulta directamente la tabla de inventario base `DBA.in_existencia` bajo los mismos criterios de optimización y suficiencia de unidades.

* **registrarOrdenWeb($orden, string $documento)** Aplica una mutación de actualización sobre la tabla intermedia de control web `DBA.PW_ORDENES_WEB` para estampar las firmas digitales, referencias cruzadas del documento del ERP y actualizar la marca de tiempo de modificación.
