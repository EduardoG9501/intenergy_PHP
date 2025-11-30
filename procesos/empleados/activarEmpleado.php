<?php 

	require_once "../../clases/Conexion.php";
	require_once "../../clases/Empleados.php";

	$obj= new empleados();

	
	echo $obj->activarEmpleado($_POST['idempleado']);
 ?>