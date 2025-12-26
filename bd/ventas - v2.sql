
use intenerg_sgi;

create table usuarios(
				id_usuario int auto_increment,
				nombre varchar(50),
				apellido varchar(50),
				email varchar(50),
				password text(50),
				fechaCaptura date,
				estado bit not null default 1,
				primary key(id_usuario)
					);

create table categorias (
				id_categoria int auto_increment,
				id_usuario int not null,
				nombreCategoria varchar(150),
				fechaCaptura date,
				subcategoria bit not null default 0,
				estado bit not null default 1,
				primary key(id_categoria)
						);

create table imagenes(
				id_imagen int auto_increment,
				id_categoria int not null,
				nombre varchar(500),
				ruta varchar(500),
				fechaSubida date,
				estado bit not null default 1,
				primary key(id_imagen)
					);
create table articulos(
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

						);

create table clientes(
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
					);
-- Recuerda agregar el id de usuario por favor 
create table ventas(
				id_venta int not null,
				id_cliente int,
				id_producto int,
				id_usuario int,
				precio float,
				estado bit not null default 1,
				fechaCompra date
					);
					
CREATE TABLE bodegas (
    id_bodega INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombreBodega VARCHAR(150),
    identificador VARCHAR(10) UNIQUE,
    es_bodega_secundaria BIT DEFAULT 0,
    id_bodega_principal INT NULL,
    fechaCaptura DATE,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega)
);
		
INSERT INTO bodegas (id_usuario,nombreBodega,fechaCaptura)	
VALUES ('1','Bodega Prncipal','2025-05-21');

create table proveedor(
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
		);
	 	
create table tipoidentificacion (
				id_tipo_identificacion int auto_increment,
				id_usuario int not null,
				tipo varchar(200),
				codigo varchar(20),
				fechaCaptura date, 
				estado bit not null default 1,
				primary key(id_tipo_identificacion)
	);
	
INSERT INTO tipoidentificacion (id_usuario,tipo,codigo,fechaCaptura)	
VALUES (1,'RUC','04','2025-05-21');	

INSERT INTO tipoidentificacion (id_usuario,tipo,codigo,fechaCaptura)	
VALUES (1,'CÉDULA','05','2025-05-21');	

INSERT INTO tipoidentificacion (id_usuario,tipo,codigo,fechaCaptura)	
VALUES (1,'PASAPORTE','06','2025-05-21');	

INSERT INTO tipoidentificacion (id_usuario,tipo,codigo,fechaCaptura)	
VALUES (1,'VENTA A CONSUMIDOR FINAL','07','2025-05-21');		

INSERT INTO tipoidentificacion (id_usuario,tipo,codigo,fechaCaptura)	
VALUES (1,'IDENTIFICACIÓN DELEXTERIOR','08','2025-05-21');		
		
create table tipocontribuyente (
				id_tipo_contribuyente int auto_increment,
				id_usuario int not null,
				tipo varchar(200),
				fechaCaptura date, 
				estado bit not null default 1,
				primary key(id_tipo_contribuyente)
	);
	
INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'NINGUNO','2025-05-21');		

INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'CONTRIBUYENTE ESPECIAL','2025-05-21');	

INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'PERSONA NATURAL','2025-05-21');	

INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'RIMPE EMPRENDEDOR','2025-05-21');	

INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'RIMPE NEGOCIO POPULAR','2025-05-21');

INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'SECTOR PUBLICO','2025-05-21');	

INSERT INTO tipocontribuyente (id_usuario,tipo,fechaCaptura)	
VALUES (1,'SOCIEDAD','2025-05-21');	
				
create table genero (
				id_genero int auto_increment,
				id_usuario int not null,
				genero varchar(200),
				fechaCaptura date, 
				estado bit not null default 1,
				primary key(id_genero)
	);
	
INSERT INTO genero (id_usuario,genero,fechaCaptura)	
VALUES (1,'MASCULINO','2025-05-21');		

INSERT INTO genero (id_usuario,genero,fechaCaptura)	
VALUES (1,'FEMENINO','2025-05-21');	

create table empleados(
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
					);

create table cargo_empleado (
				id_cargo int auto_increment,
				id_usuario int not null,
				cargo varchar(200),
				fechaCaptura date, 
				estado bit not null default 1,
				primary key(id_cargo)
	);
	
