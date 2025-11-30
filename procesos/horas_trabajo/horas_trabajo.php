<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/HorasTrabajo.php";

$horas = new HorasTrabajo();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'listar':
        // FILTRO POR BODEGA SELECCIONADA
        $id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;
        echo json_encode($horas->listar($id_bodega));
        break;
    case 'obtener':
        $id = $_POST['id_horas_trabajo'] ?? 0;
        echo json_encode($horas->obtener($id));
        break;
    case 'guardar':
        echo json_encode($horas->guardar($_POST));
        break;
    case 'eliminar':
        $id = $_POST['id_horas_trabajo'] ?? 0;
        echo json_encode($horas->eliminar($id));
        break;
    case 'listar_bodegas':
        echo json_encode($horas->listarBodegas());
        break;
    case 'listar_empleados':
        echo json_encode($horas->listarEmpleados());
        break;
    // Detalle empleados
    case 'listar_detalle_empleados':
        $id_horas_trabajo = $_POST['id_horas_trabajo'] ?? 0;
        echo json_encode($horas->listarDetalleEmpleados($id_horas_trabajo));
        break; 
    case 'agregar_detalle_empleados':
        $id_horas_trabajo = $_POST['id_horas_trabajo'] ?? 0;
        $id_empleado = $_POST['id_empleado'] ?? 0;
        $numero_horas = $_POST['numero_horas'] ?? 0;
        $costo_por_hora = $_POST['costo_por_hora'] ?? 0;
        echo json_encode($horas->agregarDetalleEmpleados($id_horas_trabajo, $id_empleado, $numero_horas, $costo_por_hora));
        break;
    case 'editar_detalle_empleado':
        $id_horas_trabajo_empleado = $_POST['id_horas_trabajo_empleado'] ?? 0;
        $id_empleado = $_POST['id_empleado'] ?? 0;
        $numero_horas = $_POST['numero_horas'] ?? 0;
        $costo_por_hora = $_POST['costo_por_hora'] ?? 0;
        echo json_encode($horas->editarDetalleEmpleado($id_horas_trabajo_empleado, $id_empleado, $numero_horas, $costo_por_hora));
        break;
    case 'obtener_detalle_empleado':
        $id_horas_trabajo_empleado = $_POST['id_horas_trabajo_empleado'] ?? 0;
        echo json_encode($horas->obtenerDetalleEmpleado($id_horas_trabajo_empleado));
        break;
    case 'eliminar_detalle_empleado':
        $id_horas_trabajo_empleado = $_POST['id_horas_trabajo_empleado'] ?? 0;
        echo json_encode($horas->eliminarDetalleEmpleado($id_horas_trabajo_empleado));
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
}
?>