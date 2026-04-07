<?php
require_once "../../clases/Conexion.php";
require_once("../../clases/Reportes.php");

$reportes = new Reporte();
 
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$filtros = isset($_POST['filtros']) ? $_POST['filtros'] : [];
if (!is_array($filtros)) {
    $filtros = [];
}
if (empty($filtros['id_orden']) && isset($_POST['id_orden'])) {
    $filtros['id_orden'] = $_POST['id_orden'];
}

switch ($accion) {
    case 'materiales':
        $data = $reportes->reporteMaterialesEjecucionObra($filtros);
        echo json_encode($data);
        break;
    case 'horas':
        $data = $reportes->reporteHorasTrabajoEjecucionObra($filtros);
        echo json_encode($data);
        break;
    case 'informes':
        $data = $reportes->reporteInformesDiariosEjecucionObra($filtros);
        echo json_encode($data);
        break;
    default:
        echo json_encode([]);
        break;

    case 'listar_proyectos':
            $data = $reportes->listarProyectos();
            echo json_encode($data);
            break;
    case 'listar_obras':
            $data = $reportes->listarObras(isset($_POST['proyecto']) ? $_POST['proyecto'] : '');
            echo json_encode($data);
            break;
    case 'listar_bodegas':
            $data = $reportes->listarBodegas();
            echo json_encode($data);
            break;
}