INSERT INTO cargo_empleado (id_usuario,cargo,fechaCaptura)	
VALUES (1,'OFICINA DE BODEGA','2025-05-21');	 

create table tipo_movimiento (
				id_tipo_movimiento int auto_increment,
				id_usuario int not null,
				tipo varchar(200),
				fechaCaptura date, 
				estado bit not null default 1,
				entrada bit  not null,
				salida bit  not null,
				primary key(id_tipo_movimiento)
	);
	
INSERT INTO tipo_movimiento (id_usuario,tipo,fechaCaptura,entrada,salida)	
VALUES (1,'ENTRADA','2025-05-21',1,0);	

INSERT INTO tipo_movimiento (id_usuario,tipo,fechaCaptura,entrada,salida)	
VALUES (1,'SALIDA','2025-05-21',0,1);	

create table sub_tipo_movimiento (
				id_sub_tipo_movimiento int auto_increment,
				id_usuario int not null,
				sub_tipo varchar(200),
				fechaCaptura date, 
				id_tipo_movimiento int not null,
				estado bit not null default 1,
				primary key(id_sub_tipo_movimiento)
	);
	
INSERT INTO sub_tipo_movimiento (id_usuario,sub_tipo,id_tipo_movimiento,fechaCaptura)	
VALUES (1,'COMPRAS',1,'2025-05-21');	

INSERT INTO sub_tipo_movimiento (id_usuario,sub_tipo,id_tipo_movimiento,fechaCaptura)	
VALUES (1,'CARGOS',1,'2025-05-21');	 

INSERT INTO sub_tipo_movimiento (id_usuario,sub_tipo,id_tipo_movimiento,fechaCaptura)	
VALUES (1,'DESCARGO',2,'2025-05-21');	 

create table forma_pago (
				id_forma_pago int auto_increment,
				id_usuario int not null,
				forma_pago varchar(200),
				fechaCaptura date,  
				estado bit not null default 1,
				primary key(id_forma_pago)
	);
	
INSERT INTO forma_pago (id_usuario,forma_pago,fechaCaptura)	
VALUES (1,'CREDITO','2025-05-21');	

INSERT INTO forma_pago (id_usuario,forma_pago,fechaCaptura)	
VALUES (1,'CONTADO','2025-05-21');	

create table movimientos (
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
				iva_mto decimal(18,4)  not null default 0,
				desc_mto decimal(18,4)  not null default 0,
				subtotal_mto decimal(18,4)  not null default 0,
				total_mto decimal(18,4)  not null default 0,
				iva_porc decimal(18,4)  not null default 0,
				desc_porc decimal(18,4)  not null default 0, 
			    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
			    FOREIGN KEY (id_bodega) REFERENCES bodegas(id_bodega), 
			    FOREIGN KEY (id_tipo_movimiento) REFERENCES tipo_movimiento(id_tipo_movimiento),
                FOREIGN KEY (id_sub_tipo_movimiento) REFERENCES sub_tipo_movimiento(id_sub_tipo_movimiento)
	);

create table movimientos_detalle (
				id_movimiento_detalle int auto_increment PRIMARY KEY,
				id_movimiento int not null,
				id_articulo int not null,
				id_bodega_lugar int not null,
				precio decimal(18,4)  not null,
				cantidad decimal(18,4)  not null,
				lote varchar(200),
				bonificacion decimal(18,4)  ,
				subtotal decimal(18,4) not null default 0,
				decuento decimal(18,4) not null default 0,
				iva decimal(18,4) not null default 0,
				total decimal(18,4)  not null, 
				iva_porc decimal(18,4)  not null default 0,
				desc_porc decimal(18,4)  not null default 0,
				estado bit not null default 1, 
				FOREIGN KEY (id_movimiento) REFERENCES movimientos(id_movimiento),  
				FOREIGN KEY (id_articulo) REFERENCES articulos(id_producto),
                FOREIGN KEY (id_bodega_lugar) REFERENCES bodegas(id_bodega)
               
				
	);
	
