<?php
session_start();
require_once "../../clases/Conexion.php";

require_once "../../clases/PedidoMateriales.php";
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$pedidoMateriales = new PedidoMateriales();

switch ($action) {
    case 'listar':
        echo json_encode($pedidoMateriales->listar());
        break;
    case 'obtener':
        $id = $_GET['id_pedido_material'];
        echo json_encode($pedidoMateriales->obtener($id));
        break;
    case 'guardar':
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
        echo json_encode($pedidoMateriales->ordenes());
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
