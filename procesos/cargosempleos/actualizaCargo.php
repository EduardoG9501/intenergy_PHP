<?php 
require_once "../../clases/Conexion.php";
require_once "../../clases/CargoEmpleos.php";

$datos = array(
    $_POST['idcargo'],
    trim($_POST['cargoU']) // Limpiar espacios
);

$obj = new cargosempleos();
echo $obj->actualizaCargo($datos); // Retorna 2 si ya existe, 1 si ok, 0 si error
?>