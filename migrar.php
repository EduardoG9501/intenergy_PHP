<?php
/**
 * MIGRATION SCRIPT - Intenergy Sistema Inventario
 * Ejecutar desde el navegador: https://tu-dominio.com/migrar.php
 * Se puede ejecutar multiples veces de forma segura (idempotente).
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/clases/Conexion.php';

$c = new conectar();
$conexion = $c->conexion();

if (!$conexion) {
    die("Error de conexion: " . mysqli_connect_error());
}

$resultados = [];

function ejecutarSQL($conexion, $sql, &$resultados) {
    $resultado = @mysqli_query($conexion, $sql);
    if ($resultado === TRUE) {
        return true;
    } else {
        $error = mysqli_error($conexion);
        // Ignorar errores de "table already exists" o "duplicate key"
        if (stripos($error, 'already exists') !== false || 
            stripos($error, 'duplicate') !== false ||
            stripos($error, 'duplicate key') !== false) {
            return true;
        }
        $resultados[] = ['sql' => substr($sql, 0, 80) . '...', 'error' => $error];
        return false;
    }
}

function crearTablaSiNoExiste($conexion, $nombre, $sqlCrear, &$resultados) {
    $check = mysqli_query($conexion, "SHOW TABLES LIKE '$nombre'");
    if (mysqli_num_rows($check) > 0) {
        $resultados[] = ['tabla' => $nombre, 'status' => 'YA EXISTE'];
        return true;
    }
    if (ejecutarSQL($conexion, $sqlCrear, $resultados)) {
        $resultados[] = ['tabla' => $nombre, 'status' => 'CREADA'];
        return true;
    }
    return false;
}

function insertarSiNoExiste($conexion, $tabla, $condicion, $sqlInsert, &$resultados) {
    $check = mysqli_query($conexion, "SELECT COUNT(*) as total FROM $tabla WHERE $condicion");
    $row = mysqli_fetch_assoc($check);
    if ($row && intval($row['total']) > 0) {
        return true;
    }
    return ejecutarSQL($conexion, $sqlInsert, $resultados);
}

// ============================================
// 1. TABLAS BASE
// ============================================

crearTablaSiNoExiste($conexion, 'usuarios', "
    CREATE TABLE usuarios(
        id_usuario int auto_increment,
        nombre varchar(50),
        apellido varchar(50),
        email varchar(50),
        password text(50),
        fechaCaptura date,
        estado bit not null default 1,
        primary key(id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'categorias', "
    CREATE TABLE categorias(
        id_categoria int auto_increment,
        id_usuario int not null,
        nombreCategoria varchar(150),
        fechaCaptura date,
        subcategoria bit not null default 0,
        estado bit not null default 1,
        primary key(id_categoria)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'imagenes', "
    CREATE TABLE imagenes(
        id_imagen int auto_increment,
        id_categoria int not null,
        nombre varchar(500),
        ruta varchar(500),
        fechaSubida date,
        estado bit not null default 1,
        primary key(id_imagen)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'articulos', "
    CREATE TABLE articulos(
        id_producto int auto_increment,
        id_categoria int not null,
        id_subcategoria int not null,
        id_imagen int not null,
        id_usuario int not null,
        nombre varchar(200),
        descripcion varchar(500),
        cantidad int,
        precio float,
        fechaCaptura date,
        estado bit not null default 1,
        codigobarra varchar(50),
        proveedor varchar(200),
        referencia varchar(200),
        id_proveedor int not null,
        primary key(id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'clientes', "
    CREATE TABLE clientes(
        id_cliente int auto_increment,
        id_usuario int not null,
        nombre varchar(200),
        apellido varchar(200),
        direccion varchar(200),
        email varchar(200),
        telefono varchar(200),
        rfc varchar(200),
        estado bit not null default 1,
        primary key(id_cliente)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'bodegas', "
    CREATE TABLE bodegas(
        id_bodega INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        nombreBodega VARCHAR(150),
        identificador VARCHAR(10) UNIQUE,
        es_bodega_secundaria BIT DEFAULT 0,
        id_bodega_principal INT NULL,
        fechaCaptura DATE,
        estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'proveedor', "
    CREATE TABLE proveedor(
        id_proveedor int auto_increment,
        id_usuario int not null,
        tipo_identificacion int not null,
        numero_identificacion varchar(100) not null,
        razon_social varchar(200) not null,
        direccion varchar(200),
        telefono varchar(200),
        email varchar(200),
        nombre_comercial varchar(200),
        tipo_contribuyente int not null,
        agente_retencion varchar(200),
        estado bit not null default 1,
        primary key(id_proveedor)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'genero', "
    CREATE TABLE genero(
        id_genero int auto_increment,
        id_usuario int not null,
        genero varchar(200),
        fechaCaptura date,
        estado bit not null default 1,
        primary key(id_genero)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'cargo_empleado', "
    CREATE TABLE cargo_empleado(
        id_cargo int auto_increment,
        id_usuario int not null,
        cargo varchar(200),
        fechaCaptura date,
        estado bit not null default 1,
        primary key(id_cargo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'empleados', "
    CREATE TABLE empleados(
        id_empleados int auto_increment,
        id_usuario int not null,
        nombres_apellidos varchar(200),
        cedula varchar(100) not null,
        id_genero int not null,
        direccion varchar(200),
        email varchar(200),
        telefono varchar(200),
        fecha_nacimiento date,
        id_cargo int not null,
        estado bit not null default 1,
        primary key(id_empleados)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

// ============================================
// 2. TABLAS DE MOVIMIENTOS
// ============================================

crearTablaSiNoExiste($conexion, 'tipo_movimiento', "
    CREATE TABLE tipo_movimiento(
        id_tipo_movimiento int auto_increment,
        id_usuario int not null,
        tipo varchar(200),
        fechaCaptura date,
        estado bit not null default 1,
        entrada bit not null,
        salida bit not null,
        primary key(id_tipo_movimiento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'sub_tipo_movimiento', "
    CREATE TABLE sub_tipo_movimiento(
        id_sub_tipo_movimiento int auto_increment,
        id_usuario int not null,
        sub_tipo varchar(200),
        fechaCaptura date,
        id_tipo_movimiento int not null,
        estado bit not null default 1,
        primary key(id_sub_tipo_movimiento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'forma_pago', "
    CREATE TABLE forma_pago(
        id_forma_pago int auto_increment,
        id_usuario int not null,
        forma_pago varchar(200),
        fechaCaptura date,
        estado bit not null default 1,
        primary key(id_forma_pago)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'movimientos', "
    CREATE TABLE movimientos(
        id_movimiento int auto_increment PRIMARY KEY,
        id_usuario int not null,
        id_bodega int not null,
        id_tipo_movimiento int not null,
        id_sub_tipo_movimiento int not null,
        id_proveedor int,
        no_documento varchar(200),
        fechaDocumento date,
        id_forma_pago int,
        fechaPago date,
        cant_dia_pago int,
        fechaCaptura date,
        observacion varchar(1000),
        estado bit not null default 1,
        guardarDefinitivo bit not null default 0,
        iva_mto decimal(18,4) not null default 0,
        desc_mto decimal(18,4) not null default 0,
        subtotal_mto decimal(18,4) not null default 0,
        total_mto decimal(18,4) not null default 0,
        iva_porc decimal(18,4) not null default 0,
        desc_porc decimal(18,4) not null default 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'movimientos_detalle', "
    CREATE TABLE movimientos_detalle(
        id_movimiento_detalle int auto_increment PRIMARY KEY,
        id_movimiento int not null,
        id_articulo int not null,
        id_bodega_lugar int not null,
        precio decimal(18,4) not null,
        cantidad decimal(18,4) not null,
        lote varchar(200),
        bonificacion decimal(18,4),
        subtotal decimal(18,4) not null default 0,
        decuento decimal(18,4) not null default 0,
        iva decimal(18,4) not null default 0,
        total decimal(18,4) not null,
        iva_porc decimal(18,4) not null default 0,
        desc_porc decimal(18,4) not null default 0,
        estado bit not null default 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'stock_productos', "
    CREATE TABLE stock_productos(
        id_stock INT AUTO_INCREMENT PRIMARY KEY,
        id_movimiento INT,
        id_tipo_movimiento INT,
        id_sub_tipo_movimiento INT,
        no_documento VARCHAR(200),
        id_bodega_principal INT,
        id_bodega_secundaria INT,
        tipo_movimiento VARCHAR(50),
        sub_tipo_movimiento VARCHAR(50),
        id_movimiento_detalle INT,
        id_producto INT,
        producto VARCHAR(200),
        lote VARCHAR(200),
        precio DECIMAL(18,4),
        cantidad DECIMAL(18,4),
        subtotal DECIMAL(18,4),
        decuento DECIMAL(18,4),
        iva DECIMAL(18,4),
        total DECIMAL(18,4),
        stock DECIMAL(18,4),
        id_stock_credito INT,
        id_usuario INT,
        fecha_captura DATE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

// ============================================
// 3. TABLAS DE OPERACIONES
// ============================================

crearTablaSiNoExiste($conexion, 'stock_productos_bodega', "
    CREATE TABLE stock_productos_bodega(
        id_stock_bodega INT AUTO_INCREMENT PRIMARY KEY,
        id_producto INT NOT NULL,
        id_bodega_principal INT NOT NULL,
        id_bodega_secundaria INT NOT NULL,
        cantidad DECIMAL(18,4) NOT NULL DEFAULT 0,
        fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        estado BIT NOT NULL DEFAULT 1,
        UNIQUE KEY (id_producto, id_bodega_principal)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'permiso_bodega', "
    CREATE TABLE permiso_bodega(
        id_permiso_bodega INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_bodega INT NOT NULL,
        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'proyecto', "
    CREATE TABLE proyecto(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(250) NOT NULL,
        estado bit not null default 1,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'obra', "
    CREATE TABLE obra(
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(250) NOT NULL,
        estado bit not null default 1,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'estado_pedido_materiales', "
    CREATE TABLE estado_pedido_materiales(
        id_estado_pedido_material INT AUTO_INCREMENT,
        estado VARCHAR(50),
        activo BIT NOT NULL DEFAULT 1,
        PRIMARY KEY(id_estado_pedido_material)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

insertarSiNoExiste($conexion, 'estado_pedido_materiales', "estado = 'APROBADO'",
    "INSERT INTO estado_pedido_materiales (estado) VALUES ('APROBADO')", $resultados);
insertarSiNoExiste($conexion, 'estado_pedido_materiales', "estado = 'ANULADO'",
    "INSERT INTO estado_pedido_materiales (estado) VALUES ('ANULADO')", $resultados);
insertarSiNoExiste($conexion, 'estado_pedido_materiales', "estado = 'TERMINADO'",
    "INSERT INTO estado_pedido_materiales (estado) VALUES ('TERMINADO')", $resultados);

crearTablaSiNoExiste($conexion, 'ordenes_trabajo', "
    CREATE TABLE ordenes_trabajo(
        id_orden INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_bodega_principal INT NOT NULL,
        identificador VARCHAR(50) NOT NULL,
        observacion TEXT,
        fechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'pedido_materiales', "
    CREATE TABLE pedido_materiales(
        id_pedido_material INT AUTO_INCREMENT PRIMARY KEY,
        id_orden INT NOT NULL,
        fecha_solicitud DATE NOT NULL,
        observacion TEXT,
        id_estado_pedido_material INT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'pedido_materiales_detalle', "
    CREATE TABLE pedido_materiales_detalle(
        id_pedido_material_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_pedido_material INT NOT NULL,
        id_producto INT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
        Estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'estado_ejecucion_obra', "
    CREATE TABLE estado_ejecucion_obra(
        id_estado_ejecucion_obra INT AUTO_INCREMENT,
        estado VARCHAR(50),
        activo BIT NOT NULL DEFAULT 1,
        PRIMARY KEY(id_estado_ejecucion_obra)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

insertarSiNoExiste($conexion, 'estado_ejecucion_obra', "estado = 'PENDIENTE'",
    "INSERT INTO estado_ejecucion_obra(estado) VALUES ('PENDIENTE')", $resultados);
insertarSiNoExiste($conexion, 'estado_ejecucion_obra', "estado = 'TERMINADO'",
    "INSERT INTO estado_ejecucion_obra (estado) VALUES ('TERMINADO')", $resultados);

crearTablaSiNoExiste($conexion, 'ejecucion_obra', "
    CREATE TABLE ejecucion_obra(
        id_ejecucion_obra INT AUTO_INCREMENT PRIMARY KEY,
        id_orden INT NOT NULL,
        fecha_solicitud DATE NOT NULL,
        feriado BIT NOT NULL DEFAULT 0,
        observacion TEXT,
        id_estado_ejecucion_obra INT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'ejecucion_obra_detalle_materiales_utilizar', "
    CREATE TABLE ejecucion_obra_detalle_materiales_utilizar(
        id_ejecucion_obra_detalle_materiales_utilizar INT AUTO_INCREMENT PRIMARY KEY,
        id_ejecucion_obra INT NOT NULL,
        id_producto INT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
        id_bodega_lugar int not null,
        Contabilizado BIT NOT NULL DEFAULT 0,
        Estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'horas_trabajo_diario_detalle', "
    CREATE TABLE horas_trabajo_diario_detalle(
        id_horas_trabajo_diario_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_ejecucion_obra INT NOT NULL,
        id_empleado INT NOT NULL,
        hora_entrada TIME NOT NULL,
        hora_salida TIME NOT NULL,
        cantidad_horas_normal DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cantidad_horas_extra DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cantidad_horas_extraordinaria DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

// ============================================
// 4. TABLAS DE SOLICITUD / MENU / IVA
// ============================================

crearTablaSiNoExiste($conexion, 'menu', "
    CREATE TABLE menu(
        id_menu INT AUTO_INCREMENT PRIMARY KEY,
        menu_padre VARCHAR(100),
        menu_hijo VARCHAR(100),
        descripcion VARCHAR(200),
        opcion VARCHAR(200),
        icono_padre VARCHAR(100),
        icono_hijo VARCHAR(100),
        estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'iva', "
    CREATE TABLE iva(
        id_iva INT AUTO_INCREMENT PRIMARY KEY,
        porcentaje DECIMAL(5,2) NOT NULL DEFAULT 15.00,
        estado BIT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'solicitud_materiales', "
    CREATE TABLE solicitud_materiales(
        id_solicitud_material INT AUTO_INCREMENT PRIMARY KEY,
        id_orden INT NOT NULL,
        fecha_solicitud DATE NOT NULL,
        observacion TEXT,
        id_estado_solicitud_material INT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'solicitud_materiales_detalle', "
    CREATE TABLE solicitud_materiales_detalle(
        id_solicitud_material_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_solicitud_material INT NOT NULL,
        id_producto INT NOT NULL,
        cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
        id_detalle_articulo INT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

crearTablaSiNoExiste($conexion, 'estado_solicitud_materiales_articulos', "
    CREATE TABLE estado_solicitud_materiales_articulos(
        id_estado_solicitud_articulos INT AUTO_INCREMENT,
        estado VARCHAR(50),
        activo BIT NOT NULL DEFAULT 1,
        PRIMARY KEY(id_estado_solicitud_articulos)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
", $resultados);

// ============================================
// 5. TRIGGERS Y PROCEDURES
// ============================================

// Eliminar trigger viejo si existe, y recrear
ejecutarSQL($conexion, "DROP TRIGGER IF EXISTS actualizar_stock_bodega", $resultados);

$triggerSql = "
CREATE TRIGGER actualizar_stock_bodega AFTER INSERT ON stock_productos
FOR EACH ROW
BEGIN
  DECLARE mov_cantidad DECIMAL(18,4);
  IF NEW.tipo_movimiento = 'ENTRADA' THEN
    SET mov_cantidad = NEW.cantidad;
  ELSEIF NEW.tipo_movimiento = 'SALIDA' THEN
    SET mov_cantidad = -NEW.cantidad;
  ELSE
    SET mov_cantidad = 0;
  END IF;
  INSERT INTO stock_productos_bodega (id_producto, id_bodega_principal, id_bodega_secundaria, cantidad)
    VALUES (NEW.id_producto, NEW.id_bodega_principal, NEW.id_bodega_secundaria, mov_cantidad)
    ON DUPLICATE KEY UPDATE cantidad = cantidad + mov_cantidad, fecha_actualizacion = NOW();
END
";
ejecutarSQL($conexion, $triggerSql, $resultados);

// Procedure
ejecutarSQL($conexion, "DROP PROCEDURE IF EXISTS actualizar_cantidad_articulos_por_movimiento", $resultados);

$procedureSql = "
CREATE PROCEDURE actualizar_cantidad_articulos_por_movimiento(IN movimiento_id INT)
BEGIN
  UPDATE articulos a
    JOIN (
      SELECT sp.id_producto,
        COALESCE(SUM(
          CASE
            WHEN sp.tipo_movimiento = 'ENTRADA' AND sp.cantidad > 0 THEN sp.cantidad
            WHEN sp.tipo_movimiento = 'SALIDA' AND sp.cantidad > 0 THEN -sp.cantidad
            ELSE 0
          END
        ), 0) AS stock_real
      FROM stock_productos sp
      WHERE sp.id_producto IN (
        SELECT id_producto FROM stock_productos WHERE id_movimiento = movimiento_id
      )
      GROUP BY sp.id_producto
    ) s ON a.id_producto = s.id_producto
    SET a.cantidad = s.stock_real;
END
";
ejecutarSQL($conexion, $procedureSql, $resultados);

// ============================================
// 6. DATOS INICIALES (solo si las tablas estan vacias)
// ============================================

insertarSiNoExiste($conexion, 'tipo_movimiento', "tipo = 'ENTRADA'",
    "INSERT INTO tipo_movimiento (id_usuario,tipo,fechaCaptura,entrada,salida) VALUES (1,'ENTRADA','2025-05-21',1,0)", $resultados);
insertarSiNoExiste($conexion, 'tipo_movimiento', "tipo = 'SALIDA'",
    "INSERT INTO tipo_movimiento (id_usuario,tipo,fechaCaptura,entrada,salida) VALUES (1,'SALIDA','2025-05-21',0,1)", $resultados);

insertarSiNoExiste($conexion, 'sub_tipo_movimiento', "sub_tipo = 'COMPRAS'",
    "INSERT INTO sub_tipo_movimiento (id_usuario,sub_tipo,id_tipo_movimiento,fechaCaptura) VALUES (1,'COMPRAS',1,'2025-05-21')", $resultados);
insertarSiNoExiste($conexion, 'sub_tipo_movimiento', "sub_tipo = 'CARGOS'",
    "INSERT INTO sub_tipo_movimiento (id_usuario,sub_tipo,id_tipo_movimiento,fechaCaptura) VALUES (1,'CARGOS',1,'2025-05-21')", $resultados);
insertarSiNoExiste($conexion, 'sub_tipo_movimiento', "sub_tipo = 'DESCARGO'",
    "INSERT INTO sub_tipo_movimiento (id_usuario,sub_tipo,id_tipo_movimiento,fechaCaptura) VALUES (1,'DESCARGO',2,'2025-05-21')", $resultados);

insertarSiNoExiste($conexion, 'forma_pago', "forma_pago = 'CREDITO'",
    "INSERT INTO forma_pago (id_usuario,forma_pago,fechaCaptura) VALUES (1,'CREDITO','2025-05-21')", $resultados);
insertarSiNoExiste($conexion, 'forma_pago', "forma_pago = 'CONTADO'",
    "INSERT INTO forma_pago (id_usuario,forma_pago,fechaCaptura) VALUES (1,'CONTADO','2025-05-21')", $resultados);

// ============================================
// MOSTRAR RESULTADOS
// ============================================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Migracion - Intenergy</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body { padding: 20px; font-family: Arial; }
        .existe { color: #999; }
        .creada { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Migracion de Base de Datos - Intenergy</h1>
        <p>Base de datos: <strong><?= $c->getBd() ?></strong></p>
        <hr>

        <?php if (count($resultados) > 0): ?>
            <h3>Detalle:</h3>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr><th>Tabla/SQL</th><th>Resultado</th></tr>
                </thead>
                <tbody>
                <?php foreach ($resultados as $r): ?>
                    <tr>
                        <td><?= $r['tabla'] ?? $r['sql'] ?? 'N/A' ?></td>
                        <td class="<?= isset($r['error']) ? 'error' : ($r['status'] === 'CREADA' ? 'creada' : 'existe') ?>">
                            <?= $r['error'] ?? $r['status'] ?? 'OK' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-success">
                <h4>Todas las tablas ya existen. No se necesitaron cambios.</h4>
            </div>
        <?php endif; ?>

        <div class="alert alert-info">
            <strong>Nota:</strong> Este script es seguro ejecutarlo multiples veces.
            Solo crea tablas que no existen e inserta datos que falten.
        </div>
    </div>
</body>
</html>
