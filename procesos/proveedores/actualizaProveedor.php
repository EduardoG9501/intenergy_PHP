<?php 

session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Proveedores.php";

$obj = new proveedores();

$idProveedor = intval($_POST['idproveedorU']);
$razonSocial = trim($_POST['razonsocialU']);
$nombreComercial = trim($_POST['nombrecomercialU']);

// Validación de duplicados, solo permite actualizar si retorna 0
$dup = $obj->existeDuplicadoProveedor($razonSocial, $nombreComercial, $idProveedor);

if ($dup > 0) {
    echo $dup; // 2=razón social, 3=nombre comercial, 4=ambos
    exit;
}

$datos = array(
    $idProveedor,
    $_POST['tipoIdentificacionSelectU'],
    $_POST['noidentificacionU'],
    $razonSocial,
    $_POST['direccionU'],
    $_POST['telefonoU'],
    $_POST['correoU'],
    $nombreComercial,
    $_POST['tipoIdTipoContribuyenteSelectU'],
    $_POST['agenteretencionU']
);

if($obj->actualizaProveedor($datos)){
	echo 1;
} else {
	echo 0;
}

?>