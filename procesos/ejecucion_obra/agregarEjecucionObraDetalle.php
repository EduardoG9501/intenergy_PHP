<?php
// Proceso para agregar detalle de Ejecución de Obra
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$ejecucionObra = new EjecucionObra();
$data = $_POST;
$result = $ejecucionObra->guardarDetalle($data);
echo json_encode($result);
