<?php 
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/CargoEmpleos.php";
$fecha = date("Y-m-d");
$idusuario = $_SESSION['iduser'];
$cargo = trim($_POST['cargo']); // Limpiar espacios

$datos = array(
    $idusuario,
    $cargo,
    $fecha
);

$obj = new cargosempleos();
echo $obj->agregaCargo($datos); // Retorna 2 si ya existe, 1 si ok, 0 si error
?>