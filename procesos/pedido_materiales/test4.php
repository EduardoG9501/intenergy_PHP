<?php
header('Content-Type: application/json; charset=utf-8');
$r = array();
$r['paso1'] = 'inicio';

require_once "../../clases/Conexion.php";
$r['paso2'] = 'conexion_ok';

// Intentar cargar PedidoMateriales.php y capturar errores
try {
    $file = __DIR__ . '/../../clases/PedidoMateriales.php';
    $r['file_exists'] = file_exists($file);
    $r['file_size'] = filesize($file);
    
    // Leer el archivo para verificar contenido
    $content = file_get_contents($file);
    $r['file_length'] = strlen($content);
    $r['first_50_chars'] = substr($content, 0, 50);
    
    // Intentar evaluar el archivo
    require_once $file;
    $r['paso3'] = 'pedido_cargado';
    
    $pm = new PedidoMateriales();
    $r['paso4'] = 'instancia_ok';
    
    $articulos = $pm->articulos();
    $r['paso5'] = is_array($articulos) ? count($articulos) : gettype($articulos);
    
    echo json_encode($articulos);
    
} catch (Exception $e) {
    $r['error'] = $e->getMessage();
    $r['error_file'] = $e->getFile();
    $r['error_line'] = $e->getLine();
    echo json_encode($r);
} catch (Error $e) {
    $r['error'] = $e->getMessage();
    $r['error_file'] = $e->getFile();
    $r['error_line'] = $e->getLine();
    echo json_encode($r);
}
