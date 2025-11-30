<?php 
	require_once "../../clases/Conexion.php";
	require_once "../../clases/CargoEmpleos.php";
	$idcargo=$_POST['idcargo'];

	$obj= new cargosempleos();
	echo $obj->eliminaCargo($idcargo);

 ?>