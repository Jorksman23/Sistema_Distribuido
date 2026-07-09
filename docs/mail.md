## Mail

El directorio `Mail` concentra la definición de todos los correos transaccionales gestionados de forma nativa por el framework. Centraliza la configuración de cabeceras, firmas de seguridad y el empaquetado de metadatos antes de su distribución a los motores de renderizado o APIs de despacho.

---

### Detalles de las Clases y Componentes

#### 1. `PasswordResetMail`
Componente especializado (Mailable) encargado de dar soporte al flujo de recuperación de accesos de la tienda web.
* **¿Cómo funciona internamente?:** Inicializa la instancia capturando el correo de destino, el token de seguridad y el objeto con los datos del cliente. Al invocar el método `build()`, define de manera encadenada el asunto (`subject`), vincula la plantilla HTML base alojada en `resources/views/email/password_reset.blade.php` e inyecta dinámicamente una URL estructurada hacia el formulario mediante el resolvedor de rutas del sistema (`route()`).
* **¿Qué devuelve?:** Un objeto de tipo `Mailable` configurado y listo para ser procesado por el servicio de mensajería (o enviado a colas asíncronas), exponiendo sus propiedades públicas de forma directa en el template de destino.
