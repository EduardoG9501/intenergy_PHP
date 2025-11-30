<?php 
	session_start();
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";
	 
    $vidmovimiento=$_POST['id_movimiento'];
	$vidarticulo=$_POST['id_articulo'];
    $vidbodega_lugar=$_POST['id_bodega_lugar'];
    $vprecio=$_POST['precio'];
	$vcantidad=$_POST['cantidad'];
	$vlote=$_POST['lote']; 	  
	$subtotal=$vprecio*$vcantidad; 	 
	$total=$vprecio*$vcantidad; 	 

	$datos=array(
		            $vidmovimiento,
		            $vidarticulo,
		            $vidbodega_lugar,
		            $vprecio,
		            $vcantidad,
		            $vlote,
					$subtotal,
					$total
				); 
	$obj= new movimientos(); 
	echo $obj->agregaMovimientoDetalle($datos); 
 ?>