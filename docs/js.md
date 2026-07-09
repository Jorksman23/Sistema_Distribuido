# `agregarAlCarrito`

Esta función de JavaScript maneja la interceptación y el envío asíncrono (AJAX) de los formularios que añaden productos al carrito de compras desde el catálogo de la tienda web. Su objetivo principal es optimizar la experiencia de usuario (UX) evitando la recarga total de la página y garantizando la integridad de las solicitudes mediante bloqueos de UI.

## Información General

* **Tecnología:** JavaScript Nativo (ES6+, Vanilla JS), Async/Await API.
* **Dependencias de Terceros:** SweetAlert2 (`Swal`) para notificaciones contextuales y dinámicas en la interfaz.
* **Controlador Destino:** Vinculado directamente al endpoint expuesto por `CarritoController::add`.

---

## Funcionamiento de la Función

La función `agregarAlCarrito(event, form)` encapsula el control transaccional del lado del cliente a través del siguiente pipeline:

### 1. Interceptación y Bloqueo de Clics Múltiples (Anti-Debounce)
* **`event.preventDefault()`:** Cancela de forma inmediata el comportamiento nativo del navegador de recargar la página tras el submit del formulario.
* **Control de Estado del Botón:** Identifica el botón que gatilló el evento dentro del formulario. Si el botón ya está deshabilitado (`btn.disabled`), rompe la ejecución de forma temprana. En caso contrario, se desactiva físicamente (`btn.disabled = true`) y se inyecta temporalmente el texto `Agregando...`. Esto previene de forma absoluta que clics accidentales o repetitivos generen solicitudes duplicadas al backend e incrementos erróneos de cantidades.

### 2. Despacho Asíncrono de Datos (`Fetch API`)
* **Serialización:** Utiliza el constructor nativo `FormData` para compilar y mapear automáticamente todos los inputs del formulario (como el `product_id`, `cantidad`, tokens CSRF, etc.).
* **Cabeceras Especializadas:** Envía la petición HTTP mediante el método `POST` inyectando las cabeceras `'Accept': 'application/json'` y `'X-Requested-With': 'XMLHttpRequest'`. Esto le comunica a Laravel que la solicitud proviene de una rutina AJAX para que el backend responda con estructuras JSON y no con redirecciones.

### 3. Procesamiento de Respuestas y Reactividad

#### Escenario A: Operación Exitosa (`response.ok && data.success`)
* **Actualización Dinámica del Navbar:** Localiza el elemento `#carritoCountBadge`. Si el servidor retorna un número válido en `data.carrito_count`, muta su contenido de texto de forma reactiva instantánea. Si el contador disminuye a 0 o menos, le añade la clase CSS `.hidden` para ocultar la viñeta visual.
* **Feedback Visual:** Despliega una alerta emergente autoprogramada de SweetAlert2 con un temporizador automático de **1.8 segundos** sin interrumpir la navegación del usuario.

#### Escenario B: Errores de Negocio o Validación (`data.success == false`)
* Despliega un modal imperativo de SweetAlert2 exponiendo la razón exacta retornada por el controlador (por ejemplo: *"Stock insuficiente"* o *"Producto no disponible"*).

#### Escenario C: Fallas de Conexión (`catch (error)`)
* Atrapa problemas a nivel de red, caídas de servidor o fallas de parsing JSON, imprimiendo la traza técnica en la consola y notificando al cliente una alerta genérica de error de comunicación.

### 4. Liberación de Recursos (`finally`)
* El bloque `finally` garantiza, sin importar si la petición fue exitosa o fallida, que el botón del carrito recupere su estado activo original (`disabled = false`) y restaure el contenido textual o los íconos previos (`textoOriginal`), dejando la interfaz lista para próximas interacciones.

---

# Gestor de Interacciones del Carrito (`DOMContentLoaded`)

