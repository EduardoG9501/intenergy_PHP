<?php
require_once "../../clases/Conexion.php";
require_once("../../clases/Reportes.php");

$reporte = new Reporte();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action){
    case 'listar_bodegas':
        echo json_encode($reporte->listarBodegas());
        break;

    case 'listar_estados_solicitud':
        echo json_encode($reporte->listarEstadosSolicitudMateriales());
        break;

    case 'listar_estados_articulo':
        echo json_encode($reporte->listarEstadosSolicitudMaterialesArticulos());
        break;

    case 'listar_articulos':
        echo json_encode($reporte->listarArticulos());
        break;

    case 'listar_reporte_solicitudes':
        $filtros = [
            'fecha_desde' => $_POST['fecha_desde'] ?? '',
            'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
            'id_bodega_principal' => $_POST['id_bodega_principal'] ?? '',
            'id_bodega_secundaria' => $_POST['id_bodega_secundaria'] ?? '',
            'id_estado' => $_POST['id_estado'] ?? '',
            'id_articulo' => $_POST['id_articulo'] ?? '',
            'id_estado_articulo' => $_POST['id_estado_articulo'] ?? ''
        ];
        $data = $reporte->listarReporteSolicitudesMateriales($filtros);
        if (!is_array($data)) $data = [];
        echo json_encode($data);
        break;

    case 'exportar_excel_solicitudes':
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'id_bodega_principal' => $_GET['id_bodega_principal'] ?? '',
            'id_bodega_secundaria' => $_GET['id_bodega_secundaria'] ?? '',
            'id_estado' => $_GET['id_estado'] ?? '',
            'id_articulo' => $_GET['id_articulo'] ?? '',
            'id_estado_articulo' => $_GET['id_estado_articulo'] ?? ''
        ];
        $data = $reporte->listarReporteSolicitudesMateriales($filtros);
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=solicitudes_materiales.xls");
        echo "ID Solicitud\tFecha\tBodega Principal\tBodega Secundaria\tArtículo\tCantidad\tEstado Solicitud\tEstado Solicitud Artículo\n";
        foreach($data as $row){
            echo "{$row['id_solicitud_material']}\t{$row['fecha_solicitud']}\t{$row['bodega_principal']}\t{$row['bodega_secundaria']}\t{$row['articulo']}\t{$row['cantidad']}\t{$row['estado_solicitud']}\t{$row['estado_solicitud_articulo']}\n";
        }
        break;

    default:
        echo json_encode(['error'=>'Acción no válida']);
}
?>