<?php
require_once "../../clases/Conexion.php";
require_once("../../clases/Reportes.php");

$reporte = new Reporte();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action){
    case 'listar_bodegas':
        echo json_encode($reporte->listarBodegas());
        break;

    case 'listar_reporte_ordenes':
        $filtros = [
            'desde' => $_POST['desde'] ?? '',
            'hasta' => $_POST['hasta'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'proyecto' => $_POST['proyecto'] ?? '',
            'obra' => $_POST['obra'] ?? ''
        ];
        $data = $reporte->listarReporteOrdenes($filtros);
        if (!is_array($data)) $data = [];
        echo json_encode($data);
        break;

    case 'exportar_excel_ordenes':
        $filtros = [
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
            'lugar' => $_GET['lugar'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'proyecto' => $_GET['proyecto'] ?? '',
            'obra' => $_GET['obra'] ?? ''
        ];
        $data = $reporte->listarReporteOrdenes($filtros);
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=ordenes_trabajo.xls");
        echo "ID Orden\tCosto\tFecha\tLugar\tObservación\tEstado\n";
        foreach($data as $row){
            echo "{$row['id_orden']}\t{$row['costo']}\t{$row['fecha_inicial']}\t{$row['lugar']}\t{$row['observacion']}\t{$row['estado']}\n";
        }
        break;

    default:
        echo json_encode(['error'=>'Acción no válida']);
    case 'listar_proyectos':
        require_once("../../clases/Proyecto.php");
        $proyObj = new Proyecto();
        echo json_encode($proyObj->obtenerProyectos());
        break;

    case 'listar_obras':
        require_once("../../clases/Obra.php");
        $obraObj = new Obra();
        echo json_encode($obraObj->obtenerObras());
        break;

    case 'listar_reporte_ordenes':
        $filtros = [
            'desde' => $_POST['desde'] ?? '',
            'hasta' => $_POST['hasta'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'proyecto' => $_POST['proyecto'] ?? '',
            'obra' => $_POST['obra'] ?? ''
        ];
        $data = $reporte->listarReporteOrdenes($filtros);
        if (!is_array($data)) $data = [];
        echo json_encode($data);
        break;

    case 'exportar_excel_ordenes':
        $filtros = [
            'fecha_inicial' => $_GET['fecha_inicial'] ?? '',
            'fecha_final' => $_GET['fecha_final'] ?? '',
            'lugar' => $_GET['lugar'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'proyecto' => $_GET['proyecto'] ?? '',
            'obra' => $_GET['obra'] ?? ''
        ];
        $data = $reporte->listarReporteOrdenes($filtros);
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=ordenes_trabajo.xls");
        echo "ID Orden\tProyecto\tObra\tCosto\tFecha Inicial\tFecha Final\tLugar\tObservación\tEstado\n";
        foreach($data as $row){
            echo "{$row['id_orden']}\t{$row['proyecto']}\t{$row['obra']}\t{$row['costo']}\t{$row['fecha_inicial']}\t{$row['fecha_final']}\t{$row['lugar']}\t{$row['observacion']}\t{$row['estado']}\n";
        }
        break;
}
?>