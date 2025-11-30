<?php 

	require_once "../../clases/Conexion.php";
	require_once "../../clases/Empleados.php";

	$obj= new empleados();

	echo json_encode($obj->obtenDatosEmpleado($_POST['idempleado']));

 ?>