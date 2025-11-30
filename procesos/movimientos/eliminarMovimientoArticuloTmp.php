<?php 
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";
	$id=$_POST['idmovimiento'];

	$obj= new movimientos();
	echo $obj->eliminarMovimientoArticuloTmp($id);

 ?>