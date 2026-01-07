<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/PedidoMateriales.php";
$pedidoMateriales = new PedidoMateriales();
$data = $_POST;
$id = $pedidoMateriales->guardar($data);
echo json_encode([
	'id_pedido_material' => $id,
	'success' => $id ? true : false,
	'mensaje' => $id ? 'Pedido agregado correctamente' : 'Error al agregar pedido'
]);