create table movimientos_detalle_tmp (
				id_movimientos_detalle_tmp int auto_increment,
				id_movimiento int not null,
				id_articulo int not null,
				id_bodega_lugar int not null,
				precio decimal(18,4)  not null,
				cantidad decimal(18,4)  not null,
				lote varchar(200),
				bonificacion decimal(18,4) ,
				subtotal decimal(18,4)  not null,
				decuento decimal(18,4) not null default 0,
				iva decimal(18,4) not null default 0,
				total decimal(18,4)  not null, 
				iva_porc decimal(18,4)  not null default 0,
				desc_porc decimal(18,4)  not null default 0,
				primary key(id_movimientos_detalle_tmp)
				
	);
  create table stock_productos (
				id_stock_productos int AUTO_INCREMENT PRIMARY KEY,
				id_movimiento int not null,
				id_tipo_movimiento int not null,
				id_sub_tipo_movimiento int not null, 
				no_documento varchar(200),
				id_bodega_principal int not null,
				id_bodega_secundaria int not null, 
				tipo_movimiento varchar(200),
				sub_tipo_movimiento varchar(200),
                id_movimiento_detalle int not null, 
                id_producto int not null,
                producto varchar(200),
                lote varchar(200),
				precio decimal(18,4)  not null,
				cantidad decimal(18,4)  not null,
                subtotal decimal(18,4)  not null,
                decuento decimal(18,4) not null default 0,
                iva decimal(18,4) not null default 0,
                total decimal(18,4)  not null, 
                stock decimal(18,4)  not null,
				id_stock_credito int,
				estado bit not null default 1, 
				id_usuario int not null,
				fecha_captura date not null, 
				FOREIGN KEY (id_movimiento) REFERENCES movimientos(id_movimiento),
                FOREIGN KEY (id_tipo_movimiento) REFERENCES tipo_movimiento(id_tipo_movimiento),
                FOREIGN KEY (id_sub_tipo_movimiento) REFERENCES sub_tipo_movimiento(id_sub_tipo_movimiento),
                FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega),
                FOREIGN KEY (id_bodega_secundaria) REFERENCES bodegas(id_bodega),
                FOREIGN KEY (id_movimiento_detalle) REFERENCES movimientos_detalle(id_movimiento_detalle),
                FOREIGN KEY (id_producto) REFERENCES articulos(id_producto),
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
				
	);
create table iva (
				id_iva int auto_increment,
				monto float not null,
				CODIGO_SRI varchar(20),
				Codigo_Porc_Iva varchar(20), 
				estado bit not null default 1,
				primary key(id_iva)
	);
	
INSERT INTO iva (monto,CODIGO_SRI,Codigo_Porc_Iva)	
VALUES (15,'2','4');	

CREATE TABLE estado_ordenes (
    id_estado_orden INT AUTO_INCREMENT,
    estado VARCHAR(50),
    activo BIT NOT NULL DEFAULT 1,
    PRIMARY KEY(id_estado_orden)
);

INSERT INTO estado_ordenes (estado) VALUES ('GRABADO');
INSERT INTO estado_ordenes (estado) VALUES ('APROBADO');
INSERT INTO estado_ordenes (estado) VALUES ('RECHAZADO');
INSERT INTO estado_ordenes (estado) VALUES ('PENDIENTE');
INSERT INTO estado_ordenes (estado) VALUES ('ANULADO');
							

CREATE TABLE ordenes_trabajo (
    id_orden INT AUTO_INCREMENT PRIMARY KEY,
    identificador VARCHAR(10), -- Ejemplo: T001, T002 (puedes generarlo en el backend)
    costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fecha_inicial DATE NOT NULL,
    fecha_final DATE NOT NULL,
    id_bodega_principal INT NOT NULL,
    id_bodega_secundaria INT NOT NULL,
    observacion TEXT,
    id_estado_orden INT NOT NULL DEFAULT 1,
    fecha_graba DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_bodega_secundaria) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_estado_orden) REFERENCES estado_ordenes(id_estado_orden)
);

CREATE TABLE estado_solicitud_materiales(
    id_estado_solicitud INT AUTO_INCREMENT,
    estado VARCHAR(50),
    activo BIT NOT NULL DEFAULT 1,
    PRIMARY KEY(id_estado_solicitud)
);

INSERT INTO estado_solicitud_materiales (estado) VALUES ('GRABADO');
INSERT INTO estado_solicitud_materiales (estado) VALUES ('RECIBIDO');
INSERT INTO estado_solicitud_materiales (estado) VALUES ('ENTREGADO');
INSERT INTO estado_solicitud_materiales (estado) VALUES ('RECHAZADO');
INSERT INTO estado_solicitud_materiales (estado) VALUES ('ANULADO');

