<?php 

	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";

	$obj= new movimientos();


	$iddetalle=$_POST['iddetalle'];
	$idmovimiento=$_POST['idmovimiento'];

	echo json_encode($obj->obtenDatosMovimientoResumenItem($iddetalle,$idmovimiento));

 ?>