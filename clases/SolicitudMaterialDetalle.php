<?php
class SolicitudMaterialDetalle {
    public function listarPorSolicitud($id_solicitud_material) {
        $c = new conectar();
        $conexion = $c->conexion();
        $id = mysqli_real_escape_string($conexion, $id_solicitud_material);
        $sql = "SELECT d.id_solicitud_material_detalle, d.id_producto, a.nombre AS nombre_articulo, d.cantidad,
               d.id_detalle_articulo, ea.estado as estado_detalle
        FROM solicitud_materiales_detalle d
        JOIN articulos a ON d.id_producto = a.id_producto
        JOIN estado_solicitud_materiales_articulos ea ON d.id_detalle_articulo = ea.id_estado_solicitud_articulos
        WHERE d.id_solicitud_material = '$id'";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function guardar($data) {
        $c = new conectar();
        $conexion = $c->conexion();
        $id_detalle = mysqli_real_escape_string($conexion, $data['id_solicitud_material_detalle'] ?? '');
        $id_solicitud = mysqli_real_escape_string($conexion, $data['id_solicitud_material']);
        $id_producto = mysqli_real_escape_string($conexion, $data['id_producto']);
        $cantidad = mysqli_real_escape_string($conexion, $data['cantidad']);
        $id_estado = mysqli_real_escape_string($conexion, $data['id_detalle_articulo']);

        if ($id_detalle) {
            // Editar
            $sql = "UPDATE solicitud_materiales_detalle SET id_producto='$id_producto', cantidad='$cantidad', id_detalle_articulo='$id_estado'
                    WHERE id_solicitud_material_detalle='$id_detalle'";
            $ok = mysqli_query($conexion, $sql);
            return [
                'success' => $ok,
                'mensaje' => $ok ? 'Artículo editado correctamente.' : 'Error al editar artículo.'
            ];
        } else {
            // Validar que el producto no exista ya en la solicitud
            $sqlVerifica = "SELECT COUNT(*) AS total FROM solicitud_materiales_detalle 
                            WHERE id_solicitud_material='$id_solicitud' AND id_producto='$id_producto'";
            $resVerifica = mysqli_query($conexion, $sqlVerifica);
            $row = mysqli_fetch_assoc($resVerifica);
            if ($row['total'] > 0) {
                return [
                    'success' => false,
                    'mensaje' => 'Este artículo ya fue agregado a la solicitud.'
                ];
            }
            // Agregar
            $sql = "INSERT INTO solicitud_materiales_detalle (id_solicitud_material, id_producto, cantidad, id_detalle_articulo)
                    VALUES ('$id_solicitud', '$id_producto', '$cantidad', '$id_estado')";
            $ok = mysqli_query($conexion, $sql);
            return [
                'success' => $ok,
                'mensaje' => $ok ? 'Artículo agregado correctamente.' : 'Error al agregar artículo.'
            ];
        }
    }

    public function eliminar($id_solicitud_material_detalle) {
        $c = new conectar();
        $conexion = $c->conexion();
        $id = mysqli_real_escape_string($conexion, $id_solicitud_material_detalle);
        $sql = "DELETE FROM solicitud_materiales_detalle WHERE id_solicitud_material_detalle = '$id'";
        $ok = mysqli_query($conexion, $sql);
        return [
            'success' => $ok,
            'mensaje' => $ok ? 'Artículo eliminado correctamente.' : 'Error al eliminar artículo.'
        ];
    }

    public function buscarProductos($q) {
        $c = new conectar();
        $conexion = $c->conexion();
        $q = mysqli_real_escape_string($conexion, $q);
        $sql = "SELECT id_producto, nombre FROM articulos WHERE estado=1 AND nombre LIKE '%$q%' LIMIT 30";
        $result = mysqli_query($conexion, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    public function estadosArticulo() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_estado_solicitud_articulos, estado FROM estado_solicitud_materiales_articulos WHERE activo = 1";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }
    public function getStockProductoBodega($id_producto, $id_bodega) {
    $c = new conectar();
    $conexion = $c->conexion();
    $id_producto = mysqli_real_escape_string($conexion, $id_producto);
    $id_bodega = mysqli_real_escape_string($conexion, $id_bodega);
    $sql = "SELECT cantidad stock FROM stock_productos_bodega WHERE id_producto = '$id_producto' AND id_bodega_principal = '$id_bodega' LIMIT 1";
    $result = mysqli_query($conexion, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        return floatval($row['stock']);
    }
    return 0;
}

}
?>