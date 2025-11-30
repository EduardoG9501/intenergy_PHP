<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Reportes.php";

$reporte = new Reporte();

$action = $_REQUEST['action'] ?? '';

switch($action)
{
    case 'bodegas':
        echo json_encode($reporte->listarBodegas());
        break;

    case 'responsables':
        echo json_encode($reporte->listarEmpleadosInternos());
        break;

    case 'empleados':
        echo json_encode($reporte->listarEmpleadosInternos());
        break;

    case 'listar_reporte':
        $filtros = [
            "fecha_desde" => $_POST['fecha_desde'] ?? "",
            "fecha_hasta" => $_POST['fecha_hasta'] ?? "",
            "id_bodega_principal" => $_POST['id_bodega_principal'] ?? "",
            "id_bodega_secundaria" => $_POST['id_bodega_secundaria'] ?? "",
            "id_responsable_obra" => $_POST['id_responsable_obra'] ?? "",
            "id_empleado" => $_POST['id_empleado'] ?? "",
        ];
        echo json_encode($reporte->listarReporteHorasTrabajo($filtros));
        break;

    case 'exportar_excel': 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=horas_trabajo.xls");
        $filtros = [
            "fecha_desde" => $_GET['fecha_desde'] ?? "",
            "fecha_hasta" => $_GET['fecha_hasta'] ?? "",
            "id_bodega_principal" => $_GET['id_bodega_principal'] ?? "",
            "id_bodega_secundaria" => $_GET['id_bodega_secundaria'] ?? "",
            "id_responsable_obra" => $_GET['id_responsable_obra'] ?? "",
            "id_empleado" => $_GET['id_empleado'] ?? "",
        ];
        $rows = $reporte->listarReporteHorasTrabajo($filtros);
        echo "ID,Fecha,Bodega Principal,Bodega Secundaria,Responsable Obra,Empleado,# Horas,Costo x Hora,Total,Estado\n";
        foreach($rows as $r){
            echo "{$r['id_horas_trabajo_empleado']},{$r['fecha']},{$r['bodega_principal']},{$r['bodega_secundaria']},{$r['responsable']},{$r['empleado']},{$r['numero_horas']},{$r['costo_por_hora']},{$r['total']},{$r['estado']}\n";
        }
        break;

    // Para PDF, puedes implementar aquí el HTML que será convertido por el frontend JS con jsPDF/autotable
    default:
        echo json_encode(["success"=>false,"mensaje"=>"Acción no reconocida"]);
        break;
}
?>