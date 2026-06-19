/* ==========================================================
   PW_GE_USUARIO
   Se amplió el tamaño del campo contraseña para almacenar
   contraseñas cifradas mediante Hash::make().
   ========================================================== */
ALTER TABLE DBA.PW_GE_USUARIO
MODIFY contrasena VARCHAR(255);

-- Añadir nuevo campo para que verifique el email del usuaio
ALTER TABLE pw_ge_usuarios
ADD email_verified_at DATETIME NULL;

-- Creacion de tabla para el reseteo de contraseñas
CREATE TABLE pw_password_resets (
    empresa     VARCHAR(3)    NOT NULL,
    codigo      VARCHAR(8)   NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    token_hash  VARCHAR(64)   NOT NULL,
    created_at  DATETIME      NOT NULL,
    PRIMARY KEY (empresa, codigo)
);

-- Relacion entre pw_password_resets e in_cliente para asegurar que solo se puedan generar tokens de reseteo para clientes existentes
ALTER TABLE pw_password_resets
ADD CONSTRAINT fk_pw_resets_cliente
FOREIGN KEY (empresa, codigo)
REFERENCES in_cliente (empresa, codigo);

/* ==========================================================
   PW_GE_USUARIOS / IN_CLIENTE

   Trigger trg_crear_cliente encargado de sincronizar automáticamente
   los usuarios registrados en la tienda web con la
   tabla maestra de clientes del sistema ERP.

   Eventos:
   - INSERT
   - UPDATE

   Momento de ejecución:
   - AFTER

   Tabla origen:
   - PW_GE_USUARIOS

   Tabla destino:
   - IN_CLIENTE
   ========================================================== */
ALTER TRIGGER "trg_crear_cliente"
AFTER INSERT, UPDATE ON "DBA"."pw_ge_usuarios"
REFERENCING NEW AS new_row OLD AS old_row
FOR EACH ROW
BEGIN

    IF INSERTING THEN
        BEGIN
            IF NOT EXISTS (
                SELECT 1 FROM "DBA"."in_cliente"
                WHERE codigo  = CAST(new_row.user_id AS VARCHAR(8))
                AND   empresa = new_row.empresa
            ) THEN
                INSERT INTO "DBA"."in_cliente" (
                    codigo,
                    nombre,
                    cedula_ruc,
                    e_mail,
                    estado,
                    empresa,
                    direccion1,
                    telefono,
                    vendedor
                ) VALUES (
                    CAST(new_row.user_id AS VARCHAR(8)),
                    new_row.nombre,
                    new_row.cedula_ruc,
                    new_row.email,
                    SUBSTRING(new_row.estado, 1, 1),
                    new_row.empresa,
                    new_row.direccion,
                    new_row.telefono,
                    '1'
                );
            ELSE
                -- Si ya existe el codigo, actualiza en lugar de insertar
                UPDATE "DBA"."in_cliente"
                SET
                    nombre     = new_row.nombre,
                    cedula_ruc = new_row.cedula_ruc,
                    e_mail     = new_row.email,
                    estado     = SUBSTRING(new_row.estado, 1, 1),
                    direccion1 = new_row.direccion,
                    telefono   = new_row.telefono
                WHERE codigo  = CAST(new_row.user_id AS VARCHAR(8))
                AND   empresa = new_row.empresa;
            END IF;
        END;

    ELSEIF UPDATING THEN
        UPDATE "DBA"."in_cliente"
        SET
            nombre     = new_row.nombre,
            cedula_ruc = new_row.cedula_ruc,
            e_mail     = new_row.email,
            estado     = SUBSTRING(new_row.estado, 1, 1),
            empresa    = new_row.empresa,
            direccion1 = new_row.direccion,
            telefono   = new_row.telefono
        WHERE codigo  = CAST(old_row.user_id AS VARCHAR(8))
        AND   empresa = old_row.empresa;

    END IF;

END

/* ==========================================================
   PW_CARRITO_WEB
   Campo para almacenar la ubicación/sucursal desde donde
   se tomará el inventario del producto.
   ========================================================== */
