<?php 

	require_once "../../clases/Conexion.php";
	require_once "../../clases/Articulos.php";

	$obj= new articulos();


	$filtro=$_POST['filtro'];

	echo json_encode($obj->filtrarArticulobynombre($filtro));

 ?>