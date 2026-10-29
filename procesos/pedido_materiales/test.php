<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    $result = [];
    $result['paso1_start'] = 'ok';

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $result['paso2_session'] = 'ok';

    require_once "../../clases/Conexion.php";
    $result['paso3_conexion'] = 'ok';

    $c = new conectar();
    $conexion = $c->conexion();
    $result['paso4_bd'] = $conexion ? 'conectado' : 'fallo';

    $res = @mysqli_query($conexion, "SELECT COUNT(*) as total FROM articulos WHERE estado = 1");
    $row = $res ? mysqli_fetch_assoc($res) : null;
    $result['paso5_articulos'] = $row ? intval($row['total']) : 0;

    require_once "../../clases/PedidoMateriales.php";
    $result['paso6_pedido'] = 'ok';

    $pm = new PedidoMateriales();
    $articulos = $pm->articulos();
    $result['paso7_lista'] = is_array($articulos) ? count($articulos) : $articulos;

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'archivo' => $e->getFile(), 'linea' => $e->getLine()]);
} catch (Error $e) {
    echo json_encode(['error' => $e->getMessage(), 'archivo' => $e->getFile(), 'linea' => $e->getLine()]);
}
