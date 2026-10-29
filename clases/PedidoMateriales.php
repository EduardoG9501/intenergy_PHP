<?php
class PedidoMateriales {
    // --- CRUD principal ---
    public function listar($id_orden = null, $id_bodega = null) {
        $c = new conectar();
        $conexion = $c->conexion();
        $filtros = [];
        if (!empty($id_orden) && is_numeric($id_orden)) {
            $filtros[] = "pm.id_orden = " . intval($id_orden);
        }
        if (!empty($id_bodega) && is_numeric($id_bodega)) {
            $filtros[] = "ot.id_bodega_principal = " . intval($id_bodega);
        }
        $where = '';
        if (!empty($filtros)) {
            $where = ' WHERE ' . implode(' AND ', $filtros);
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

    public function obtenerBodegaPedido($id_pedido_material) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT ot.id_bodega_principal FROM pedido_materiales pm JOIN ordenes_trabajo ot ON pm.id_orden = ot.id_orden WHERE pm.id_pedido_material = " . intval($id_pedido_material);
        $result = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row ? intval($row['id_bodega_principal']) : 0;
    }

    public function getStockProductoBodega($id_producto, $id_bodega) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT cantidad FROM stock_productos_bodega WHERE id_producto = " . intval($id_producto) . " AND id_bodega_principal = " . intval($id_bodega) . " AND estado = 1 LIMIT 1";
        $result = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($result);
        return $row ? floatval($row['cantidad']) : 0;
    }

