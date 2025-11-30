<?php 

session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Empleados.php";

$obj= new empleados();

$datos=array( 
	$_POST['nombre'],
	$_POST['cedula'],
	$_POST['generoSelect'],
	$_POST['direccion'],
	$_POST['correo'],
	$_POST['telefono'], 
	$_POST['nacimiento'],
	$_POST['cargoSelect'] 
);

$result = $obj->agregaEmpleado($datos);

// Responde 2 si la cédula ya existe, 1 si éxito, 0 si error
echo $result;

?>