<?php
class SolicitudMaterial {
    // Ahora acepta $id_bodega como parámetro
    public function listar($id_bodega = null) {
        $c = new conectar();
        $conexion = $c->conexion();

        $filtro = "";
        if ($id_bodega) {
            $id_bodega = intval($id_bodega);
            $filtro = "WHERE sm.id_bodega_principal = $id_bodega";
        }

        $sql = "SELECT sm.*, 
                       ot.identificador AS orden_identificador,
                       ot.observacion AS orden_descripcion,
                       bp.nombrebodega AS bodega_principal,
                       bs.nombrebodega AS bodega_secundaria,
                       e1.nombres_apellidos AS responsable_obra,
                       e2.nombres_apellidos AS entrega_articulos,
                       e3.nombres_apellidos AS recibe_articulos,
                       esm.estado AS estado_nombre
                FROM solicitud_materiales sm
                JOIN ordenes_trabajo ot ON sm.id_orden = ot.id_orden
                JOIN bodegas bp ON sm.id_bodega_principal = bp.id_bodega
                JOIN bodegas bs ON sm.id_bodega_secundaria = bs.id_bodega
                JOIN empleados e1 ON sm.id_responsable_obra = e1.id_empleados
                JOIN empleados e2 ON sm.id_entrega_articulos = e2.id_empleados
                JOIN empleados e3 ON sm.id_recibe_articulos = e3.id_empleados
                JOIN estado_solicitud_materiales esm ON sm.id_estado_solicitud = esm.id_estado_solicitud
                $filtro
                ORDER BY sm.id_solicitud_material DESC";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    } 
    public function obtener($id) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT * FROM solicitud_materiales WHERE id_solicitud_material = $id";
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function guardar($data) {
    $c = new conectar();
    $conexion = $c->conexion();
    // Si viene el id, EDITA
    if (!empty($data['id_solicitud_material'])) {
        $sql = "UPDATE solicitud_materiales SET
                    id_orden='{$data['id_orden']}',
                    fecha_solicitud='{$data['fecha_solicitud']}',
                    id_bodega_principal='{$data['id_bodega_principal']}',
                    id_bodega_secundaria='{$data['id_bodega_secundaria']}',
                    id_responsable_obra='{$data['id_responsable_obra']}',
                    id_entrega_articulos='{$data['id_entrega_articulos']}',
                    id_recibe_articulos='{$data['id_recibe_articulos']}',
                    observacion='{$data['observacion']}'";
        // Solo agrega el estado si está presente
        if (isset($data['id_estado_solicitud'])) {
            $sql .= ", id_estado_solicitud='{$data['id_estado_solicitud']}'";
        }
        $sql .= " WHERE id_solicitud_material={$data['id_solicitud_material']}";
        mysqli_query($conexion, $sql);
        return $data['id_solicitud_material'];
    } else {
        // Si NO viene el id, CREA NUEVO
        $sql = "INSERT INTO solicitud_materiales (
                    id_orden, fecha_solicitud, id_bodega_principal, id_bodega_secundaria, 
                    id_responsable_obra, id_entrega_articulos, id_recibe_articulos, observacion, id_estado_solicitud
                ) VALUES (
                    '{$data['id_orden']}', '{$data['fecha_solicitud']}', '{$data['id_bodega_principal']}',
                    '{$data['id_bodega_secundaria']}', '{$data['id_responsable_obra']}', '{$data['id_entrega_articulos']}',
                    '{$data['id_recibe_articulos']}', '{$data['observacion']}', 1
                )";
        mysqli_query($conexion, $sql);
        return mysqli_insert_id($conexion); // Devuelve el ID de la nueva solicitud
    }
}

    public function bodegas() {
        $c = new conectar();
        $conexion = $c->conexion();
        // Ahora trae también es_bodega_secundaria e id_bodega_principal
        $sql = "SELECT id_bodega, nombrebodega AS nombre, es_bodega_secundaria, id_bodega_principal 
                FROM bodegas 
                WHERE estado = 1";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            // Asegura que los campos sean del tipo correcto para el frontend JS
            $fila['es_bodega_secundaria'] = intval($fila['es_bodega_secundaria']);
            $fila['id_bodega_principal'] = $fila['id_bodega_principal'] !== null ? intval($fila['id_bodega_principal']) : null;
            $datos[] = $fila;
        }
        return $datos;
    }

    public function empleados() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_empleados, nombres_apellidos nombre FROM empleados";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function estados() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_estado_solicitud, estado FROM estado_solicitud_materiales WHERE activo = 1";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function articulos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_producto, nombre FROM articulos where estado = 1";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    } 

public function existeSolicitudPorOrden($id_orden, $id_solicitud_material = null) {
    $c = new conectar();
    $conexion = $c->conexion();
    $where = "id_orden = '$id_orden' and id_estado_solicitud in (1,2,3)";
    // Si es edición, excluye la actual solicitud
    if ($id_solicitud_material) {
        $where .= " AND id_solicitud_material != '$id_solicitud_material'";
    }
    $sql = "SELECT COUNT(*) as total FROM solicitud_materiales WHERE $where";
    $res = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['total'] > 0;
}
 public function listarorden($id_solicitud_material) {
        $c= new conectar();
	    $conexion=$c->conexion();
        $sql = "SELECT id_orden, identificador
                FROM ordenes_trabajo ot
                where ot.estado = 1
                and id_orden not in (select id_orden from solicitud_materiales where id_orden not in ('$id_solicitud_material') and id_estado_solicitud  in (1,2,3))
                ORDER BY ot.id_orden DESC";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }
}
?>