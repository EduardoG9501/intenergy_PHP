<?php
require_once "../../clases/Conexion.php";
require_once("../../clases/Reportes.php");

$reportes = new Reporte();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
 
switch($action) {
    case 'listar_proyectos':
        echo json_encode($reportes->listarProyectos());
        break;
    case 'listar_obras':
        $proyecto = isset($_POST['proyecto']) ? $_POST['proyecto'] : '';
        echo json_encode($reportes->listarObras($proyecto));
        break;
    case 'listar_articulos':
        echo json_encode($reportes->listarArticulos());
        break;
    case 'listar_lugares':
        echo json_encode($reportes->listarLugares());
        break;
    case 'listar_reporte_pedido_materiales':
        $desde = $_POST['desde'] ?? '';
        $hasta = $_POST['hasta'] ?? '';
        $proyecto = $_POST['proyecto'] ?? '';
        $obra = $_POST['obra'] ?? '';
        $articulo = $_POST['articulo'] ?? '';
        $lugar = $_POST['lugar'] ?? '';
        $idOrden = $_POST['id_orden'] ?? '';
        echo json_encode($reportes->reportePedidoMateriales($desde, $hasta, $proyecto, $obra, $articulo, $lugar, $idOrden));
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
}
