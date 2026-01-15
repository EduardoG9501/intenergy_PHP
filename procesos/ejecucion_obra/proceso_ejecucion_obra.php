<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$ejecucionObra = new EjecucionObra();

switch ($action) {
	case 'contabilizar':
		$id_ejecucion_obra = $_POST['id_ejecucion_obra'] ?? null;
		$usuario = $_SESSION['usuario'] ?? null;
		if (!$id_ejecucion_obra || !$usuario) {
			echo json_encode(['success' => false, 'mensaje' => 'Faltan parámetros o sesión expirada.']);
			exit;
		}
		$result = $ejecucionObra->contabilizarYGenerarMovimiento($id_ejecucion_obra, $usuario);
		echo json_encode($result);
		break;
	case 'bodegas':
		// Devuelve solo bodegas activas y ordenadas por id_bodega asc
		require_once "../../clases/Conexion.php";
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT id_bodega, nombreBodega FROM bodegas WHERE estado = 1 ORDER BY id_bodega ASC";
		$result = mysqli_query($conexion, $sql);
		$bodegas = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$bodegas[] = $row;
		}
		echo json_encode($bodegas);
		break;
	case 'validar_stock':
		// Valida el stock disponible en stock_productos_bodega
		$id_producto = $_POST['id_producto'] ?? $_GET['id_producto'] ?? null;
		$id_bodega = $_POST['id_bodega'] ?? $_GET['id_bodega'] ?? null;
		$cantidad = $_POST['cantidad'] ?? $_GET['cantidad'] ?? null;
		if ($id_producto && $id_bodega && $cantidad) {
			require_once "../../clases/Conexion.php";
			$c = new conectar();
			$conexion = $c->conexion();
			$sql = "SELECT cantidad FROM stock_productos_bodega WHERE id_producto = '$id_producto' AND id_bodega_principal = '$id_bodega' AND estado = 1";
			$result = mysqli_query($conexion, $sql);
			$row = mysqli_fetch_assoc($result);
			$stock = $row ? floatval($row['cantidad']) : 0;
			$success = ($cantidad <= $stock);
			echo json_encode([
				'success' => $success,
				'stock' => $stock,
				'mensaje' => $success ? 'Stock suficiente' : 'Stock insuficiente'
			]);
		} else {
			echo json_encode(['success' => false, 'mensaje' => 'Parámetros incompletos']);
		}
		break;
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
