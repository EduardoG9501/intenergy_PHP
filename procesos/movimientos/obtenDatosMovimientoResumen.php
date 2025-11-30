<?php 

	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";

	$obj= new movimientos();


	$idmovimiento=$_POST['idmovimiento'];

	echo json_encode($obj->obtenDatosMovimientoResumen($idmovimiento));

 ?>