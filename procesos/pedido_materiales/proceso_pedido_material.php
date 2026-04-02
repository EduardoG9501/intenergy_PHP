<?php
session_start();
require_once "../../clases/Conexion.php";

require_once "../../clases/PedidoMateriales.php";
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$pedidoMateriales = new PedidoMateriales();

switch ($action) {
    case 'listar':
        $idOrden = isset($_GET['id_orden']) ? intval($_GET['id_orden']) : null;
        $idBodegaSesion = isset($_SESSION['bodega_seleccionada']) ? intval($_SESSION['bodega_seleccionada']) : null;
        echo json_encode($pedidoMateriales->listar($idOrden, $idBodegaSesion));
        break;
    case 'obtener':
        $id = $_GET['id_pedido_material'];
        echo json_encode($pedidoMateriales->obtener($id));
        break;
    case 'guardar':
        $idOrden = isset($_POST['id_orden']) ? intval($_POST['id_orden']) : 0;
        $idBodegaSesion = isset($_SESSION['bodega_seleccionada']) ? intval($_SESSION['bodega_seleccionada']) : 0;
        if ($idOrden <= 0 || $idBodegaSesion <= 0) {
            echo json_encode([
                'id_pedido_material' => 0,
                'success' => false,
                'mensaje' => 'Debe tener una bodega activa y seleccionar una orden valida.'
            ]);
            exit;
        }
        $c = new conectar();
        $conexion = $c->conexion();
        $sqlValidacionOrden = "SELECT COUNT(*) AS total FROM ordenes_trabajo WHERE id_orden = $idOrden AND id_bodega_principal = $idBodegaSesion AND estado = 1";
        $resValidacionOrden = mysqli_query($conexion, $sqlValidacionOrden);
        $rowValidacionOrden = $resValidacionOrden ? mysqli_fetch_assoc($resValidacionOrden) : null;
        $ordenPermitida = $rowValidacionOrden ? intval($rowValidacionOrden['total']) > 0 : false;
        if (!$ordenPermitida) {
            echo json_encode([
                'id_pedido_material' => 0,
                'success' => false,
                'mensaje' => 'La orden seleccionada no pertenece a la bodega activa.'
            ]);
            exit;
        }
        $id = $pedidoMateriales->guardar($_POST);
        echo json_encode([
            'id_pedido_material' => $id,
            'success' => $id ? true : false,
            'mensaje' => $id ? 'Pedido guardado correctamente' : 'Error al guardar pedido'
        ]);
        break;
    case 'estados':
        echo json_encode($pedidoMateriales->estados());
        break;
    case 'ordenes':
        $idBodegaSesion = isset($_SESSION['bodega_seleccionada']) ? intval($_SESSION['bodega_seleccionada']) : null;
        echo json_encode($pedidoMateriales->ordenes($idBodegaSesion));
        break;
    case 'articulos':
        echo json_encode($pedidoMateriales->articulos());
        break;
    case 'listar_detalle':
        $id = $_GET['id_pedido_material'];
        echo json_encode($pedidoMateriales->listarDetalle($id));
        break;
    case 'guardar_detalle':
        $result = $pedidoMateriales->guardarDetalle($_POST);
        echo json_encode($result);
        break;
    case 'eliminar_detalle':
        $id = $_POST['id_pedido_material_detalle'];
        $result = $pedidoMateriales->eliminarDetalle($id);
        echo json_encode($result);
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
        break;
}
