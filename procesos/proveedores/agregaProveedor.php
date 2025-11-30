<?php 

session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Proveedores.php";

$obj = new proveedores();

$razonSocial = trim($_POST['razonsocial']);
$nombreComercial = trim($_POST['nombrecomercial']);

// Validación de duplicados, solo permite agregar si retorna 0
$dup = $obj->existeDuplicadoProveedor($razonSocial, $nombreComercial);

if ($dup > 0) {
    echo $dup; // 2=razón social, 3=nombre comercial, 4=ambos
    exit;
}

$datos = array(
    $_POST['tipoIdentificacionSelect'],
    $_POST['noidentificacion'],
    $razonSocial,
    $_POST['direccion'],
    $_POST['telefono'],
    $_POST['correo'],
    $nombreComercial,
    $_POST['tipoIdTipoContribuyenteSelect'],
    $_POST['agenteretencion']
);

if($obj->agregaProveedor($datos)){
	echo 1;
} else {
	echo 0;
}

?>