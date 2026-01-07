<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();
$id = $_POST['id_pedido_material'];
$sql = "DELETE FROM pedido_materiales WHERE id_pedido_material = $id";
$result = mysqli_query($conexion, $sql);
echo json_encode([
	'success' => $result,
	'mensaje' => $result ? 'Pedido eliminado correctamente' : 'Error al eliminar pedido'
]);
