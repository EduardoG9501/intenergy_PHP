<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $debug = array();
    $debug['paso1'] = 'inicio';
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $debug['paso2'] = 'session_ok';
    
    $conexionPath = __DIR__ . '/../../clases/Conexion.php';
    $debug['paso3_conexion_path'] = $conexionPath;
    $debug['paso4_file_exists'] = file_exists($conexionPath);
    
    if (!file_exists($conexionPath)) {
        echo json_encode(array('error' => 'Conexion.php no existe', 'path' => $conexionPath));
        exit;
    }
    
    require_once $conexionPath;
    $debug['paso5'] = 'conexion_incluida';
    
    $pedidoPath = __DIR__ . '/../../clases/PedidoMateriales.php';
    $debug['paso6_pedido_path'] = $pedidoPath;
    $debug['paso7_file_exists'] = file_exists($pedidoPath);
    
    if (!file_exists($pedidoPath)) {
        echo json_encode(array('error' => 'PedidoMateriales.php no existe', 'path' => $pedidoPath));
        exit;
    }
    
    require_once $pedidoPath;
    $debug['paso8'] = 'pedido_incluido';
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $debug['paso9_action'] = $action;
    
    $pedidoMateriales = new PedidoMateriales();
    $debug['paso10'] = 'instancia_creada';
    
    if ($action == 'articulos') {
        $articulos = $pedidoMateriales->articulos();
        $debug['paso11'] = 'articulos_obtenidos';
        $debug['paso12_cantidad'] = is_array($articulos) ? count($articulos) : 'no_es_array';
        echo json_encode($articulos);
    } else {
        echo json_encode(array('debug' => $debug, 'mensaje' => 'No action'));
    }
    
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()));
} catch (Error $e) {
    echo json_encode(array('error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()));
}
