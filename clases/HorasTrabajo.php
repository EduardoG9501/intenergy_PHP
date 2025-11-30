<?php
class HorasTrabajo {

    // Ahora acepta $id_bodega como parámetro para filtrar por bodega seleccionada
    public function listar($id_bodega = null) {
        $c = new conectar();
        $conexion = $c->conexion();
        $datos = [];

        // Agrega filtro si hay una bodega seleccionada
        $filtro = "WHERE ht.estado = 1";
        if ($id_bodega) {
            $id_bodega = intval($id_bodega);
            $filtro .= " AND ht.id_bodega_principal = $id_bodega";
        }

        $sql = "SELECT ht.*, bp.nombreBodega AS bodega_principal, bs.nombreBodega AS bodega_secundaria, e.nombres_apellidos AS responsable
                ,IFNULL(sum(dh.numero_horas),0) numero_horas
                ,IFNULL(sum(dh.costo_por_hora),0) costo_por_hora
                ,IFNULL(sum(dh.total),0) total
                FROM horas_trabajo ht
                JOIN bodegas bp ON ht.id_bodega_principal = bp.id_bodega
                JOIN bodegas bs ON ht.id_bodega_secundaria = bs.id_bodega
                JOIN empleados e ON ht.id_responsable_obra = e.id_empleados
                LEFT JOIN horas_trabajo_empleado dh on ht.id_horas_trabajo = dh.id_horas_trabajo and dh.activo = 1
                $filtro
                GROUP BY ht.id_horas_trabajo,
                   ht.fecha,ht.id_bodega_principal,ht.id_bodega_secundaria,
                   ht.id_responsable_obra, ht.observacion,ht.fecha_creacion, ht.estado,
                   bp.nombreBodega,bs.nombreBodega,e.nombres_apellidos
                ORDER BY ht.id_horas_trabajo DESC";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function guardar($data) {
        $c= new conectar();
        $conexion = $c->conexion();
        $id = !empty($data['id_horas_trabajo']) ? intval($data['id_horas_trabajo']) : 0;
        $fecha = $data['fecha'];
        $id_bodega_principal = $data['id_bodega_principal'];
        $id_bodega_secundaria = $data['id_bodega_secundaria'];
        $id_responsable_obra = $data['id_responsable_obra'];
        $observacion = mysqli_real_escape_string($conexion, $data['observacion']);

        if ($id > 0) {
            $sql = "UPDATE horas_trabajo SET 
                        fecha='$fecha', id_bodega_principal='$id_bodega_principal', 
                        id_bodega_secundaria='$id_bodega_secundaria', id_responsable_obra='$id_responsable_obra',
                        observacion='$observacion'
                    WHERE id_horas_trabajo='$id'";
            mysqli_query($conexion, $sql);
            return ['success' => true, 'msg' => 'Registro actualizado'];
        } else {
            $sql = "INSERT INTO horas_trabajo (fecha, id_bodega_principal, id_bodega_secundaria, id_responsable_obra, observacion, estado)
                    VALUES ('$fecha', '$id_bodega_principal', '$id_bodega_secundaria', '$id_responsable_obra','$observacion', 1)";
            mysqli_query($conexion, $sql);
            $nuevoId = mysqli_insert_id($conexion);
            return ['success' => true, 'msg' => 'Registro creado', 'id_horas_trabajo' => $nuevoId];
        }
    }

