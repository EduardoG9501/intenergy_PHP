<?php 

	require_once "../../clases/Conexion.php";
	require_once "../../clases/Articulos.php";

	$obj= new articulos();


	$filtro=$_POST['idarticulo'];

	echo json_encode($obj->obtenerprecioarticulo($filtro));

 ?>