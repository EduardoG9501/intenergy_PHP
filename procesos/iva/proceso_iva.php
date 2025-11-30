<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Iva.php";

$iva = new Iva();
  
// POR ESTO:
if (isset($_GET['action']) && $_GET['action'] === 'listar') {
    echo json_encode($iva->listar());
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'editar') {
    $data = [
        'id_iva' => $_POST['id_iva'],
        'monto' => $_POST['monto'],
        'CODIGO_SRI' => $_POST['CODIGO_SRI'],
        'Codigo_Porc_Iva' => $_POST['Codigo_Porc_Iva']
    ];
    $success = $iva->editar($data);
    echo json_encode([
        'success' => $success,
        'mensaje' => $success ? 'Datos actualizados correctamente' : 'Error al actualizar'
    ]);
    exit;
}
?>