    public function eliminar($id) {
        $c= new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE horas_trabajo SET estado=0 WHERE id_horas_trabajo=$id";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function obtener($id) {
        $c= new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT * FROM horas_trabajo WHERE id_horas_trabajo = $id";
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function listarBodegas() {
    $c = new conectar();
    $conexion = $c->conexion();
    $bodegas = [];
    // AHORA incluye es_bodega_secundaria e id_bodega_principal para el filtro dependiente
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
        $c = new conectar();
        $conexion = $c->conexion();
        $empleados = [];
        $sql = "SELECT id_empleados, nombres_apellidos FROM empleados WHERE estado=1";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $empleados[] = $row;
        return $empleados;
    }

    public function listarDetalleEmpleados($id_horas_trabajo) {
        $c = new conectar();
        $conexion = $c->conexion();
        $datos = [];
        $sql = "SELECT hte.*, e.nombres_apellidos 
                FROM horas_trabajo_empleado hte
                JOIN empleados e ON hte.id_empleado = e.id_empleados
                WHERE hte.id_horas_trabajo = $id_horas_trabajo AND hte.activo=1";
        $result = mysqli_query($conexion, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function agregarDetalleEmpleados($id_horas_trabajo, $id_empleado, $numero_horas, $costo_por_hora) {
        $c = new conectar();
        $conexion = $c->conexion();

        // Validación estricta: ¿Existe ya este empleado en este registro y activo?
        $sqlValida = "SELECT COUNT(*) as existe FROM horas_trabajo_empleado 
                      WHERE id_horas_trabajo='$id_horas_trabajo' 
                        AND id_empleado='$id_empleado' 
                        AND activo=1";
        $resValida = mysqli_query($conexion, $sqlValida);
        $rowValida = mysqli_fetch_assoc($resValida);

        if($rowValida['existe'] > 0) {
            return ['success' => false, 'msg' => 'El empleado ya está asignado a esta hora de trabajo.'];
        }

        $total = $numero_horas * $costo_por_hora;
        $sql = "INSERT INTO horas_trabajo_empleado 
                (id_horas_trabajo, id_empleado, numero_horas, costo_por_hora, total, activo) 
                VALUES ('$id_horas_trabajo', '$id_empleado', '$numero_horas', '$costo_por_hora', '$total', 1)";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function editarDetalleEmpleado($id_horas_trabajo_empleado, $id_empleado, $numero_horas, $costo_por_hora) {
        $c = new conectar();
        $conexion = $c->conexion();

        // Obtén el registro actual
        $sqlGet = "SELECT id_horas_trabajo, id_empleado FROM horas_trabajo_empleado WHERE id_horas_trabajo_empleado='$id_horas_trabajo_empleado'";
        $rsGet = mysqli_query($conexion, $sqlGet);
        $rowGet = mysqli_fetch_assoc($rsGet);
        $id_horas_trabajo = $rowGet['id_horas_trabajo'];
        $empleado_actual = $rowGet['id_empleado'];

        // Solo valida si está cambiando el empleado
        if ($id_empleado != $empleado_actual) {
            $sqlValida = "SELECT COUNT(*) as existe FROM horas_trabajo_empleado 
                  WHERE id_horas_trabajo='$id_horas_trabajo' 
                    AND id_empleado='$id_empleado'
                    AND activo=1
                    AND id_horas_trabajo_empleado != '$id_horas_trabajo_empleado'";
            $resValida = mysqli_query($conexion, $sqlValida);
            $rowValida = mysqli_fetch_assoc($resValida);

            if($rowValida['existe'] > 0) {
                return ['success' => false, 'msg' => 'El empleado ya está asignado a esta hora de trabajo.'];
            }
        }

        $total = $numero_horas * $costo_por_hora;
        $sql = "UPDATE horas_trabajo_empleado 
                SET id_empleado='$id_empleado', numero_horas='$numero_horas', costo_por_hora='$costo_por_hora', total='$total' 
                WHERE id_horas_trabajo_empleado='$id_horas_trabajo_empleado'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }

    public function obtenerDetalleEmpleado($id_horas_trabajo_empleado) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT * FROM horas_trabajo_empleado WHERE id_horas_trabajo_empleado = $id_horas_trabajo_empleado";
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function eliminarDetalleEmpleado($id_horas_trabajo_empleado) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE horas_trabajo_empleado SET activo=0 WHERE id_horas_trabajo_empleado='$id_horas_trabajo_empleado'";
        mysqli_query($conexion, $sql);
        return ['success' => true];
    }
}
?>