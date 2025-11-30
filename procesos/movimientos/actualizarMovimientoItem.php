<?php 
	session_start();
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";
	 
    $vidmovimiento=$_POST['id_movimiento_detalle']; 
    $vporciva=$_POST['porciva'];
	$vpordescuento=$_POST['pordescuento'];

	$datos=array(
		            $vidmovimiento, 
					$vporciva,
					$vpordescuento
				); 
	$obj= new movimientos(); 
	echo $obj->actualizarMovimientoItem($datos); 
 ?>