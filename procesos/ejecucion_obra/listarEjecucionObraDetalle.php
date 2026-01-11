<?php
// Proceso para listar detalle de Ejecución de Obra
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$ejecucionObra = new EjecucionObra();
$id = $_GET['id_ejecucion_obra'];
echo json_encode($ejecucionObra->listarDetalle($id));
