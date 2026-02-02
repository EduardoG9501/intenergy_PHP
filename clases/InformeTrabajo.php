<?php
class InformeTrabajo {

    // ----------- INFORMES PRINCIPALES -----------
    // Ahora acepta $id_bodega como parámetro para filtrar por bodega seleccionada
    public function listar($id_bodega = null, $id_ejecucion_obra = null) {
        $c = new conectar();
        $conexion = $c->conexion();
        $datos = [];

        $filtro = "WHERE it.estado = 1";
        if ($id_bodega) {
            $id_bodega = intval($id_bodega);
            $filtro .= " AND it.id_bodega_principal = $id_bodega";
        }
        if ($id_ejecucion_obra !== null && $id_ejecucion_obra !== '' && intval($id_ejecucion_obra) > 0) {
            $id_ejecucion_obra = intval($id_ejecucion_obra);
            $filtro .= " AND it.id_ejecucion_obra = $id_ejecucion_obra";
        }

        $sql = "SELECT it.*, eo.id_orden, eo.id_ejecucion_obra, eo.fecha_solicitud, eo.observacion AS ejecucion_observacion, ot.identificador, bp.nombreBodega AS bodega_principal, bs.nombreBodega AS bodega_secundaria
            FROM informe_trabajo it
            JOIN ejecucion_obra eo ON it.id_ejecucion_obra = eo.id_ejecucion_obra
            LEFT JOIN ordenes_trabajo ot ON eo.id_orden = ot.id_orden
            JOIN bodegas bp ON it.id_bodega_principal = bp.id_bodega
            JOIN bodegas bs ON it.id_bodega_secundaria = bs.id_bodega
            $filtro
            ORDER BY it.id_informe DESC";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            // Concatenar identificador de orden + id_ejecucion_obra para el grid
            $fila['identificador_ejecucion'] = ($fila['identificador'] ? $fila['identificador'] : '') . ' - ' . $fila['id_ejecucion_obra'];
            $datos[] = $fila;
        }
        return $datos;
    }

    public function obtener($id) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "SELECT * FROM informe_trabajo WHERE id_informe = $id";
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function guardar($data, $id_usuario) {
        $c= new conectar();
        $conexion=$c->conexion();
        $id = !empty($data['id_informe']) ? intval($data['id_informe']) : 0;
        $fecha = $data['fecha'];
        $id_bodega_principal = $data['id_bodega_principal'];
        $id_bodega_secundaria = $data['id_bodega_secundaria'];
        $observacion = mysqli_real_escape_string($conexion, $data['observacion']);
        $id_ejecucion_obra = $data['id_ejecucion_obra'];

        if ($id > 0) {
            $sql = "UPDATE informe_trabajo SET 
                        fecha='$fecha', id_bodega_principal='$id_bodega_principal', 
                        id_bodega_secundaria='$id_bodega_secundaria', 
                        observacion='$observacion', id_ejecucion_obra='$id_ejecucion_obra'
                    WHERE id_informe='$id'";
            mysqli_query($conexion, $sql);
            return ['success' => true, 'msg' => 'Informe actualizado'];
        } else {
            $sql = "INSERT INTO informe_trabajo (id_usuario, fecha, id_bodega_principal, id_bodega_secundaria, observacion, id_ejecucion_obra, estado)
                    VALUES ('$id_usuario', '$fecha', '$id_bodega_principal', '$id_bodega_secundaria', '$observacion', '$id_ejecucion_obra', 1)";
            mysqli_query($conexion, $sql);
            $nuevoId = mysqli_insert_id($conexion);
            return ['success' => true, 'msg' => 'Informe creado', 'id_informe' => $nuevoId];
        }
    }

    public function eliminar($id) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "UPDATE informe_trabajo SET estado=0 WHERE id_informe=$id";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    // ---- Métodos para combos dinámicos ----
    public function listarBodegas() {
    $c = new conectar();
    $conexion = $c->conexion();
    $bodegas = [];
    // Traer también es_bodega_secundaria e id_bodega_principal
    $sql = "SELECT id_bodega, nombreBodega, es_bodega_secundaria, id_bodega_principal FROM bodegas WHERE estado=1";
    $rs = mysqli_query($conexion, $sql);
    while ($row = mysqli_fetch_assoc($rs)) {
        $row['es_bodega_secundaria'] = intval($row['es_bodega_secundaria']);
        $row['id_bodega_principal'] = $row['id_bodega_principal'] !== null ? intval($row['id_bodega_principal']) : null;
        $bodegas[] = $row;
    }
    return $bodegas;
}

    public function listarEmpleados() {
        $c= new conectar();
        $conexion=$c->conexion();
        $empleados = [];
        $sql = "SELECT id_empleados, nombres_apellidos FROM empleados WHERE estado=1";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $empleados[] = $row;
        return $empleados;
    }

    public function listarEjecuciones() {
        $c= new conectar();
        $conexion=$c->conexion();
        $ejecuciones = [];
        $sql = "SELECT eo.id_ejecucion_obra, eo.id_orden, eo.fecha_solicitud, eo.observacion, ot.identificador
                FROM ejecucion_obra eo
                LEFT JOIN ordenes_trabajo ot ON eo.id_orden = ot.id_orden
                WHERE 1";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) {
            // Concatenar identificador de orden + id_ejecucion_obra
            $row['identificador_ejecucion'] = ($row['identificador'] ? $row['identificador'] : '') . ' - ' . $row['id_ejecucion_obra'];
            $ejecuciones[] = $row;
        }
        return $ejecuciones;
    }

    // ----------- EMPLEADOS INTERNOS -----------
    public function listarEmpleadosInternos($id_informe) {
        $c= new conectar();
        $conexion=$c->conexion();
        $datos = [];
        $sql = "SELECT ite.*, e.nombres_apellidos FROM informe_trabajo_empleado ite
                JOIN empleados e ON ite.id_empleado = e.id_empleados
                WHERE ite.id_informe = $id_informe";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function agregarEmpleadoInterno($id_informe, $id_empleado) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "INSERT INTO informe_trabajo_empleado (id_informe, id_empleado) VALUES ('$id_informe', '$id_empleado')";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function eliminarEmpleadoInterno($id_informe_empleado) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "DELETE FROM informe_trabajo_empleado WHERE id_informe_empleado='$id_informe_empleado'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    // ----------- EMPLEADOS EXTERNOS -----------
    public function listarEmpleadosExternos($id_informe) {
        $c= new conectar();
        $conexion=$c->conexion();
        $datos = [];
        $sql = "SELECT itee.*, e.nombres_apellidos FROM informe_trabajo_empleado_externo itee
                JOIN empleados e ON itee.id_empleado_externo = e.id_empleados
                WHERE itee.id_informe = $id_informe";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function agregarEmpleadoExterno($id_informe, $id_empleado_externo) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "INSERT INTO informe_trabajo_empleado_externo (id_informe, id_empleado_externo) VALUES ('$id_informe', '$id_empleado_externo')";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function eliminarEmpleadoExterno($id_informe_empleado_externo) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "DELETE FROM informe_trabajo_empleado_externo WHERE id_informe_empleado_externo='$id_informe_empleado_externo'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    // ----------- ARTÍCULOS DEL INFORME -----------
    public function listarArticulos($id_informe) {
        $c= new conectar();
        $conexion=$c->conexion();
        $datos = [];
        $sql = "SELECT ita.*, a.nombre FROM informe_trabajo_articulo ita
                JOIN articulos a ON ita.id_producto = a.id_producto
                WHERE ita.id_informe = $id_informe";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function agregarArticulo($id_informe, $id_producto, $cantidad, $lote) {
        $c= new conectar();
        $conexion=$c->conexion();
        $lote = mysqli_real_escape_string($conexion, $lote);
        $sql = "INSERT INTO informe_trabajo_articulo (id_informe, id_producto, cantidad, lote) VALUES ('$id_informe', '$id_producto', '$cantidad', '$lote')";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function eliminarArticulo($id_informe_articulo) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "DELETE FROM informe_trabajo_articulo WHERE id_informe_articulo='$id_informe_articulo'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    // ----------- IMÁGENES DEL INFORME -----------
    public function listarImagenes($id_informe) {
        $c= new conectar();
        $conexion=$c->conexion();
        $datos = [];
        $sql = "SELECT * FROM informe_trabajo_imagen WHERE id_informe = '$id_informe' AND estado = 1";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }
 
    public function listarOrdenesTrabajo() {
        $c= new conectar();
        $conexion=$c->conexion();
        $ejecuciones = [];
        $sql = "SELECT id_ejecucion_obra, id_orden, fecha_solicitud, observacion FROM ejecucion_obra WHERE 1 order by id_ejecucion_obra desc";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $ejecuciones[] = $row;
        return $ejecuciones;
    }
    public function editarEmpleadoInterno($id_informe_empleado, $id_empleado) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "UPDATE informe_trabajo_empleado SET id_empleado='$id_empleado' WHERE id_informe_empleado='$id_informe_empleado'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function editarEmpleadoExterno($id_informe_empleado_externo, $id_empleado_externo) {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "UPDATE informe_trabajo_empleado_externo SET id_empleado_externo='$id_empleado_externo' WHERE id_informe_empleado_externo='$id_informe_empleado_externo'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }
    public function listarArticulosCombo() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_producto, nombre FROM articulos WHERE estado=1";
        $result = mysqli_query($conexion, $sql);
        $articulos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $articulos[] = $row;
        }
        return $articulos;
    }
    public function editarArticulo($id_informe_articulo, $id_informe, $id_producto, $cantidad, $lote) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE informe_trabajo_articulo 
                SET id_informe='$id_informe', id_producto='$id_producto', cantidad='$cantidad', lote='$lote' 
                WHERE id_informe_articulo='$id_informe_articulo'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }
    public function agregarImagen($id_informe, $nombre, $ruta) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "INSERT INTO informe_trabajo_imagen (id_informe, nombre, ruta, fecha_subida, estado) 
                VALUES (?, ?, ?, NOW(), 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iss", $id_informe, $nombre, $ruta);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function obtenerRutaImagen($id_informe_imagen) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT ruta FROM informe_trabajo_imagen WHERE id_informe_imagen=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_informe_imagen);
        $stmt->execute();
        $stmt->bind_result($ruta);
        $stmt->fetch();
        $stmt->close();
        return $ruta;
    }

    /**
     * Actualiza la imagen del informe (nombre y ruta).
     * Si hay una imagen anterior, primero debes eliminarla en el proceso antes de llamar a este método.
     */
    public function editarImagen($id_informe_imagen, $id_informe, $nombre, $ruta) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE informe_trabajo_imagen SET id_informe=?, nombre=?, ruta=?, fecha_subida=NOW() WHERE id_informe_imagen=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("issi", $id_informe, $nombre, $ruta, $id_informe_imagen);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Elimina el registro de la imagen del informe (la imagen física debes eliminarla en el proceso).
     */
    public function eliminarImagen($id_informe_imagen) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "DELETE FROM informe_trabajo_imagen WHERE id_informe_imagen=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_informe_imagen);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
     public function actualizarDescripcion($id_informe, $descripcion) {
        $c = new conectar();
        $conexion = $c->conexion();
        $descripcion = mysqli_real_escape_string($conexion, $descripcion);
        $id_informe = intval($id_informe);
        $sql = "UPDATE informe_trabajo SET descripcion='$descripcion' WHERE id_informe=$id_informe";
        $res = mysqli_query($conexion, $sql);
        return ['success' => $res];
    }
    // Puedes seguir agregando más métodos según lo que necesite tu módulo.
}
?>