CREATE TABLE solicitud_materiales (
    id_solicitud_material INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,                           -- Orden de trabajo aprobada
    fecha_solicitud DATE NOT NULL,                   -- Fecha de la solicitud
    id_bodega_principal INT NOT NULL,                -- Bodega principal (Lugar)
    id_bodega_secundaria INT NOT NULL,               -- Bodega secundaria (Ubicación)
    id_responsable_obra INT NOT NULL,                -- Responsable obra (id_empleados)
    id_entrega_articulos INT NOT NULL,               -- Responsable que entrega (id_empleados)
    id_recibe_articulos INT NOT NULL,                -- Responsable que recibe (id_empleados)
    observacion TEXT,
    id_estado_solicitud INT NOT NULL DEFAULT 1,      -- Estado de la solicitud
    FOREIGN KEY (id_orden) REFERENCES ordenes_trabajo(id_orden),
    FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_bodega_secundaria) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_responsable_obra) REFERENCES empleados(id_empleados),
    FOREIGN KEY (id_entrega_articulos) REFERENCES empleados(id_empleados),
    FOREIGN KEY (id_recibe_articulos) REFERENCES empleados(id_empleados),
    FOREIGN KEY (id_estado_solicitud) REFERENCES estado_solicitud_materiales(id_estado_solicitud)
);

CREATE TABLE estado_solicitud_materiales_articulos(
    id_estado_solicitud_articulos INT AUTO_INCREMENT,
    estado VARCHAR(50),
    activo BIT NOT NULL DEFAULT 1,
    PRIMARY KEY(id_estado_solicitud_articulos)
);

INSERT INTO estado_solicitud_materiales_articulos (estado) VALUES ('PENDIENTE');
INSERT INTO estado_solicitud_materiales_articulos (estado) VALUES ('COMPLETADO');
INSERT INTO estado_solicitud_materiales_articulos (estado) VALUES ('INCOMPLETO');
INSERT INTO estado_solicitud_materiales_articulos (estado) VALUES ('MAL ESTADO');

CREATE TABLE solicitud_materiales_detalle (
    id_solicitud_material_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud_material INT NOT NULL,              -- Relaciona con la solicitud principal
    id_producto INT NOT NULL,                        -- Artículo solicitado (de la tabla articulos)
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,       -- Cantidad solicitada
	id_detalle_articulo INT NOT NULL DEFAULT 1,      -- Estado del articulo en el detalle 
    FOREIGN KEY (id_solicitud_material) REFERENCES solicitud_materiales(id_solicitud_material),
    FOREIGN KEY (id_producto) REFERENCES articulos(id_producto),
	FOREIGN KEY (id_detalle_articulo) REFERENCES estado_solicitud_materiales_articulos(id_estado_solicitud_articulos)
);
CREATE TABLE informe_trabajo (
    id_informe INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL, -- quien lo crea
    id_orden INT NOT NULL,   -- relación con ordenes_trabajo
    fecha DATE NOT NULL,
    id_bodega_principal INT NOT NULL,
    id_bodega_secundaria INT NOT NULL,
    lugar VARCHAR(150)  NULL, -- ejemplo: TROPACK
    observacion TEXT,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1, -- 1=activo, 0=inactivo
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_orden) REFERENCES ordenes_trabajo(id_orden),
    FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_bodega_secundaria) REFERENCES bodegas(id_bodega)
);

-- Relación empleados internos
CREATE TABLE informe_trabajo_empleado (
    id_informe_empleado INT AUTO_INCREMENT PRIMARY KEY,
    id_informe INT NOT NULL,
    id_empleado INT NOT NULL,
    FOREIGN KEY (id_informe) REFERENCES informe_trabajo(id_informe),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados)
);

-- Relación empleados externos
CREATE TABLE informe_trabajo_empleado_externo (
    id_informe_empleado_externo INT AUTO_INCREMENT PRIMARY KEY,
    id_informe INT NOT NULL,
    id_empleado_externo INT NOT NULL,
    FOREIGN KEY (id_informe) REFERENCES informe_trabajo(id_informe),
    FOREIGN KEY (id_empleado_externo) REFERENCES empleados(id_empleados)
);

