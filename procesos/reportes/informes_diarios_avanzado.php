<?php
require_once "../../clases/Conexion.php";
require_once("../../clases/Reportes.php");

$reporte = new Reporte();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action){
    case 'listar_bodegas':
        echo json_encode($reporte->listarBodegas());
        break;
    case 'listar_empleados_internos':
        echo json_encode($reporte->listarEmpleadosInternos());
        break;
    case 'listar_empleados_externos':
        echo json_encode($reporte->listarEmpleadosExternos());
        break;
    case 'listar_articulos':
        echo json_encode($reporte->listarArticulos());
        break;
    case 'listar_reporte_informes_diarios_avanzado':
        $filtros = [
            'fecha_desde' => $_POST['fecha_desde'] ?? '',
            'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
            'id_bodega_principal' => $_POST['id_bodega_principal'] ?? '',
            'id_bodega_secundaria' => $_POST['id_bodega_secundaria'] ?? '',
            'tipo_informe' => $_POST['tipo_informe'] ?? '',
            'id_empleado' => $_POST['id_empleado'] ?? '',
            'id_empleado_externo' => $_POST['id_empleado_externo'] ?? '',
            'id_articulo' => $_POST['id_articulo'] ?? ''
        ];
        if($filtros['tipo_informe']=='general'){
            $data = $reporte->listarReporteGeneralInformesDiarios($filtros);
        }else{
            $data = $reporte->listarReporteInformesDiariosAvanzado($filtros);
        }
        if (!is_array($data)) $data = [];
        echo json_encode($data);
        break;
    case 'exportar_excel_informes_diarios':
    $filtros = [
        'fecha_desde' => $_GET['fecha_desde'] ?? '',
        'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
        'id_bodega_principal' => $_GET['id_bodega_principal'] ?? '',
        'id_bodega_secundaria' => $_GET['id_bodega_secundaria'] ?? '',
        'tipo_informe' => $_GET['tipo_informe'] ?? '',
        'id_empleado' => $_GET['id_empleado'] ?? '',
        'id_empleado_externo' => $_GET['id_empleado_externo'] ?? '',
        'id_articulo' => $_GET['id_articulo'] ?? ''
    ];
    $data = $reporte->listarReporteInformesDiariosAvanzado($filtros);

    $tipo = $filtros['tipo_informe'];
    if ($tipo == 'general') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=informes_diarios_general.xls");
        echo "ID Informe\tIdentificador\tFecha\tBodega Principal\tBodega Secundaria\tArtículos\tEmpleado Interno\tEmpleado Externo\tImágenes\tObservación\n";
        foreach($data as $row){
            $articulos = "";
            if (!empty($row['articulos'])) {
                $articulos = implode(' | ', array_map(function($a){return $a['nombre']." (".$a['cantidad'].") [Lote: ".($a['lote']?:'-')."]";}, $row['articulos']));
            }
            $empleados_internos = implode(", ", $row['empleados_internos'] ?? []);
            $empleados_externos = implode(", ", $row['empleados_externos'] ?? []);
            $imagenes = implode(", ", $row['imagenes'] ?? []);
            echo "{$row['id_informe']}\t{$row['identificador']}\t{$row['fecha']}\t{$row['bodega_principal']}\t{$row['bodega_secundaria']}\t{$articulos}\t{$empleados_internos}\t{$empleados_externos}\t{$imagenes}\t{$row['observacion']}\n";
        }
        exit;
    }
    // ... resto igual para los otros tipos
    if ($tipo == 'interno') {
        echo "ID Informe\tIdentificador\tFecha\tBodega Principal\tBodega Secundaria\tEmpleado Interno\tObservación\n";
        foreach($data as $row){
            echo "{$row['id_informe']}\t{$row['identificador']}\t{$row['fecha']}\t{$row['bodega_principal']}\t{$row['bodega_secundaria']}\t{$row['empleado']}\t{$row['observacion']}\n";
        }
    } elseif ($tipo == 'externo') {
        echo "ID Informe\tIdentificador\tFecha\tBodega Principal\tBodega Secundaria\tEmpleado Externo\tObservación\n";
        foreach($data as $row){
            echo "{$row['id_informe']}\t{$row['identificador']}\t{$row['fecha']}\t{$row['bodega_principal']}\t{$row['bodega_secundaria']}\t{$row['empleado_externo']}\t{$row['observacion']}\n";
        }
    } elseif ($tipo == 'articulo') {
        echo "ID Informe\tIdentificador\tFecha\tBodega Principal\tBodega Secundaria\tArtículo\tCantidad\tObservación\n";
        foreach($data as $row){
            echo "{$row['id_informe']}\t{$row['identificador']}\t{$row['fecha']}\t{$row['bodega_principal']}\t{$row['bodega_secundaria']}\t{$row['articulo']}\t{$row['cantidad']}\t{$row['observacion']}\n";
        }
    } else {
        echo "ID Informe\tIdentificador\tFecha\tBodega Principal\tBodega Secundaria\n";
        foreach($data as $row){
            echo "{$row['id_informe']}\t{$row['identificador']}\t{$row['fecha']}\t{$row['bodega_principal']}\t{$row['bodega_secundaria']}\n";
        }
    }
    break;
    default:
        echo json_encode(['error'=>'Acción no válida']);
}
?>