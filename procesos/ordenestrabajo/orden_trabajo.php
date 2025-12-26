<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/OrdenTrabajo.php";
require_once "../../clases/Proyecto.php";
require_once "../../clases/Obra.php";
 
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

$ordenTrabajo = new OrdenTrabajo();

switch ($action) {
        case 'proyectos':
            $proyecto = new Proyecto();
            echo json_encode($proyecto->obtenerProyectos());
            break;
        case 'obras':
            $obra = new Obra();
            echo json_encode($obra->obtenerObras());
            break;
    case 'listar':
        // FILTRO POR BODEGA SELECCIONADA
        $id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;
        echo json_encode($ordenTrabajo->listar($id_bodega));
        break;
    case 'obtener':
        // Permitir tanto id_orden como id (por compatibilidad)
        $id = isset($_GET['id_orden']) ? $_GET['id_orden'] : (isset($_GET['id']) ? $_GET['id'] : null);
        if ($id === null || $id === '') {
            echo json_encode(['error' => 'ID de orden no especificado']);
            exit;
        }
        echo json_encode($ordenTrabajo->obtener($id));
        break;
    case 'guardar':
        echo json_encode($ordenTrabajo->guardar($_POST));
        break;
    case 'bodegas':
        echo json_encode($ordenTrabajo->bodegas());
        break;
    case 'estados':
        echo json_encode($ordenTrabajo->estados());
        break;
}
?>