ALTER TABLE DBA.PW_CARRITO_WEB
ADD ubicacion VARCHAR(3) NULL;

/* ==========================================================
   PW_CARRITO_WEB
   Campo para asociar los productos del carrito con la
   orden generada una vez procesada la compra.
   ========================================================== */
ALTER TABLE DBA.PW_CARRITO_WEB
ADD order_id INTEGER NULL;

/* ==========================================================
   PW_WISHLIST
   Tabla para almacenar la lista de deseos (favoritos)
   de cada cliente.
   ========================================================== */
CREATE TABLE DBA.PW_WISHLIST (
    id_wish INTEGER DEFAULT AUTOINCREMENT,
    cod_cliente VARCHAR(20) NOT NULL,
    codigo_item VARCHAR(20) NOT NULL,
    nombre VARCHAR(250) NULL,
    pvp3 NUMERIC(14,4) NULL,
    imagen VARCHAR(100) NULL,
    empresa VARCHAR(5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT TIMESTAMP NULL,
    PRIMARY KEY (id_wish)
);

/* ==========================================================
   PW_WISHLIST
   Índice para optimizar la búsqueda de favoritos por
   empresa y cliente.
   Mejora el rendimiento de consultas como:
   WHERE empresa = ? AND cod_cliente = ?
   ========================================================== */
CREATE INDEX "idx_pw_wishlist_cliente"
ON "DBA"."pw_wishlist" (
    "empresa",
    "cod_cliente"
);

/* ==========================================================
   PW_ADJUNTO_WEB
   Tabla para almacenar los archivos adjuntos asociados
   a una orden web, principalmente comprobantes de pago
   cargados por el cliente.
   ========================================================== */
CREATE TABLE DBA.PW_ADJUNTO_WEB (

    empresa VARCHAR(3) NOT NULL,
    secuencia INTEGER NOT NULL DEFAULT AUTOINCREMENT,

    cod_orden VARCHAR(15) NOT NULL,
    cod_cliente INTEGER NULL,

    foto LONG VARCHAR NOT NULL,
    foto_id VARCHAR(30),

    nombre_archivo VARCHAR(255),
    tipo_archivo VARCHAR(20),

    created_at TIMESTAMP DEFAULT CURRENT TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT TIMESTAMP,

    PRIMARY KEY (empresa,secuencia)

) IN "system";


/* ==========================================================
   CXC_FORMA_PAGO
   Campo para relacionar cada forma de pago con una cuenta
   bancaria específica.
   ========================================================== */
ALTER TABLE DBA.CXC_FORMA_PAGO
ADD cod_cuenta_banco VARCHAR(20) NULL;

/* ==========================================================
   CXC_FORMA_PAGO
   Actualización de datos para asociar automáticamente
   cada forma de pago con la cuenta bancaria correspondiente.
   ========================================================== */
UPDATE CXC_FORMA_PAGO fp
SET cod_cuenta_banco = (
    SELECT TOP 1 cb.cod_sistema
    FROM TE_CUENTAS_BANCOS cb
    WHERE cb.empresa = fp.empresa
      AND cb.cta_contable = fp.cuenta
);

/* ==========================================================
   CXC_FORMA_PAGO
   Relacionar forma de pago TRANSFERENCIA BAN. INTERNACION
   con la cuenta bancaria Banco Internacional (cod_sistema 1101002004000
   No se encontro mas relaciones que concuerden habria que ajustar la tabla te_cuentas_bancos
   con datos que sean del cliente y se adapte a la forma de pago.
   ========================================================== */
UPDATE DBA.cxc_forma_pago
SET cod_cuenta_banco = 1101002004000
WHERE empresa = '005'
AND forma_pago LIKE '%INTERNACION%';

/* ==========================================================
   ge_empresa
  Añadir Campos adicionales para guardar informacion de la empresa
   ========================================================== */
ALTER TABLE DBA.ge_empresa
ADD direccion_tienda LONG VARCHAR NULL,
ADD celular LONG VARCHAR NULL,
ADD telefono2 LONG VARCHAR NULL,
ADD celular_rl LONG VARCHAR NULL,
ADD logo_tienda LONG VARCHAR NULL;


