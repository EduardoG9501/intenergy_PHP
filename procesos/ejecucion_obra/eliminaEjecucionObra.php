<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();
$id = $_POST['id_ejecucion_obra'];
$sql = "DELETE FROM ejecucion_obra WHERE id_ejecucion_obra = $id";
$result = mysqli_query($conexion, $sql);
echo json_encode([
	'success' => $result,
	'mensaje' => $result ? 'Ejecución de obra eliminada correctamente' : 'Error al eliminar ejecución de obra'
]);
