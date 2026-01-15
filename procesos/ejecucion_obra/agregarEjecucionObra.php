<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$ejecucionObra = new EjecucionObra();
$data = $_POST;
$id = $ejecucionObra->guardar($data);
echo json_encode([
	'id_ejecucion_obra' => $id,
	'success' => $id ? true : false,
	'mensaje' => $id ? 'Ejecución de obra agregada correctamente' : 'Error al agregar ejecución de obra'
]);