-- Artículos usados en el informe
CREATE TABLE informe_trabajo_articulo (
    id_informe_articulo INT AUTO_INCREMENT PRIMARY KEY,
    id_informe INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    lote VARCHAR(100),
    FOREIGN KEY (id_informe) REFERENCES informe_trabajo(id_informe),
    FOREIGN KEY (id_producto) REFERENCES articulos(id_producto)
);

-- Imágenes asociadas al informe (opcional)
CREATE TABLE informe_trabajo_imagen (
    id_informe_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_informe INT NOT NULL,
    nombre VARCHAR(500),
    ruta VARCHAR(500),
    fecha_subida DATE NOT NULL,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_informe) REFERENCES informe_trabajo(id_informe)
);
 -- Tabla principal: horas de trabajo por obra
CREATE TABLE horas_trabajo (
    id_horas_trabajo INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    id_bodega_principal INT NOT NULL,
    id_bodega_secundaria INT NOT NULL,
    id_responsable_obra INT NOT NULL,  -- empleado responsable 
    observacion TEXT,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_bodega_secundaria) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_responsable_obra) REFERENCES empleados(id_empleados)
);

-- Tabla de detalle de empleados asignados a la obra y horas
CREATE TABLE horas_trabajo_empleado (
    id_horas_trabajo_empleado INT AUTO_INCREMENT PRIMARY KEY,
    id_horas_trabajo INT NOT NULL,
    id_empleado INT NOT NULL,
	numero_horas INT NOT NULL DEFAULT 0,
    costo_por_hora DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00, -- SE CALCULA: numero_horas * costo_por_hora
    activo BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_horas_trabajo) REFERENCES horas_trabajo(id_horas_trabajo),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados)
);
CREATE TABLE backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_crea DATETIME NOT NULL,
    ruta VARCHAR(255) NOT NULL
);

create table menu(
				id_menu int auto_increment,
				menu_padre TEXT,
			    menu_hijo TEXT,
				icono_padre TEXT,  
				icono_hijo TEXT, 
		        descripcion TEXT,
				opcion TEXT,
		        fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                estado BIT NOT NULL DEFAULT 1,
				primary key(id_menu)
);

create table permisos_usuario_menu(
	            id_permisos_usuario_menu INT AUTO_INCREMENT PRIMARY KEY, 
				id_usuario INT,
				id_menu INT,
				fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
			    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
				FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);

/*REGISTROS GENERAL*/
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','ARTICULOS','ARTICULOS','articulos.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','CATEGORIAS','CATEGORIAS','categorias.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','BODEGAS','BODEGAS','bodegas.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','PROVEEDORES','PROVEEDORES','proveedores.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','EMPLEADOS','EMPLEADOS','empleados.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','CARGOS EMPLEADOS','CARGOS EMPLEADOS','cargos.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','PROYECTO','PROYECTO','proyecto.php','fa fa-edit');
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REGISTROS GENERAL','OBRA','OBRA','obra.php','fa fa-edit');
  
/*INVENTARIO*/
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('INVENTARIO','MOVIMIENTOS','MOVIMIENTOS','movimientos.php','glyphicon glyphicon-tasks'); 

/*OPERACIONES*/
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('OPERACIONES','ORDEN DE TRABAJO','ORDEN DE TRABAJO','ordenes_trabajo.php','fa fa-gear'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('OPERACIONES','SOLICITUD DE MATERIAL','SOLICITUD DE MATERIAL','solicitudmaterial.php','fa fa-gear'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('OPERACIONES','INFORMES DIARIOS','INFORMES DIARIOS','informe_trabajo.php','fa fa-gear'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('OPERACIONES','HORAS DE TRABAJOS','HORAS DE TRABAJOS','horas_trabjo.php','fa fa-gear'); 

/*REPORTES*/
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','STOCK DE ARTICULOS','STOCK DE ARTICULOS','reportestock.php','fa fa-chart-bar'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','ORDENES DE TRABAJO POR CLIENTES','ORDENES DE TRABAJO POR CLIENTES','reporte_ordenes_trabajo.php','fa fa-chart-bar'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','SOLICITUD DE MATERIALES','SOLICITUD DE MATERIALES','reporte_solicitud_materiales.php','fa fa-chart-bar'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','INFORMES DIARIO','INFORMES DIARIO','informes_diarios_avanzado.php','fa fa-chart-bar'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','HORAS DE TRABAJO','HORAS DE TRABAJO','informe_horas_trabajo.php','fa fa-chart-bar'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','ENTRADA / SALIDA (KARDEX)','ENTRADA / SALIDA (KARDEX)','reportekardex.php','fa fa-chart-bar'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre) 
VALUES ('REPORTES','INFORME DISPONIBILIDAD POR BODEGAS','INFORME DISPONIBILIDAD POR BODEGAS','reporte_disponibilidad_bodega.php','fa fa-chart-bar'); 

/*ADMIN*/
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre,icono_hijo) 
VALUES ('ADMIN','USUARIOS','USUARIOS','usuarios.php','fa fa-wrench','glyphicon glyphicon-user'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre,icono_hijo) 
VALUES ('ADMIN','BACKUP','BACKUP','backup.php','fa fa-wrench','glyphicon glyphicon-download');  
INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre,icono_hijo) 
VALUES ('ADMIN','IVA','IVA','iva.php','fa fa-edit','fa fa-exchange');

