<?php
header('Content-Type: application/json; charset=utf-8');
$r = array();
$r['paso1'] = 'inicio';

require_once "../../clases/Conexion.php";
require_once "../../clases/PedidoMateriales.php";
$r['paso2'] = 'archivos_cargados';

$pm = new PedidoMateriales();
$r['paso3'] = 'instancia_creada';

$articulos = $pm->articulos();
$r['paso4'] = is_array($articulos) ? count($articulos) : 'no_es_array';

echo json_encode($articulos);
