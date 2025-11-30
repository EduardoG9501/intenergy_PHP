<?php 
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Bodegas.php";
	$id=$_POST['idbodega'];

	$obj= new bodegas();
	echo $obj->eliminaBodega($id);

 ?>