<?php 
 
	require_once "../../clases/Conexion.php";
    require_once "../../clases/Movimientos.php";

	$obj= new movimientos();


	$filtro=$_POST['filtro'];

	echo json_encode($obj->filtrarSubTipoMovimiento($filtro));

 ?>