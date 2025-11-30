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
            'fecha_inicial' => $_POST['fecha_inicial'] ?? '',
            'fecha_final' => $_POST['fecha_final'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'ubicacion' => $_POST['ubicacion'] ?? '',
            'estado' => $_POST['estado'] ?? ''
        ];
        $data = $reporte->listarReporteOrdenes($filtros);
        // Garantiza que sea un array
        if (!is_array($data)) $data = [];
        echo json_encode($data);
        break;

    case 'exportar_excel_ordenes':
        $filtros = [
            'fecha_inicial' => $_GET['fecha_inicial'] ?? '',
            'fecha_final' => $_GET['fecha_final'] ?? '',
            'lugar' => $_GET['lugar'] ?? '',
            'ubicacion' => $_GET['ubicacion'] ?? '',
            'estado' => $_GET['estado'] ?? ''
        ];
        $data = $reporte->listarReporteOrdenes($filtros);
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=ordenes_trabajo.xls");
        echo "ID Orden\tCosto\tFecha Inicial\tFecha Final\tLugar\tUbicación\tObservación\tEstado\n";
        foreach($data as $row){
            echo "{$row['id_orden']}\t{$row['costo']}\t{$row['fecha_inicial']}\t{$row['fecha_final']}\t{$row['lugar']}\t{$row['ubicacion']}\t{$row['observacion']}\t{$row['estado']}\n";
        }
        break;

    default:
        echo json_encode(['error'=>'Acción no válida']);
}
?>