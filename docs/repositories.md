## Repositorios

Los repositorios dentro de `app/Repositories/` se encargan de encapsular la lógica de acceso a datos y las consultas SQL crudas, aislando las operaciones de la base de datos externa de las capas superiores de la aplicación.

---

### `CartRepository`

Este repositorio centraliza y gestiona el ciclo de vida del carrito de compras web de los clientes sobre la tabla `DBA.pw_carrito_web`.

* **¿Cómo funciona internamente?:** Se comunica de manera directa a través de la conexión heredada `odbc`. Administra los estados de compra mediante la columna `estatus`, donde el valor `'1'` representa un carrito activo/pendiente y el valor `'2'` identifica una orden procesada y cerrada.

* **Métodos del repositorio:**

  - `getByUser(string $codCliente)`:  
    * **¿Cómo funciona internamente?:** Ejecuta una sentencia selectiva sobre la tabla filtrando por el código único del usuario y validando únicamente registros con `estatus = '1'`.  
    * **¿Qué devuelve?:** Un `array` con los objetos de las filas que representan todos los artículos actualmente agregados a la cesta del cliente.

  - `exists(string $codCliente, string $codigoItem, int $presentacion, ?string $ubicacion)`:  
    * **¿Cómo funciona internamente?:** Evalúa de forma dinámica si un producto o variante ya se encuentra en el carrito. Añade condicionalmente una cláusula `AND ubicacion = ?` a la consulta SQL únicamente si se recibe dicho parámetro, ajustando de igual manera el mapeo de los argumentos correspondientes.  
    * **¿Qué devuelve?:** Un booleano (`true` si el registro ya existe bajo esas condiciones o `false` en caso contrario).

  - `delete(int $idItemWeb, string $codCliente)`:  
    * **¿Cómo funciona internamente?:** Ejecuta un comando `DELETE` físico del registro que coincida de forma unívoca con el identificador secuencial del ítem y el código de cliente.  
    * **¿Qué devuelve?:** Un entero (`int`) con la cantidad de filas eliminadas.

  - `updateCantidad(int $idItemWeb, string $codCliente, int $cantidad)`:  
    * **¿Cómo funciona internamente?:** Altera el número de unidades de un renglón activo del carrito (`estatus = '1'`) aplicando una sentencia `UPDATE`.  
    * **¿Qué devuelve?:** El número de registros modificados en la transacción.

  - `vaciar(string $codCliente)`:  
    * **¿Cómo funciona internamente?:** Purga masivamente todos los artículos guardados y activos (`estatus = '1'`) vinculados al identificador del cliente en sesión.  
    * **¿Qué devuelve?:** Cantidad de registros eliminados.

  - `getTotal(string $codCliente)`:  
    * **¿Cómo funciona internamente?:** Invoca la función agregada `SUM()`. Para evitar inconsistencias de redondeo o desbordamiento de tipos de datos en el servidor Sybase/SQL Anywhere, aplica un casteo explícito convirtiendo el precio (`pvp3`) a `NUMERIC(15,6)` y la columna `cantidad` a `INTEGER` antes de la multiplicación.  
    * **¿Qué devuelve?:** Un valor decimal (`float`) con el importe acumulado total del carrito.

  - `count(string $codCliente)`:  
    * **¿Cómo funciona internamente?:** Ejecuta una consulta simplificada utilizando `COUNT(*)` sobre los ítems asociados que mantengan el estado activo.  
    * **¿Qué devuelve?:** Un entero (`int`) con el total de registros en la cesta (utilizado para actualizar contadores en tiempo real).

  - `marcarComoProcesado(string $codCliente, string $ordenId)`:  
    * **¿Cómo funciona internamente?:** Recupera la sucursal del usuario mediante el helper de sesión `session('ubicacion_seleccionada')`. Posteriormente, ejecuta un `UPDATE` que transiciona el estado de la cesta activa de `'1'` a `'2'` y liga de forma permanente el identificador `orden_id` generado por la pasarela de pagos, restringiendo el proceso a registros que no posean una orden previa (`orden_id IS NULL`).  
    * **¿Qué devuelve?:** El número de filas afectadas que pasaron al histórico de órdenes.

