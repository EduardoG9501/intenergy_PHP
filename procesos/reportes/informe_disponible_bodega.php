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

    case 'articulos':
        echo json_encode($reporte->listarArticulos());
        break;

    case 'listar_reporte':
        $filtros = [
            "id_bodega" => $_POST['id_bodega'] ?? "",
            "id_producto" => $_POST['id_producto'] ?? "",
        ];
        echo json_encode($reporte->listarDisponibilidadBodega($filtros));
        break;

    case 'exportar_excel': 
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=disponibilidad_bodega.xls");
        $filtros = [
            "id_bodega" => $_GET['id_bodega'] ?? "",
            "id_producto" => $_GET['id_producto'] ?? "",
        ];
        $rows = $reporte->listarDisponibilidadBodega($filtros);
        echo "ID Artículo,Nombre Artículo,ID Bodega,Nombre Bodega,Cantidad Disponible\n";
        foreach($rows as $r){
            echo "{$r['id_producto']},{$r['nombre_articulo']},{$r['id_bodega']},{$r['nombreBodega']},{$r['cantidad']}\n";
        }
        break;

    default:
        echo json_encode(["success"=>false,"mensaje"=>"Acción no reconocida"]);
        break;
}
?>