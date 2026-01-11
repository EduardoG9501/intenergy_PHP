<?php
class PedidoMateriales {
    // --- CRUD principal ---
    public function listar() {
        $c = new conectar();
        $conexion = $c->conexion();
        $where = '';
        if (isset($_GET['id_orden']) && is_numeric($_GET['id_orden'])) {
            $id_orden = intval($_GET['id_orden']);
            $where = " WHERE pm.id_orden = $id_orden ";
        }
        $sql = "SELECT pm.*, ot.identificador AS orden_identificador, ot.observacion AS orden_descripcion, epm.estado AS estado_nombre
                FROM pedido_materiales pm
                JOIN ordenes_trabajo ot ON pm.id_orden = ot.id_orden
                JOIN estado_pedido_materiales epm ON pm.id_estado_pedido_material = epm.id_estado_pedido_material
                $where
                ORDER BY pm.id_pedido_material DESC";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    // --- CRUD de detalle ---
    public function listarDetalle($id_pedido_material) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT pd.*, a.nombre as articulo_nombre FROM pedido_materiales_detalle pd JOIN articulos a ON pd.id_producto = a.id_producto WHERE pd.id_pedido_material = $id_pedido_material";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function guardarDetalle($data) {
        $c = new conectar();
        $conexion = $c->conexion();
        if (!empty($data['id_pedido_material_detalle'])) {
            $sql = "UPDATE pedido_materiales_detalle SET id_producto='{$data['id_producto']}', cantidad='{$data['cantidad']}' WHERE id_pedido_material_detalle={$data['id_pedido_material_detalle']}";
            $ok = mysqli_query($conexion, $sql);
            return [
                'success' => $ok,
                'id_pedido_material_detalle' => $data['id_pedido_material_detalle'],
                'mensaje' => $ok ? 'Detalle actualizado correctamente' : 'Error al actualizar detalle'
            ];
        } else {
            $sql = "INSERT INTO pedido_materiales_detalle (id_pedido_material, id_producto, cantidad, Estado) VALUES ('{$data['id_pedido_material']}', '{$data['id_producto']}', '{$data['cantidad']}', 1)";
            $ok = mysqli_query($conexion, $sql);
            $id = mysqli_insert_id($conexion);
            return [
                'success' => $ok,
                'id_pedido_material_detalle' => $id,
                'mensaje' => $ok ? 'Detalle guardado correctamente' : 'Error al guardar detalle'
            ];
        }
    }

    public function eliminarDetalle($id) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "DELETE FROM pedido_materiales_detalle WHERE id_pedido_material_detalle = $id";
        $ok = mysqli_query($conexion, $sql);
        return [
            'success' => $ok,
            'mensaje' => $ok ? 'Detalle eliminado correctamente' : 'Error al eliminar detalle'
        ];
    }

    public function obtener($id) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT * FROM pedido_materiales WHERE id_pedido_material = $id";
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function guardar($data) {
        $c = new conectar();
        $conexion = $c->conexion();
        if (!empty($data['id_pedido_material'])) {
            $sql = "UPDATE pedido_materiales SET
                        id_orden='{$data['id_orden']}',
                        fecha_solicitud='{$data['fecha_solicitud']}',
                        observacion='{$data['observacion']}'";
            if (isset($data['id_estado_pedido_material'])) {
                $sql .= ", id_estado_pedido_material='{$data['id_estado_pedido_material']}'";
            }
            $sql .= " WHERE id_pedido_material={$data['id_pedido_material']}";
            mysqli_query($conexion, $sql);
            return $data['id_pedido_material'];
        } else {
            $sql = "INSERT INTO pedido_materiales (
                        id_orden, fecha_solicitud, observacion, id_estado_pedido_material
                    ) VALUES (
                        '{$data['id_orden']}', '{$data['fecha_solicitud']}', '{$data['observacion']}', 1
                    )";
            mysqli_query($conexion, $sql);
            return mysqli_insert_id($conexion);
        }
    }

    public function estados() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_estado_pedido_material, estado FROM estado_pedido_materiales WHERE activo = 1";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function ordenes() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_orden, identificador FROM ordenes_trabajo WHERE estado = 1";
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
        $sql = "SELECT id_producto, nombre FROM articulos WHERE estado = 1";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }
}