### `ClientRepository`

Este repositorio centraliza las consultas y operaciones relacionadas con la gestión de clientes en la tabla `in_cliente`, enfocándose principalmente en los procesos de seguridad, validación y restablecimiento de contraseñas de la plataforma.

* **¿Cómo funciona internamente?:** Ejecuta sentencias SQL crudas delegadas a la conexión `odbc`. Su particularidad radica en el uso de transacciones atómicas para operaciones sensibles de escritura y en la actualización simultánea de credenciales en múltiples tablas maestras del sistema externo para mantener la integridad de los accesos.

* **Métodos del repositorio:**

  - `findByEmailOrContact(string $email)`:  
    * **¿Cómo funciona internamente?:** Realiza una consulta directa con la sintaxis `SELECT TOP 1` sobre la tabla maestra `in_cliente` buscando coincidencias exactas en la columna `e_mail`.  
    * **¿Qué devuelve?:** Un `object` con todos los datos de la fila del cliente si es localizado, o `null` si el correo electrónico no se encuentra registrado en el sistema.

  - `storeResetToken(string $codigo, string $email, string $hash, \DateTimeInterface $expiresAt)`:  
    * **¿Cómo funciona internamente?:** Envuelve el proceso dentro de una transacción de base de datos (`DB::transaction`). Primero, ejecuta un `DELETE` preventivo en la tabla `pw_password_resets` para remover cualquier token anterior vinculado al correo electrónico del usuario bajo la empresa activa (`currentCompany()`). Posteriormente, realiza un `INSERT` con el nuevo registro de seguridad y guarda la fecha y hora actual en formato de cadena de texto.  
    * **¿Qué devuelve?:** No tiene retorno (`void`).

  - `validateResetToken(string $email, string $token)`:  
    * **¿Cómo funciona internamente?:** Aplica el algoritmo `sha256` al token plano recibido para compararlo con el de la base de datos. Extrae el registro correspondiente de la tabla `pw_password_resets` y ejecuta dos filtros estrictos de seguridad: una comparación en tiempo constante mediante `hash_equals()` para evitar ataques de sincronización (timing attacks), y un cálculo matemático de expiración basado en marcas de tiempo (`strtotime`), el cual añade de manera estricta una ventana de validez de 1 hora (`+1 hour`) sobre la columna `created_at`.  
    * **¿Qué devuelve?:** Un booleano (`true` si el token coincide y sigue vigente, o `false` si es inválido o caducó).

  - `updatePassword(string $codigo, string $hashedPassword)`:  
    * **¿Cómo funciona internamente?:** Ejecuta dos sentencias `UPDATE` consecutivas para asegurar la sincronización de accesos en el Core: la primera impacta directamente a la entidad de la tienda dentro de `in_cliente`, y la segunda modifica el registro homólogo de credenciales en la tabla de accesos globales `pw_ge_usuarios`, restringiendo ambas acciones por el código de cliente y la empresa activa.  
    * **¿Qué devuelve?:** No tiene retorno (`void`).

  - `deleteResetToken(string $email)`:  
    * **¿Cómo funciona internamente?:** Ejecuta una sentencia de limpieza `DELETE FROM` sobre la tabla `pw_password_resets` para invalidar y purgar por completo el token de recuperación asociado al correo del cliente una vez que el proceso concluye con éxito.  
    * **¿Qué devuelve?:** No tiene retorno (`void`).

### `LoginRepository`

Este repositorio gestiona el ciclo de vida de los usuarios de la plataforma y el control de accesos directos sobre la tabla credencial `DBA.pw_ge_usuarios`.

