<?php 
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Categorias.php";
$fecha = date("Y-m-d");
$idusuario = $_SESSION['iduser'];
$categoria = trim($_POST['categoria']);
$subcategoria = $_POST['subcategoria'];

$obj = new categorias();

// Validación de nombre repetido solo en estado=1
if ($obj->existeNombreCategoria($categoria)) {
    echo "2";
    exit;
}

$datos = array(
    $idusuario,
    $categoria, 
    $fecha,
    $subcategoria
);

echo $obj->agregaCategoria($datos);
?>