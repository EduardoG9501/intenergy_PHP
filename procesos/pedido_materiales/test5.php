<?php
header('Content-Type: application/json; charset=utf-8');
$r = array();
$r['paso1'] = 'inicio';

require_once "../../clases/Conexion.php";
$r['paso2'] = 'conexion_ok';

require_once "../../clases/PedidoMateriales.php";
$r['paso3'] = 'pedido_ok';

try {
    $pm = new PedidoMateriales();
    $r['paso4'] = 'instancia_ok';
} catch (Exception $e) {
    $r['error_instancia'] = $e->getMessage();
}

echo json_encode($r);
