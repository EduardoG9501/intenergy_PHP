<?php
header('Content-Type: application/json; charset=utf-8');
$r = array();
$r['paso1'] = 'inicio';
$r['conexion_exists'] = file_exists(__DIR__ . '/../../clases/Conexion.php');
$r['pedido_exists'] = file_exists(__DIR__ . '/../../clases/PedidoMateriales.php');
if ($r['conexion_exists']) {
    require_once "../../clases/Conexion.php";
    $r['paso2'] = 'conexion_cargada';
    $c = new conectar();
    $r['paso3'] = 'instancia_creada';
    $conn = $c->conexion();
    $r['paso4'] = $conn ? 'conectado' : 'fallo_conexion';
} else {
    $r['paso2'] = 'conexion_no_existe';
}
echo json_encode($r);