Este script se ejecuta automáticamente una vez que el DOM de la página web ha sido completamente cargado (`DOMContentLoaded`). Su propósito es centralizar e implementar las rutinas asíncronas (AJAX) para la administración operativa de los productos dentro de la vista del carrito de compras, encapsulando las funciones de mutación de cantidades, eliminación unitaria y vaciado total de la sesión.

## Información General

* **Ámbito:** Frontend de la Vista del Carrito (`carrito.blade.php`).
* **Tecnología:** JavaScript Asíncrono (`Async/Await`, `Fetch API`).
* **Dependencias:** Tailwind CSS (Clases de animación como `.animate-spin`), SweetAlert2 (`Swal`).

---

##  Métodos y Arquitectura Lógica

Las funciones están expuestas al objeto global `window` al final de la carga del documento para permitir su invocación directa desde los atributos `onsubmit` del HTML dinámico.

### 1. `actualizarCantidad(event, form)`
Actualiza e incrementa/decrementa las unidades de un ítem directamente desde la fila del carrito mediante verbos HTTP simulados.

* **Efecto de Carga (Spinner):** Obtiene el contenedor `.cantidad-display`. Antes de despachar la petición, respalda el valor numérico actual e inyecta un ícono SVG animado (`animate-spin`). Esto provee retroalimentación visual al cliente y enmascara el tiempo de latencia del servidor.
* **Mutación de Datos y Spoofing:** Compila los datos vía `FormData`. Establece el valor de la nueva cantidad tomando el `value` del botón pulsado (`event.submitter`) e inyecta la cabecera `formData.append('_method', 'PUT')` para que Laravel procese la solicitud HTTP `POST` bajo el verbo estructurado `PUT`.
* **Sincronización Reactiva (Éxito):**
  * Reemplaza el spinner con el número final validado por el backend (`data.cantidad`).
  * Recalcula los atributos de los botones $+/-$ de la fila para que el siguiente clic envíe los valores enteros correctos ($Cantidad - 1$ y $Cantidad + 1$).
  * Multiplica el precio base almacenado en el atributo de datos del formulario (`form.dataset.precio`) por la nueva cantidad para actualizar el subtotal de la fila de forma inmediata.
  * Llama a la rutina `actualizarResumen(data)`.
* **Control de Errores (Falta de Stock):** Si la base de datos ERP reporta que no hay unidades disponibles, restaura el número previo, recalcula los valores de los botones y despliega un modal imperativo de SweetAlert2 exponiendo la restricción técnica.

### 2. `eliminarProducto(event, form)`
Remueve de forma definitiva un ítem específico del carrito de compras sin alterar el resto de la interfaz de manera disruptiva.

* **Mecanismo:** Fuerza el método de envío a `DELETE` mediante `_method` y despacha la petición AJAX.
* **Transición de Desvanecimiento (Fade Out):** Al recibir la confirmación del backend, localiza la fila contenedora más cercana empleando `form.closest('.item-carrito')`. Aplica una transición nativa de CSS (`opacity 0.3s ease`) reduciendo la opacidad a `0`.
* **Destrucción del Nodo:** Tras cumplirse los 300ms de la animación, remueve físicamente el nodo del árbol DOM (`fila.remove()`).
* **Validación de Estado Vacío:** Cuenta el número de filas restantes bajo `#listaProductos`. Si el contador es igual a `0`, oculta el contenedor principal del carrito (`#carritoContenido`) y activa la vista alterna `#carritoVacio`.

### 3. `vaciarCarrito(event, form)`
Limpia de forma absoluta la colección de productos almacenados en la sesión web activa del cliente.

* **Mecanismo:** Despacha un llamado asíncrono directo al endpoint de limpieza masiva.
* **Reset de Interfaz:** Oculta los bloques del panel de compras, visualiza la alerta estática de carrito vacío y manipula los componentes del header (`#carritoCountBadge` y `#carritoHeaderCount`) forzándolos a ocultarse mediante la inyección de la clase `.hidden` y reseteando sus contadores a `0`.

