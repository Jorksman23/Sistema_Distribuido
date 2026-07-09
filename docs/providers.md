## Service Providers

El proyecto utiliza Service Providers personalizados en `app/Providers/` para precargar y compartir datos estructurales de forma global hacia las vistas de Blade, evitando consultas repetitivas en los controladores.

---

### `AppServiceFooter`

Este Provider se encarga de recopilar toda la información de contacto, redes sociales y canales de atención de la empresa activa para inyectarlos automáticamente en el pie de página (`footer`) del sitio web.

* **¿Cómo funciona internamente?:** Se ejecuta de forma síncrona en el arranque de la aplicación a través del método `boot()`. 
  1. Realiza una consulta directa vía `odbc` a la tabla `DBA.ge_empresa` para traer los canales de comunicación principales filtrando por la empresa actual obtenida del helper `currentCompany()`.
  2. Consulta la tabla `DBA.pw_parametros` para extraer las configuraciones de enlaces web.
  3. Convierte el resultado de los parámetros en una colección de Laravel y utiliza el método `pluck()` para transformarlo en un mapa asociativo de tipo `'CLAVE' => 'VALOR'` (por ejemplo, asocia la clave `'FB'` con el enlace de Facebook).
  4. Utiliza la fachada `View::share()` para registrar de forma global la variable `$footerData`, permitiendo que cualquier archivo Blade acceda a este arreglo sin importar qué controlador renderizó la página.

* **¿Qué variables y datos expone globalmente?:**
  Inyecta el arreglo asociativo `footerData` con los siguientes campos listos para usar en el frontend:
  - `correo` / `direccion`: Datos de ubicación y contacto del Core de la empresa.
  - `celular` / `telefono2` / `celular_rl`: Números telefónicos institucionales.
  - `facebook` / `instagram` / `twitter` / `youtube`: Enlaces dinámicos mapeados desde las siglas del diccionario de parámetros (`FB`, `IG`, `TW`, `YT`).
  - `whatsappNumbers`: Un sub-arreglo estructurado que distribuye los números telefónicos en roles mapeados (`Asesor1`, `Asesor2`, `Asesor3`) para el despliegue del layout flotante o botones de mensajería instantánea.

### `AppServiceHome`

Este Provider se encarga de estructurar y centralizar los recursos visuales, textos de marketing y banderas comerciales que dan forma a la página principal (`body` / `home`) del sitio web de manera dinámica.

* **¿Cómo funciona internamente?:** Se inicializa en el arranque de la plataforma mediante el método `boot()`.
  1. Identifica el identificador de la tienda a través de `currentCompany()`.
  2. Consume el modelo Eloquent `Parametro` ejecutando una consulta filtrada para extraer los registros pertenecientes a la sucursal. Reduce la estructura de la consulta utilizando un método `pluck('descripcion', 'parametro')` para conformar un diccionario asociativo indexado por códigos de configuración interna (ej. `'PB1'`, `'BP3'`).
  3. Ejecuta la inyección global utilizando `View::share()` bajo la variable `$homeData`, evitando sobrecargar los controladores principales con consultas repetitivas de diseño.

* **¿Qué variables y datos expone globalmente?:**
  Inyecta el arreglo asociativo `homeData` con los siguientes bloques de datos listos para el renderizado del cuerpo de la página:
  - `publicidad`: Enlace o ruta del banner publicitario principal mapeado desde la clave `PB1`.
  - `slider`: Un arreglo secuencial indexado con hasta 5 banners dinámicos de carrusel extraídos desde los parámetros `BP3` al `BP7`.
  - `tituloSeccion` / `masVendidos` / `ofertas` / `destacados`: Cadenas de texto dinámicas para las etiquetas de títulos del catálogo y bloques comerciales destacados (`TL18`, `TL2`, `TL3`, `TL7`).
  - `anuncios`: Sub-arreglo con rutas de imágenes o contenidos informativos complementarios (`AN1` al `AN4`).
  - `informativo`: Texto descriptivo institucional o condiciones de compra obtenido desde `PB3`.
  - `subtitulos`: Colección de textos secundarios destinados a la organización de categorías dentro del index (`TL4`, `TL5`, `TL6`).

### `AppServiceProvider`

Este es el proveedor central de servicios de la plataforma. Se encarga de forzar esquemas de comunicación seguros en fases de desarrollo, registrar las funciones globales de ayuda (Helpers), inyectar la identidad de la corporación e inicializar contadores dinámicos del perfil del usuario (Wishlist y Carrito de compras) mediante compositores de vistas.

* **¿Cómo funciona internamente?:** Se inicializa al arrancar la aplicación utilizando el método `boot()`.
  1. **Control de Túnel Seguro (ngrok):** Evalúa de forma estricta la URL base configurada en el sistema. Si detecta la cadena `'ngrok'`, fuerza los esquemas de rutas hacia HTTPS de manera automática. **Esto es estrictamente obligatorio para pruebas locales (como validación de webhooks y pasarelas de pago). Al pasar a producción, el propio servidor web (Nginx/Apache) o balanceador gestiona el SSL, por lo que estas líneas quedan inactivas en el entorno real o se comentan si el servidor no requiere redirecciones internas manuales.**
  2. **Inicialización de Helpers:** Carga mediante `require_once` los métodos del archivo `CompanyHelper.php`.
  3. **Inyección Global Dinámica:** Asigna el nombre comercial global de la empresa activa en la variable `empresaNombre` consumiendo el método estático del modelo `Empresa`.
  4. **Compositor de Vistas Global (`View::composer('*')`):** Monitorea la sesión del usuario de forma proactiva. Si existe un inicio de sesión (`user_id`), recupera los datos de los ítems favoritos y del carrito desde los repositorios correspondientes, almacenando las claves resultantes en la caché de la sesión (`wish_codes` y `carrito_count`) para mitigar la redundancia de peticiones SQL en cada cambio de vista o recarga de la página.

* **¿Qué variables y datos expone globalmente?:**
  - `empresaNombre`: Un `string` permanente con la identidad de la sucursal activa.
  - `wishCount` / `wishCodes`: Número entero con la sumatoria total de favoritos y un arreglo con los códigos de los ítems añadidos.
  - `carritoCount`: Cantidad exacta de productos agregados por el cliente a su bolsa de compras activa.
  - `homeData`: Arreglo asociativo con los parámetros comerciales e informativos requeridos para el index principal.
