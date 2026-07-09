## Helpers

El proyecto incluye funciones auxiliares en `app/Helpers/CompanyHelper.php` diseñadas para gestionar la empresa activa (multi-empresa), optimizar las consultas a la base de datos (SQL Anywhere) usando caché, y construir las URLs completas de las imágenes del servidor multimedia.

---

### Detalles de las Funciones

#### 1. `currentCompany()`
Obtiene el código de la empresa o sucursal que está activa en el sistema.
* **¿Cómo funciona internamente?:** Lee el archivo de configuración de Laravel (`config/app.php`), el cual toma el valor de la variable de entorno `COMPANY_CODE` en tu archivo `.env`.
* **¿Qué devuelve?:** Un `string` con el código de la empresa activa (por ejemplo, `"01"`). Si no hay ninguna configurada, devuelve `'?'` por defecto.

---

#### 2. `companyRuc(?string $companyCode = null)`
Busca el RUC de una empresa de forma rápida y optimizada utilizando la caché.
* **¿Cómo funciona internamente?:** Si no se le pasa un código de empresa, usa el de la empresa actual. Primero revisa si el RUC ya está guardado en la caché de Laravel para no saturar la base de datos. Si no está en caché, se conecta por ODBC a SQL Anywhere y hace la consulta `SELECT TOP 1 ruc FROM GE_EMPRESA WHERE codigo = ?`. Al encontrarlo, guarda el resultado en caché por 12 horas.
* **¿Qué devuelve?:** Un `string` con el RUC de la empresa. Si por alguna razón la empresa no existe en la base de datos, devuelve el mismo código que recibió como medida de respaldo para evitar errores.

---

#### 3. `companyImageBaseUrl()`
Construye la URL base donde se almacenan las imágenes de los productos de la empresa.
* **¿Cómo funciona internamente?:** Junta la URL del servidor de imágenes y la ruta de productos configuradas en el sistema, le inyecta el RUC obtenido mediante `companyRuc()`, y limpia las barras diagonales (`/`) del final para que la ruta quede bien estructurada.
* **¿Qué devuelve?:** Un `string` con la URL base formateada de la siguiente manera:  
  `{IMAGE_SERVER_BASE_URL}/{RUC}/{IMAGE_SERVER_PATH_PRODUCTS}/`

---

#### 4. `companyLogoUrl()`
Genera la URL completa para mostrar el logo de la empresa activa.
* **¿Cómo funciona internamente?:** Toma el código de la empresa actual y hace una consulta directa vía ODBC a la tabla `DBA.ge_empresa` para traer el nombre del archivo del logo (`logo_tienda`) y el RUC. Si la empresa existe y tiene un logo asignado, une estos datos con la URL del servidor multimedia.
* **¿Qué devuelve?:** Un `string` con la URL directa para mostrar el logo en el navegador, o `null` si la empresa no tiene un logo registrado en la base de datos.

---

#### 5. `productImageUrl(?string $filename)`
Genera la URL lista para usar de la imagen de un producto.
* **¿Cómo funciona internamente?:** Verifica que el nombre del archivo no venga vacío. Si es válido, le quita las barras iniciales para que no se dupliquen y lo concatena al final de la ruta base que genera `companyImageBaseUrl()`.
* **¿Qué devuelve?:** El `string` con la URL lista para poner en la etiqueta `<img src="...">` del frontend, o `null` si el archivo estaba vacío (lo que sirve para mostrar una imagen por defecto si el producto no tiene foto).

---

#### 6. `presentationImageUrl(?string $filename, ?string $codigoProducto = null)`
Genera la URL de la imagen de presentación de un producto, soportando carpetas específicas.
* **¿Cómo funciona internamente?:** Revisa si se envió el parámetro opcional `$codigoProducto`. Si se incluye, modifica la URL para meter el código del producto como una subcarpeta intermedia; si no se envía, usa la ruta normal de productos.
* **¿Qué devuelve?:** Un `string` con la URL formateada (por ejemplo: `{URL_Base}/{codigoProducto}/{archivo}`), o `null` si no se envió un nombre de archivo válido.

---

#### 7. `companyDefaultOrderType(string $tipo = 'proforma_web')`
Traduce el tipo de pedido del sistema al código que requiere la base de datos interna.
* **¿Cómo funciona internamente?:** Utiliza una lista interna (arreglo) que mapea el nombre del flujo con su sigla correspondiente.
* **¿Qué devuelve?:** Un `string` de dos letras según el caso:
  - `'proforma_web'` $\rightarrow$ `'TW'`
  - `'invoice'` $\rightarrow$ `'FC'`
  - Si el tipo de documento no coincide con ninguno, devuelve `'TW'` de forma automática por seguridad.