### 4. `actualizarResumen(data)`
Función interna utilitaria encargada de propagar los cambios financieros devueltos por el servidor hacia los componentes de la interfaz de usuario.

* **Campos Modificados:** * `#resumen-subtotal` ──► Muestra el nuevo valor neto (`$data.subtotal`).
  * `#resumen-iva` ──► Muestra el impuesto calculado (`$data.iva`).
  * `#resumen-total` ──► Refleja el monto final de facturación (`$data.total`).
  * Modifica el indicador del navbar de forma gramatical: si `carrito_count` es `1` concatena la palabra `"producto"`, caso contrario añade `"productos"`.

---

# Interceptación de Pasarela de Pagos (Nuvei)

Este script se ejecuta tras la carga completa del DOM (`DOMContentLoaded`) en la pantalla de finalización de compra (*Checkout*). Su propósito es interceptar el envío del formulario de pago para desplegar una alerta informativa obligatoria únicamente cuando el usuario selecciona **Tarjeta de Crédito** como método de transacción a través de la pasarela **Nuvei**.

##  Información General

* **Ámbito:** Pantalla de Checkout / Pasarela de Pagos (`checkout.blade.php`).
* **Tecnología:** JavaScript Nativo (ES6, Vanilla JS), Event Listeners.
* **Dependencias:** SweetAlert2 (`Swal`) para la ventana modal de confirmación.
* **Integración:** Actúa como un paso de validación previo a la redirección externa hacia el proveedor de pagos Nuvei.

---

##  Funcionamiento y Lógica Técnica

El script automatiza un flujo de control preventivo basado en atributos de datos del HTML:

### 1. Inicialización y Validación de Entorno
* El script busca de forma mandatoria los elementos `#payment-form` (formulario de procesamiento) y `#tipo_pago` (elemento select o campo de selección del método de pago).
* **Cláusula de Escape:** Si alguno de los dos elementos no se encuentra en el DOM actual (por ejemplo, al navegar en el catálogo o el perfil), la ejecución se rompe inmediatamente mediante un `return` temprano, evitando excepciones por referencias nulas en otras páginas.

### 2. Evaluación Condicional del Método de Pago
Al dispararse el evento `submit` del formulario, el script evalúa dinámicamente la opción activa:
* Obtiene el elemento seleccionado: `tipoPago.options[tipoPago.selectedIndex]`.
* Evalúa la bandera de configuración: `dataset.tarjeta === '1'`. Esto significa que el backend de Laravel marca con un atributo personalizado `data-tarjeta="1"` a aquellas opciones de pago que correspondan a pasarelas de crédito/débito.

### 3. Bifurcación del Flujo de Envío
* **Métodos de Pago Estándar:** Si la opción seleccionada **no** es una tarjeta (ej. Transferencia Directa, Efectivo), el script ejecuta un `return` sin alterar el evento. El formulario se despacha de forma nativa e inmediata al backend.
* **Método Tarjeta de Crédito (Nuvei):** Si se confirma que es tarjeta, se ejecuta `e.preventDefault()` para suspender temporalmente el viaje del formulario hacia el servidor, congelando el estado del navegador.

### 4. Confirmación del Cliente e Invocación Nativa
* Se despliega un cuadro de diálogo SweetAlert2 de tipo informativo (`icon: 'info'`). Este advierte al usuario con instrucciones explícitas sobre la necesidad obligatoria de salvaguardar y subir posteriormente una captura o archivo digital de su comprobante físico o digital para que el departamento contable procese el pedido (vinculado internamente a la estructura de la tabla `DBA.PW_ADJUNTO_WEB`).
* **Resolución de Promesa (`.then`):**
  * **Si el usuario cancela:** La modal se cierra y el flujo se detiene. El cliente permanece seguro en la pantalla de checkout para modificar sus datos o su método de pago.
  * **Si el usuario confirma (`result.isConfirmed`):** Se invoca el método nativo `form.submit()`. Al ser un llamado directo por método del prototipo, se salta el Event Listener actual y envía los datos directamente al pasaporte de pagos de Nuvei.