* **¿Cómo funciona internamente?:** Ejecuta comandos de lectura y escritura directos mediante sentencias SQL puras bajo la conexión `odbc`. Su característica principal es que interactúa directamente con el modelo personalizado `login_model`, mapeando los registros crudos del backend externo a objetos legibles y compatibles con el sistema de autenticación nativo de Laravel.

* **Métodos del repositorio:**

  - `findByEmail(string $email)`:  
    * **¿Cómo funciona internamente?:** Captura la identidad de la sucursal activa mediante `currentCompany()` y realiza una búsqueda restrictiva mediante `SELECT TOP 1` combinando el correo electrónico y el identificador de empresa. Si localiza la fila, invoca de manera estática el mapeador `login_model::mapRowToInstance()`.  
    * **¿Qué devuelve?:** Un objeto del tipo `login_model` completamente poblado si coincide con los filtros, o `null` si no existe.

  - `createUser($data)`:  
    * **¿Cómo funciona internamente?:** Implementa un generador secuencial de llaves primarias nativas en PHP consultando previamente la tabla lógica de clientes (`DBA.in_cliente`). Aplica un casteo numérico con `MAX(CAST(codigo AS INT))` y le suma `1` para calcular de manera manual el próximo secuencial disponible (`nuevo_codigo`). Acto seguido, inyecta dicho código generado junto al arreglo `$data` (estableciendo valores por defecto como el estado `'A'`) dentro de la tabla de accesos `DBA.pw_ge_usuarios`.  
    * **¿Qué devuelve?:** Un booleano (`true` o `false`) indicando el éxito o fallo de la sentencia `INSERT`.

  - `getUsers()`:  
    * **¿Cómo funciona internamente?:** Ejecuta de manera directa un comando global `SELECT *` sobre la tabla maestra para extraer de forma masiva los registros existentes.  
    * **¿Qué devuelve?:** Un `array` con objetos crudos correspondientes a todas las filas almacenadas en la base de datos de usuarios.

  - `updateUser($user_id, $data)`:  
    * **¿Cómo funciona internamente?:** Actualiza información de perfil mediante una consulta `UPDATE`, permitiendo la edición directa sobre las columnas de contacto y datos principales (`nombre`, `direccion`, `telefono`) restringiendo la acción por el `user_id` único del usuario.  
    * **¿Qué devuelve?:** Un entero (`int`) representando el número de registros modificados en la consulta.

  - `findById(int $userId)`:  
    * **¿Cómo funciona internamente?:** Filtra de forma unívoca la tabla de credenciales a través de la columna `user_id` utilizando `SELECT TOP 1`. En caso de éxito, transforma la fila a una instancia orientada a objetos de autenticación.  
    * **¿Qué devuelve?:** Una instancia limpia de `login_model` si es localizada, o `null` si el identificador no existe.

  - `updatePassword($userId, string $nuevaContrasena)`:  
    * **¿Cómo funciona internamente?:** Realiza una mutación directa sobre el campo `contrasena` de la base de datos de accesos a través de un comando `UPDATE`, aislando y aplicando el cambio estrictamente para el registro que cumpla con el identificador del usuario proporcionado.  
    * **¿Qué devuelve?:** La cantidad de filas afectadas por la actualización en el servidor.

### `OrderRepository`

Este repositorio gestiona el almacenamiento, estructuración y consulta de las compras finalizadas por los clientes, operando principalmente sobre la tabla de transacciones de la pasarela `DBA.PW_ORDENES_WEB`.

* **¿Cómo funciona internamente?:** Se conecta directamente de forma nativa a través del driver `odbc`. Se encarga de procesar los cierres de caja virtuales mediante la inserción de metadatos de la pasarela y calcula secuenciales numéricos manuales para compatibilidad con las llaves del Core externo, cruzando además información con el histórico del carrito procesado.

