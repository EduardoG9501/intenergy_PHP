-- Módulo: LIQUIDACION DE TRABAJO

CREATE TABLE IF NOT EXISTS liquidacion_trabajo (
    id_liquidacion_trabajo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    id_proyecto INT NULL,
    id_obra INT NULL,
    id_bodega INT NULL,
    fecha_inicial DATE NOT NULL,
    fecha_final DATE NOT NULL,
    mano_obra TEXT,
    importante TEXT,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_proyecto) REFERENCES proyecto(id),
    FOREIGN KEY (id_obra) REFERENCES obra(id),
    FOREIGN KEY (id_bodega) REFERENCES bodegas(id_bodega)
);

CREATE TABLE IF NOT EXISTS liquidacion_trabajo_imagen (
    id_liquidacion_trabajo_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_liquidacion_trabajo INT NOT NULL,
    nombre VARCHAR(500),
    ruta VARCHAR(500),
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado BIT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_liquidacion_trabajo) REFERENCES liquidacion_trabajo(id_liquidacion_trabajo)
);

INSERT INTO menu (menu_padre,menu_hijo,descripcion,opcion,icono_padre)
SELECT 'REPORTES','LIQUIDACION DE TRABAJO','LIQUIDACION DE TRABAJO','liquidacion_trabajo.php','fa fa-chart-bar'
WHERE NOT EXISTS (
    SELECT 1 FROM menu WHERE menu_hijo = 'LIQUIDACION DE TRABAJO'
);