    private function descontarStock($id_producto, $cantidad, $id_bodega) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE stock_productos_bodega SET cantidad = cantidad - " . floatval($cantidad) . " WHERE id_producto = " . intval($id_producto) . " AND id_bodega_principal = " . intval($id_bodega) . " AND estado = 1";
        return mysqli_query($conexion, $sql);
    }

    private function restaurarStock($id_producto, $cantidad, $id_bodega) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE stock_productos_bodega SET cantidad = cantidad + " . floatval($cantidad) . " WHERE id_producto = " . intval($id_producto) . " AND id_bodega_principal = " . intval($id_bodega) . " AND estado = 1";
        return mysqli_query($conexion, $sql);
    }

    public function guardarDetalle($data) {
        $c = new conectar();
        $conexion = $c->conexion();
        $id_pedido_material = intval($data['id_pedido_material']);
        $id_bodega = $this->obtenerBodegaPedido($id_pedido_material);
        if (!empty($data['id_pedido_material_detalle'])) {
            $detalleActual = $this->obtenerDetalle($data['id_pedido_material_detalle']);
            if ($detalleActual && $id_bodega > 0) {
                $this->restaurarStock($detalleActual['id_producto'], $detalleActual['cantidad'], $id_bodega);
            }
            $stock = $this->getStockProductoBodega($data['id_producto'], $id_bodega);
            if ($stock < floatval($data['cantidad'])) {
                if ($detalleActual && $id_bodega > 0) {
                    $this->descontarStock($detalleActual['id_producto'], $detalleActual['cantidad'], $id_bodega);
                }
                return [
                    'success' => false,
                    'mensaje' => 'Stock insuficiente. Disponible: ' . $stock
                ];
            }
            if ($id_bodega > 0) {
                $this->descontarStock($data['id_producto'], $data['cantidad'], $id_bodega);
            }
            $sql = "UPDATE pedido_materiales_detalle SET id_producto='{$data['id_producto']}', cantidad='{$data['cantidad']}' WHERE id_pedido_material_detalle={$data['id_pedido_material_detalle']}";
            $ok = mysqli_query($conexion, $sql);
            return [
                'success' => $ok,
                'id_pedido_material_detalle' => $data['id_pedido_material_detalle'],
                'mensaje' => $ok ? 'Detalle actualizado correctamente' : 'Error al actualizar detalle'
            ];
        } else {
            if ($id_bodega > 0) {
                $stock = $this->getStockProductoBodega($data['id_producto'], $id_bodega);
                if ($stock < floatval($data['cantidad'])) {
                    return [
                        'success' => false,
                        'mensaje' => 'Stock insuficiente. Disponible: ' . $stock
                    ];
                }
                $this->descontarStock($data['id_producto'], $data['cantidad'], $id_bodega);
            }
            $sql = "INSERT INTO pedido_materiales_detalle (id_pedido_material, id_producto, cantidad, Estado) VALUES ('{$data['id_pedido_material']}', '{$data['id_producto']}', '{$data['cantidad']}', 1)";
            $ok = mysqli_query($conexion, $sql);
            $id = mysqli_insert_id($conexion);
            return [
                'success' => $ok,
                'id_pedido_material_detalle' => $id,
                'mensaje' => $ok ? 'Detalle guardado y stock descontado correctamente' : 'Error al guardar detalle'
            ];
        }
    }

    private function obtenerDetalle($id_detalle) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT * FROM pedido_materiales_detalle WHERE id_pedido_material_detalle = " . intval($id_detalle);
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function eliminarDetalle($id) {
        $c = new conectar();
        $conexion = $c->conexion();
        $detalle = $this->obtenerDetalle($id);
        if ($detalle) {
            $id_bodega = $this->obtenerBodegaPedido($detalle['id_pedido_material']);
            if ($id_bodega > 0) {
                $this->restaurarStock($detalle['id_producto'], $detalle['cantidad'], $id_bodega);
            }
        }
        $sql = "DELETE FROM pedido_materiales_detalle WHERE id_pedido_material_detalle = $id";
        $ok = mysqli_query($conexion, $sql);
        return [
            'success' => $ok,
            'mensaje' => $ok ? 'Detalle eliminado y stock restaurado correctamente' : 'Error al eliminar detalle'
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

    public function ordenes($id_bodega = null, $id_orden_especifica = null) {
        $c = new conectar();
        $conexion = $c->conexion();
        if (!empty($id_orden_especifica) && is_numeric($id_orden_especifica)) {
            $sql = "SELECT id_orden, identificador FROM ordenes_trabajo WHERE id_orden = " . intval($id_orden_especifica) . " AND estado = 1";
            $result = mysqli_query($conexion, $sql);
            $datos = [];
            while ($fila = mysqli_fetch_assoc($result)) {
                $datos[] = $fila;
            }
            return $datos;
        }
        $sql = "SELECT id_orden, identificador FROM ordenes_trabajo WHERE estado = 1 ORDER BY id_orden DESC";
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
        if (!$conexion) {
            return ['error' => 'No hay conexion a BD'];
        }
        $sql = "SELECT id_producto, nombre FROM articulos WHERE estado = 1";
        $result = mysqli_query($conexion, $sql);
        if (!$result) {
            return ['error' => 'Error en consulta: ' . mysqli_error($conexion)];
        }
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function cerrarPedido($id_pedido_material) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sqlCerrar = "SELECT id_estado_pedido_material FROM estado_pedido_materiales WHERE UPPER(estado) LIKE '%TERMINADO%' OR UPPER(estado) LIKE '%CERRADO%' LIMIT 1";
        $resCerrar = mysqli_query($conexion, $sqlCerrar);
        $rowCerrar = $resCerrar ? mysqli_fetch_assoc($resCerrar) : null;
        $id_estado_cerrado = $rowCerrar ? intval($rowCerrar['id_estado_pedido_material']) : 2;
        $sql = "UPDATE pedido_materiales SET id_estado_pedido_material = $id_estado_cerrado WHERE id_pedido_material = " . intval($id_pedido_material);
        $ok = mysqli_query($conexion, $sql);
        return [
            'success' => $ok,
            'mensaje' => $ok ? 'Pedido cerrado correctamente' : 'Error al cerrar pedido'
        ];
    }
}
