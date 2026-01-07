<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/PedidoMateriales.php";
$pedidoMateriales = new PedidoMateriales();
echo json_encode($pedidoMateriales->listar());
