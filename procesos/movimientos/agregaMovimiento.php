<?php 
	session_start();
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Movimientos.php";
	 
	$idusuario=$_SESSION['iduser'];
	$idbodega=$_POST['bodegaSelect'];
    $idtipomovimiento=$_POST['tipoSelect'];
    $idsubtipomovimiento=$_POST['subtipoSelect'];
	$idproveedor=$_POST['proveedorSelect'];
	$nodocumento=$_POST['nodocumento'];
	$fechadocumento=$_POST['fechadocumento'];
	$idformapago=$_POST['formapagoSelect'];
    $fechapago=$_POST['fechapago'];
    $cantdiapago=$_POST['diapago']; 
	$fecha=date("Y-m-d");	
	$observacion=$_POST['observacion']; 	  	 	

	$datos=array(
		            $idusuario,
		            $idbodega,
		            $idtipomovimiento,
		            $idsubtipomovimiento,
		            $idproveedor,
		            $nodocumento,
		            $fechadocumento,
		            $idformapago,
	                $fechapago,
		            $cantdiapago,
		            $fecha,
		            $observacion 
				);

	$obj= new movimientos();

	$result = $obj->agregaMovimiento($datos);

	// Si retorna 2, ya existe una factura/documento repetida en compras/facturas
	if($result == 2){
		echo 2;
	} else {
		echo $result;
	}
?>