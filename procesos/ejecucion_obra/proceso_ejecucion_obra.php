<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$ejecucionObra = new EjecucionObra();

switch ($action) {
	case 'listar':
		echo json_encode($ejecucionObra->listar());
		break;
	case 'obtener':
		$id = $_GET['id_ejecucion_obra'];
		echo json_encode($ejecucionObra->obtener($id));
		break;
	case 'guardar':
		$id = $ejecucionObra->guardar($_POST);
		echo json_encode([
			'id_ejecucion_obra' => $id,
			'success' => $id ? true : false,
			'mensaje' => $id ? 'Ejecución de obra guardada correctamente' : 'Error al guardar ejecución de obra'
		]);
		break;
	case 'estados':
		echo json_encode($ejecucionObra->estados());
		break;
	case 'ordenes':
		echo json_encode($ejecucionObra->ordenes());
		break;
	case 'articulos':
		echo json_encode($ejecucionObra->articulos());
		break;
	case 'listar_detalle':
		$id = $_GET['id_ejecucion_obra'];
		echo json_encode($ejecucionObra->listarDetalle($id));
		break;
	case 'guardar_detalle':
		$result = $ejecucionObra->guardarDetalle($_POST);
		echo json_encode($result);
		break;
	case 'eliminar_detalle':
		$id = $_POST['id_ejecucion_obra_detalle_materiales_utilizar'];
		$result = $ejecucionObra->eliminarDetalle($id);
		echo json_encode($result);
		break;
	default:
		echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
		break;
}