---

# Subida y Previsualización de Comprobantes

Este script se ejecuta tras la carga completa del DOM (`DOMContentLoaded`) en la interfaz de gestión de pedidos o de carga de adjuntos. Su propósito principal es gestionar el flujo de carga de archivos (comprobantes de pago) para interactuar directamente con la tabla `DBA.PW_ADJUNTO_WEB`, proveyendo previsualización en tiempo real, limpieza de archivos seleccionados y validación imperativa previa al envío definitivo del formulario.

##  Información General

* **Ámbito:** Interfaz de Carga de Comprobantes (`subir-comprobante.blade.php`).
* **Tecnología:** JavaScript Nativo (ES6, Vanilla JS), `FileReader` API.
* **Dependencias:** Tailwind CSS (Clases lógicas de ocultamiento dinámico como `.hidden`), SweetAlert2 (`Swal`).

---

##  Métodos y Escuchadores de Eventos (Event Listeners)

El script encapsula tres interacciones críticas del ciclo de vida del archivo dentro del formulario:

### 1. Previsualización Dinámica del Archivo (`change`)
Maneja el evento de selección de archivos sobre el input de tipo archivo (`#comprobante`).
* **Cláusula de Escape Temprana:** Si la referencia del input no existe en la vista actual, el script detiene su ejecución inmediatamente para evitar excepciones.
* **Extracción de Metadatos:** Al elegir un elemento, lee el primer objeto binario del arreglo (`this.files[0]`). Remueve la clase `.hidden` de los componentes contenedores `#fileInfo` y `#previewContainer` para mostrarlos en la UI.
* **Cálculo de Peso en MB:** Realiza la conversión matemática del tamaño del archivo dividiendo los bytes leídos de forma sucesiva:
  
  $$\text{Tamaño (MB)} = \frac{\text{file.size}}{1024 \times 1024}$$
  
  Fuerza el resultado a un string de dos decimales fijados (`.toFixed(2)`).
* **Renderización Asíncrona (`FileReader`):** Instancia un objeto nativo `FileReader`. Define la propiedad asíncrona `reader.onload` para capturar el resultado de la lectura e inyectarlo directamente en el atributo `src` de la etiqueta `#previewImage`. Finalmente, ejecuta `reader.readAsDataURL(file)` para codificar la imagen del comprobante en una cadena Base64, permitiendo que el cliente vea la imagen antes de subirla.

### 2. Remoción de Archivos del Input (`click`)
Escucha los clics sobre el botón `#btnEliminarArchivo` para restaurar la interfaz al estado inicial si el cliente se equivocó de documento.
* **Limpieza de Referencia:** Vacía el valor físico del input de archivos (`inputComprobante.value = ''`). Esto es indispensable para permitir que el navegador vuelva a disparar el evento `change` si el usuario selecciona exactamente el mismo archivo más adelante.
* **Reset de UI:** Oculta nuevamente los contenedores de información y previsualización inyectando la clase `.hidden` y purga el enlace temporal de la imagen asignando `src = ''`.

### 3. Validación y Confirmación de Envío (`submit`)
Intercepta el submit de `#formComprobante` para funcionar como un guardián de calidad de datos.
* **e.preventDefault():** Detiene la transmisión inmediata hacia el servidor de Laravel.
* **Filtro de Archivo Vacío:** Verifica si existe algún objeto en el arreglo `.files[0]`. Si no se detecta ningún archivo cargado, interrumpe el flujo y dispara un modal SweetAlert2 de advertencia (`icon: 'warning'`) solicitando la selección obligatoria de un documento.
* **Confirmación Dual:** Si el archivo es válido, despliega una alerta SweetAlert2 parametrizada consultando de manera explícita si el nombre del archivo (`archivo.name`) coincide con el comprobante emitido por la entidad bancaria. Si el usuario confirma la acción (`result.isConfirmed`), se ejecuta el método nativo `form.submit()`, enviando la cadena pesada de datos para su almacenamiento en el campo `foto` de tipo `LONG VARCHAR`.