/*MENU DE BLOQUES DE CAMPOS O PERMISOS: PERMISOS ESPECIALES*/ 
INSERT INTO menu (menu_padre,menu_hijo,descripcion) 
VALUES ('PERMISOS ESPECIALES','VER PRECIO ARTICULOS','VER PRECIO ARTICULOS'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion) 
VALUES ('PERMISOS ESPECIALES','AGREGAR ARTICULOS','AGREGAR ARTICULOS'); 
INSERT INTO menu (menu_padre,menu_hijo,descripcion) 
VALUES ('PERMISOS ESPECIALES','EDITAR ARTICULOS','EDITAR ARTICULOS');
INSERT INTO menu (menu_padre,menu_hijo,descripcion) 
VALUES ('PERMISOS ESPECIALES','ELIMINAR ARTICULOS','ELIMINAR ARTICULOS');

CREATE TABLE stock_productos_bodega (
    id_stock_bodega INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_bodega_principal INT NOT NULL,
    id_bodega_secundaria INT NOT NULL,
    cantidad DECIMAL(18,4) NOT NULL DEFAULT 0,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1,
    UNIQUE KEY (id_producto, id_bodega_principal, id_bodega_secundaria),
    FOREIGN KEY (id_producto) REFERENCES articulos(id_producto),
    FOREIGN KEY (id_bodega_principal) REFERENCES bodegas(id_bodega),
    FOREIGN KEY (id_bodega_secundaria) REFERENCES bodegas(id_bodega)
);
 
CREATE TABLE permiso_bodega (
    id_permiso_bodega INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_bodega INT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id_bodega)
);