* **Métodos del repositorio:**

  - `crearOrden(array $data)`:  
    * **¿Cómo funciona internamente?:** Utiliza el constructor de consultas estructurado (`->table()`) de Laravel en lugar de SQL crudo para realizar una inserción directa del arreglo indexed `$data` dentro de la tabla de control `DBA.PW_ORDENES_WEB`.  
    * **¿Qué devuelve?:** Un booleano (`true` si la inserción en la tabla de transacciones web fue exitosa o `false` en caso de fallar).

  - `generarCodigoOrden(string $empresa)`:  
    * **¿Cómo funciona internamente?:** Resuelve la generación de llaves primarias autoincrementales de forma manual para evitar colisiones en el servidor externo Sybase/SQL Anywhere. Ejecuta una sumatoria aplicando `MAX(CAST(codigo AS INTEGER))` sobre los registros de la sucursal activa; si localiza un código previo, incrementa dicho valor en `+1`, de lo contrario, inicializa el primer registro del histórico devolviendo el valor inicial de control.  
    * **¿Qué devuelve?:** Un `string` numérico que representa el identificador secuencial único de la nueva orden de compra.

  - `obtenerItemsOrden(string $codigo, string $codCliente)`:  
    * **¿Cómo funciona internamente?:** Extrae de manera detallada el desglose físico de productos comprados consultando la tabla `DBA.pw_carrito_web`. Para realizar la coincidencia exacta de registros, aplica un casteo explícito con `CAST(? AS INTEGER)` sobre el parámetro de la orden y restringe la búsqueda estrictamente a ítems históricos cerrados que mantengan la bandera de procesamiento finalizado (`estatus = '2'`).  
    * **¿Qué devuelve?:** Un `array` con las filas de los productos adquiridos, incluyendo el nombre sanitizado, precio de venta aplicado (`pvp3`), cantidades, variantes y el indicador de impuesto al valor agregado (`iva`), ideal para el renderizado de facturas o confirmaciones de correo.

### `ProductRepository`

El repositorio `ProductRepository` centraliza y gestiona las consultas del catálogo de productos sobre la tabla maestra `DBA.in_item` y sus entidades relacionadas (líneas, grupos, existencias y presentaciones). Su arquitectura maneja la lógica de negocio para el filtrado dinámico de inventarios según la ubicación seleccionada por el cliente.

### Características técnicas
* **Conexión:** Opera de forma directa a través de la conexión `odbc`.
* **Control de Inventario:** Restringe estrictamente la visibilidad de los productos evaluando el parámetro `view_on_tienda = 'S'` en las ubicaciones, evitando que se muestre stock de bodegas ocultas o privadas.
* **Optimización:** Utiliza una estrategia de almacenamiento en caché con una expiración de 6 horas en la lectura de estructuras comerciales estáticas.

### Métodos principales

* **normalizeString(string $text)** *(Privado)* Normaliza las cadenas de texto convirtiéndolas a minúsculas (`mb_strtolower`) y reemplazando caracteres especiales (tildes, diéresis y la letra "ñ") por sus equivalentes planos para estandarizar las búsquedas en el servidor.

* **getActiveProducts(int $limit, string $empresa)** Recupera un listado plano de productos activos configurados para la venta web (`itemb = 'S'`) cruzando cada ítem con su correspondiente línea comercial.

* **getProductosDestacados(int $limit, string $empresa)** Devuelve una colección aleatoria (`ORDER BY RAND()`) de productos con stock disponible mayor a cero. Si el usuario tiene una ubicación activa en sesión, filtra el stock únicamente por esa sucursal; si es un invitado, consolida las existencias de todas las bodegas públicas habilitadas.

* **searchProducts(string $search, string $empresa)** Realiza búsquedas de texto en el catálogo aplicando un filtro de doble coincidencia (`LIKE`), comparando la descripción original en paralelo con la cadena procesada por el normalizador de texto.

* **getGrupos(string $empresa) / getLineas(string $empresa)** Extraen los catálogos estructurados de subcategorías (`in_grupo`) y categorías (`in_linea`) de la empresa. Formatea y limpia los nombres utilizando el modelo de productos. **Cuenta con una caché de 6 horas.**

