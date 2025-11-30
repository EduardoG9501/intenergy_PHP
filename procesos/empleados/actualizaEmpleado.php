<?php 

session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Empleados.php";

$obj= new empleados();

$datos=array(
	$_POST['idempleadoU'], 
	$_POST['nombreU'],
	$_POST['cedulaU'],
	$_POST['generoSelectU'],
	$_POST['direccionU'],
	$_POST['correoU'],
	$_POST['telefonoU'],
	$_POST['nacimientoU'],
	$_POST['cargoSelectU'] 
);

$result = $obj->actualizaEmpleado($datos);

// Responde 2 si la cédula ya existe, 1 si éxito, 0 si error
echo $result;

?>