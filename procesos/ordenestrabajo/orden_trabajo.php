<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/OrdenTrabajo.php";
 
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

$ordenTrabajo = new OrdenTrabajo();

switch ($action) {
    case 'listar':
        // FILTRO POR BODEGA SELECCIONADA
        $id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;
        echo json_encode($ordenTrabajo->listar($id_bodega));
        break;
    case 'obtener':
        $id = $_GET['id_orden'];
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