* **getUbicaciones(string $empresa)** Retorna el listado de puntos de venta y sucursales configuradas que se encuentran explícitamente habilitadas para ser vistas en la tienda virtual (`view_on_tienda = 'S'`).

* **getUbicacionesProducto(string $codigo, string $empresa) / getUbicacionesPresentacion(int $codigoPresentacion, string $empresa)** *(Pendientes a eliminar)* Mapean y ordenan de mayor a menor stock las ubicaciones físicas donde un artículo simple o una variante de producto específica posee existencias reales disponibles.

* **getPaginatedProducts(...)** Motor principal de búsquedas y paginación del catálogo web. Calcula el desfase de registros mediante la sintaxis nativa `TOP {$perPage} START AT {$startAt}` de SQL Anywhere y aplica múltiples filtros dinámicos (búsqueda, grupo, línea, precios, ubicación). Realiza el conteo global preventivo mediante una subconsulta con `HAVING` para retornar los metadatos exactos de la paginación (`rows`, `total`, `per_page`, `page`, `last_page`).

* **findByCodigo(string $codigo, string $empresa)** Localiza de manera unívoca un artículo del inventario evaluando la llave compuesta por el código del ítem y el identificador de la empresa.

* **getRelacionados(string $codigo, string $grupo, string $empresa, string $ubicacion, int $limit)** Busca alternativas comerciales dentro de la misma subcategoría excluyendo el producto actual. Consolida el stock disponible sumando el inventario simple y de variantes basado estrictamente en la ubicación de la sesión del usuario.

### `WishListRepository`

El repositorio `WishListRepository` encapsula el acceso directo a la tabla `DBA.pw_wishlist` mediante la conexión **ODBC**, centralizando la gestión de la lista de deseos de los clientes y calculando la disponibilidad de stock en tiempo real según la sesión.

### Características técnicas
* **Conexión:** Realiza consultas directas sobre la conexión heredada `odbc`.
* **Cálculo de Disponibilidad:** Cruza los elementos guardados con las existencias generales y de presentaciones filtrando por la ubicación activa para asegurar que las acciones de compra reflejen la disponibilidad real de la sucursal.
* **Mapeo de Datos:** Utiliza el método estático `mapRowToInstance` del modelo `WishListModel` para transformar los objetos crudos del backend externo antes de retornarlos.

### Métodos principales

* **getByCliente(string $codCliente, string $empresa)** Recupera el listado completo de productos en la lista de deseos de un cliente ordenados de forma descendente por fecha de creación. Realiza un `LEFT JOIN` dinámico con las existencias simples (`DBA.in_existencia`) y existencias por variante (`DBA.in_existencia_presentacion`) limitadas a la ubicación de sesión del usuario para calcular el `stock_total`. También verifica si el ítem posee formatos adicionales (`tiene_presentaciones`).

* **exists(string $codCliente, string $codigoItem, string $empresa)** Evalúa mediante una consulta optimizada `SELECT TOP 1` si un artículo específico ya fue agregado por el cliente a su lista de deseos en la empresa actual. Retorna un valor booleano (`true` o `false`).

* **add(array $data)** Inserta un nuevo artículo en la tabla `DBA.pw_wishlist`. Mapea de forma segura el arreglo `$data` asignando valores por defecto de cero para el precio de venta (`pvp3`) y nulo para la ruta de la imagen en caso de no ser proporcionados. Retorna un booleano con el resultado de la inserción.

* **remove(string $codCliente, string $codigoItem, string $empresa)** Remueve físicamente el registro de la lista de deseos que coincida exactamente con el código del cliente, el identificador del ítem y el código corporativo. Retorna un entero con la cantidad de filas eliminadas.

* **count(string $codCliente, string $empresa)** Ejecuta una consulta simplificada utilizando la función agregada `COUNT(*)` para obtener la cantidad total de artículos almacenados en la lista de deseos del cliente actual. Se utiliza principalmente para actualizar contadores dinámicos en los menús de la interfaz.
