<?php
// Proceso para eliminar detalle de Ejecución de Obra
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$ejecucionObra = new EjecucionObra();
$id = $_POST['id_ejecucion_obra_detalle_materiales_utilizar'];
$result = $ejecucionObra->eliminarDetalle($id);
echo json_encode($result);
