<?php 
	session_start();
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";
	 
    $vidmovimiento=$_POST['id_movimiento']; 
    $vporciva=$_POST['porc_iva'];
	$vporcdescuento=$_POST['porc_descuento'];

	$datos=array(
		            $vidmovimiento, 
		            $vporciva,
		            $vporcdescuento
				); 
	$obj= new movimientos(); 
	echo $obj->actualizarMovimientoMontos($datos); 
 ?>