---

# Gestor de Lista de Deseos (`toggleWishlist` & `eliminarDeWishlist`)

Este script implementa la lógica asíncrona para la administración de la lista de deseos (*Wishlist*) de la plataforma web. Permite interactuar con la entidad de persistencia del backend (`DBA.PW_WISHLIST`) mediante peticiones AJAX, optimizando la respuesta visual sin forzar recargas completas de pantalla.

##  Información General

* **Ámbito:** Catálogo general, tarjetas de productos relacionados y vista interna de lista de deseos (`wishlist.blade.php`).
* **Tecnología:** JavaScript Asíncrono (`Async/Await`, `Fetch API`).
* **Dependencias Visuales:** Tailwind CSS (Clases utilitarias como `.hidden`, `.group`, e indicadores de transición).

---

##  Métodos y Arquitectura Lógica

Ambas funciones manejan el ciclo de vida de los eventos asíncronos y están pensadas para ser invocadas de forma directa desde los formularios de las vistas Blade.

### 1. `toggleWishlist(event, form)`
Encargada de alternar el estado (agregar o quitar) de un ítem favorito desde las tarjetas expuestas en el catálogo general.

* **Control Clones (Anti-Debounce):** Recupera el botón tipo `submit` del formulario. Si se encuentra deshabilitado, aborta. Si está activo, lo congela (`btn.disabled = true`) para evitar el spam de solicitudes repetitivas hacia la base de datos.
* **Transmisión de Datos:** Compila los inputs ocultos (como `empresa` y `codigo_item`) con `FormData` y envía la petición vía `POST`.
* **Conmutación del Estado del Ícono (Relleno vs. Contorno):** Al recibir una respuesta exitosa, lee la bandera lógica booleana `data.en_wishlist`. Muta las clases de visibilidad utilizando `.classList.toggle('hidden', ...)` de la siguiente manera:
  * Si el ítem está guardado, remueve la clase `.hidden` de `.icon-filled` (ícono relleno) y se la añade a `.icon-outline` (ícono con contorno).
  * Si el ítem fue removido, realiza el proceso inverso de ocultamiento.
* **Sincronización Global:** Actualiza de forma dinámica el contador flotante del navbar `#wishCountBadge` con el número actualizado devuelto por el servidor (`data.wish_count`), ocultándose por completo si el valor llega a cero.

### 2. `eliminarDeWishlist(event, form)`
Especializada para la página dedicada "Mi Lista de Deseos". Se encarga de purgar de forma definitiva el nodo de un producto y evaluar el estado de la grilla de visualización.

* **Desvanecimiento de Tarjeta (Fade Out):** Al validar la eliminación exitosa en el backend, localiza la estructura contenedora superior más cercana con `form.closest('.group')`. Le inyecta estilos dinámicos en línea para una animación suavizada de desvanecimiento: `transition = 'opacity 0.3s ease'` y `opacity = '0'`.
* **Destrucción y Reevaluación de la UI:** Espera los 300ms correspondientes a la animación para remover físicamente el nodo del árbol DOM (`card.remove()`). Acto seguido, comprueba si `data.wish_count <= 0`. De cumplirse, oculta el grid principal (`#wishlistGrid`) y expone el estado vacío con ilustraciones y mensajes amigables (`#wishlistEmptyState`).
* **Actualización Textual de Cabecera:** Sincroniza el elemento contador de la cabecera del módulo (`.bg-red-100.text-red-600`) adaptando la gramática de forma automatizada (`producto` para singular, `productos` para plural).

---