CREATE TABLE proyecto ( 
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(250) NOT NULL, 
  estado bit not null default 1,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE obra ( 
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(250) NOT NULL, 
  estado bit not null default 1,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DELIMITER //
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
END;

DELIMITER $$
CREATE PROCEDURE actualizar_cantidad_articulos_por_movimiento(IN movimiento_id INT)
BEGIN
  -- Actualiza la cantidad de cada artículo involucrado en el movimiento recibido
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
END$$
DELIMITER ;
 
DELIMITER $$

CREATE TRIGGER bodegas_set_identificador
BEFORE INSERT ON bodegas
FOR EACH ROW
BEGIN
    DECLARE nombre VARCHAR(150);
    DECLARE identificador VARCHAR(20) DEFAULT '';
    DECLARE ult_palabra VARCHAR(50);
    DECLARE partes INT DEFAULT 0;
    DECLARE i INT DEFAULT 1;
    DECLARE palabra VARCHAR(50);

    SET nombre = TRIM(NEW.nombreBodega);
    SET nombre = REPLACE(nombre, '_', ' ');
    SET partes = LENGTH(nombre) - LENGTH(REPLACE(nombre, ' ', '')) + 1;
    SET ult_palabra = SUBSTRING_INDEX(nombre, ' ', -1);

    IF partes = 1 THEN
        -- Una sola palabra: dos primeras letras
        SET identificador = UPPER(LEFT(nombre, 2));
        SET NEW.identificador = identificador;
    ELSEIF partes = 2 AND ult_palabra REGEXP '^[0-9]+$' THEN
        -- Una palabra y número separado por espacio o guion bajo: dos primeras letras + guion + número
        SET identificador = UPPER(LEFT(SUBSTRING_INDEX(nombre, ' ', 1), 2));
        SET NEW.identificador = CONCAT(identificador, '', ult_palabra);
    ELSEIF partes = 2 THEN
        -- Dos palabras: primera letra de cada palabra
        SET identificador = CONCAT(UPPER(LEFT(SUBSTRING_INDEX(nombre, ' ', 1), 1)), UPPER(LEFT(SUBSTRING_INDEX(nombre, ' ', -1), 1)));
        SET NEW.identificador = identificador;
    ELSEIF partes > 2 AND ult_palabra REGEXP '^[0-9]+$' THEN
        -- Tres o más palabras y termina en número: primera letra de cada palabra menos el número + guion + número
        WHILE i < partes DO
            SET palabra = SUBSTRING_INDEX(SUBSTRING_INDEX(nombre, ' ', i), ' ', -1);
            SET identificador = CONCAT(identificador, UPPER(LEFT(palabra, 1)));
            SET i = i + 1;
        END WHILE;
        SET NEW.identificador = CONCAT(identificador, '', ult_palabra);
    ELSE
        -- Tres o más palabras, sin número: primera letra de cada palabra
        WHILE i <= partes DO
            SET palabra = SUBSTRING_INDEX(SUBSTRING_INDEX(nombre, ' ', i), ' ', -1);
            SET identificador = CONCAT(identificador, UPPER(LEFT(palabra, 1)));
            SET i = i + 1;
        END WHILE;
        SET NEW.identificador = identificador;
    END IF;
END $$
DELIMITER ; 

DELIMITER $$

CREATE TRIGGER bodegas_update_identificador
BEFORE UPDATE ON bodegas
FOR EACH ROW
BEGIN
    DECLARE nombre VARCHAR(150);
    DECLARE identificador VARCHAR(20) DEFAULT '';
    DECLARE ult_palabra VARCHAR(50);
    DECLARE partes INT DEFAULT 0;
    DECLARE i INT DEFAULT 1;
    DECLARE palabra VARCHAR(50);

    SET nombre = TRIM(NEW.nombreBodega);
    SET nombre = REPLACE(nombre, '_', ' ');
    SET partes = LENGTH(nombre) - LENGTH(REPLACE(nombre, ' ', '')) + 1;
    SET ult_palabra = SUBSTRING_INDEX(nombre, ' ', -1);

    IF partes = 1 THEN
        -- Una sola palabra: dos primeras letras
        SET identificador = UPPER(LEFT(nombre, 2));
        SET NEW.identificador = identificador;
    ELSEIF partes = 2 AND ult_palabra REGEXP '^[0-9]+$' THEN
        -- Una palabra y número separado por espacio o guion bajo: dos primeras letras + guion + número
        SET identificador = UPPER(LEFT(SUBSTRING_INDEX(nombre, ' ', 1), 2));
        SET NEW.identificador = CONCAT(identificador, '', ult_palabra);
    ELSEIF partes = 2 THEN
        -- Dos palabras: primera letra de cada palabra
        SET identificador = CONCAT(
            UPPER(LEFT(SUBSTRING_INDEX(nombre, ' ', 1), 1)),
            UPPER(LEFT(SUBSTRING_INDEX(nombre, ' ', -1), 1))
        );
        SET NEW.identificador = identificador;
    ELSEIF partes > 2 AND ult_palabra REGEXP '^[0-9]+$' THEN
        -- Tres o más palabras y termina en número: primera letra de cada palabra menos el número + guion + número
        WHILE i < partes DO
            SET palabra = SUBSTRING_INDEX(SUBSTRING_INDEX(nombre, ' ', i), ' ', -1);
            SET identificador = CONCAT(identificador, UPPER(LEFT(palabra, 1)));
            SET i = i + 1;
        END WHILE;
        SET NEW.identificador = CONCAT(identificador, '', ult_palabra);
    ELSE
        -- Tres o más palabras, sin número: primera letra de cada palabra
        WHILE i <= partes DO
            SET palabra = SUBSTRING_INDEX(SUBSTRING_INDEX(nombre, ' ', i), ' ', -1);
            SET identificador = CONCAT(identificador, UPPER(LEFT(palabra, 1)));
            SET i = i + 1;
        END WHILE;
        SET NEW.identificador = identificador;
    END IF;
END $$
DELIMITER ;