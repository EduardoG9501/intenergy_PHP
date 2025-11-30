<?php 
/*Se asginan las fecha de zona de ecuador o ni*/
$PAIS = "ec"; // "ec" para Ecuador, "ni" para Nicaragua

if ($PAIS == "ec") {
    date_default_timezone_set('America/Guayaquil');
} else if ($PAIS == "ni") {
    date_default_timezone_set('America/Managua');
}  
class movimientos{

	// VALIDACION NUEVA: FACTURA ÚNICA GLOBAL PARA ENTRADA/COMPRAS
	private function existeFacturaGlobal($no_documento, $id_tipo_movimiento, $id_sub_tipo_movimiento, $idproveedor, $excludeId = null) {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT id_movimiento FROM movimientos 
				WHERE no_documento = '$no_documento' 
				  AND id_tipo_movimiento = '$id_tipo_movimiento' 
				  AND id_sub_tipo_movimiento = '$id_sub_tipo_movimiento'
				  AND id_proveedor = '$idproveedor'
				  AND estado = 1";
		// Solo agregar exclusión si $excludeId es mayor a cero y no vacío
		if (!empty($excludeId) && intval($excludeId) > 0) {
			$sql .= " AND id_movimiento != '$excludeId'";
		}
		$res = mysqli_query($conexion, $sql);
		return mysqli_num_rows($res) > 0;
	}

	public function agregaMovimiento($datos){
		$c= new conectar();
		$conexion=$c->conexion();

		// VALIDACIÓN SOLO para ENTRADA/COMPRAS (tipo=1, subtipo=1)
		if ($datos[2] == "1" && $datos[3] == "1") { // 1=ENTRADAS, 1=COMPRAS
			if ($this->existeFacturaGlobal($datos[5], "1", "1",$datos[4])) {
				return 2; // Ya existe documento para ese tipo/subtipo
			}
		}

		$sql="INSERT into movimientos(id_usuario,
				                    id_bodega,
				                    id_tipo_movimiento,
				                    id_sub_tipo_movimiento,
				                    id_proveedor,
				                    no_documento,
				                    fechaDocumento, 
				                    id_forma_pago,
				                    fechaPago,
                                    cant_dia_pago,		
                                    fechaCaptura, 			
                                    observacion)
						values ('$datos[0]',
								'$datos[1]',
								'$datos[2]',
								'$datos[3]',
								'$datos[4]',
								'$datos[5]',
								'$datos[6]',
								'$datos[7]',
								'$datos[8]',
								'$datos[9]',
								'$datos[10]',
								'$datos[11]')";  
		   	$result1 = mysqli_query($conexion,$sql);
			$idmovimiento = 0;
			/*Retornamos id del movimiento generado*/
			if ($result1 > 0)
			{
               $sql2="SELECT 
			          max(id_movimiento) id_movimiento
			   from movimientos mov";
		  
		       $result2   = mysqli_query($conexion,$sql2); 
                if(mysqli_num_rows( $result2) > 0){   
                   while($row =  $result2->fetch_row()) { 
				      $idmovimiento  = $row[0];
                     }	 
		       } 
			}
			
		 return $idmovimiento;

		} 

		public function eliminaMovimiento($idmovimiento){ 
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE movimientos set estado=0
				  where id_movimiento='$idmovimiento'";
			return mysqli_query($conexion,$sql); 
		} 
		public function activarMovimiento($idmovimiento){ 
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE movimientos set estado=1
				  where id_movimiento='$idmovimiento'";
			return mysqli_query($conexion,$sql); 
		} 
		public function obtenDatosMovimiento($idmovimiento){
			$c=new conectar();
			$conexion=$c->conexion();

			$sql="SELECT mov.id_movimiento, 
						mov.id_bodega, 
						mov.id_tipo_movimiento,
						mov.id_sub_tipo_movimiento,
						mov.id_proveedor,
						mov.no_documento,
						mov.fechaDocumento,
						mov.id_forma_pago,
						mov.fechaPago,
						mov.cant_dia_pago,
						mov.observacion
				from movimientos mov 
				where id_movimiento='$idmovimiento'";
			$result=mysqli_query($conexion,$sql);

			$ver=mysqli_fetch_row($result);

			$datos=array(
					"id_movimiento" => $ver[0],
					"id_bodega" => $ver[1],
					"id_tipo_movimiento" => $ver[2],
					"id_sub_tipo_movimiento" => $ver[3],
					"id_proveedor" => $ver[4],
					"no_documento" => $ver[5],
					"fechaDocumento" => $ver[6],
					"id_forma_pago" => $ver[7],
					"fechaPago" => $ver[8],
					"cant_dia_pago" => $ver[9],
					"observacion" => $ver[10]
						);

			return $datos;
		} 
		public function actualizaMovimiento($datos){
			$c=new conectar();
			$conexion=$c->conexion();

			// VALIDACIÓN SOLO para ENTRADA/COMPRAS (tipo=1, subtipo=1) en edición
			if ($datos[2] == "1" && $datos[3] == "1") { // 1=ENTRADA, 1=COMPRAS
				if ($this->existeFacturaGlobal($datos[5], "1", "1",$datos[4], $datos[0])) {
					return 2; // Ya existe documento para ese tipo/subtipo
				}
			}
            $sql="UPDATE movimientos set id_bodega='$datos[1]', 
										id_tipo_movimiento='$datos[2]',
										id_sub_tipo_movimiento='$datos[3]',
										id_proveedor='$datos[4]',
										no_documento='$datos[5]',
										fechaDocumento='$datos[6]',
										id_forma_pago='$datos[7]',
										fechaPago='$datos[8]',
										cant_dia_pago='$datos[9]',
										observacion ='$datos[10]'
				where id_movimiento='$datos[0]'";
				return mysqli_query($conexion,$sql);  
		} 
		public function agregaMovimientoDetalle($datos){
			$c= new conectar();
			$conexion=$c->conexion();

			/*0. PARA VALIDAR SI ARTICULO AGREGAR TIENE DISPONBILIDAD MAYOR A CERO*/ 
			$sqlTipo = "SELECT id_tipo_movimiento FROM movimientos WHERE id_movimiento = '$datos[0]'";
            $resTipo = mysqli_query($conexion, $sqlTipo);
            $rowTipo = mysqli_fetch_assoc($resTipo);
            $tipoMovimiento = $rowTipo ? $rowTipo['id_tipo_movimiento'] : null;

            // Solo validar si es SALIDA
            if ($tipoMovimiento == 2) {
               $stockDisponible = $this->obtenerStockDisponible($datos[1], $datos[2]);
               if ($datos[4] > $stockDisponible) {
                 return "2";
               }
           }

			$sqlMovimiento="SELECT  
					    mov.desc_porc,
					    mov.iva_porc
			from movimientos mov 
			where id_movimiento='$datos[0]'";

		   $resultMov   = mysqli_query($conexion,$sqlMovimiento); 

           /*1. Asignamos los porcentajes de los descuento e iva*/ 
           if(mysqli_num_rows($resultMov) > 0){   

             while($row = $resultMov->fetch_row()) {  
				   $PorcIva  = 0;///$row[0]/(100);
                   $PorcDesc = 0;///$row[1]/(100);
              }	 
           } 

          /*2. Se recalculan los valores del item del producto*/
		  $MontoTotalBruto         = ($datos[4] * $datos[3]);
		  $MontoTotalConDescuento  = round($MontoTotalBruto * $PorcDesc,4);
		  $MontoTotalConIva        = round((($MontoTotalBruto - $MontoTotalConDescuento) * $PorcIva),4);
          $MontoTotal              = round(($MontoTotalBruto - $MontoTotalConDescuento) + $MontoTotalConIva,4);
		  
		  /*3. Se agrega el nuevo ITEM al movimiento*/
		  $sql="INSERT into movimientos_detalle_tmp(id_movimiento,
				                    id_articulo,
				                    id_bodega_lugar,
				                    precio,
				                    cantidad,
				                    lote,
									subtotal,
									decuento,
									iva,
									total )
						values ('$datos[0]',
								'$datos[1]',
								'$datos[2]',
								'$datos[3]',
								'$datos[4]',
								'$datos[5]',
								'$datos[6]',
								'$MontoTotalConDescuento',
								'$MontoTotalConIva',
								'$MontoTotal')";

			 mysqli_query($conexion,$sql);

			 /*4. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			 $sqlDetCab="SELECT 
			           ROUND(sum(mov.subtotal),4) subtotal,
					   ROUND(sum(mov.decuento),4) decuento,
					   ROUND(sum(mov.iva),4) iva,
					   ROUND(sum(mov.total),4) total
				from movimientos_detalle_tmp mov 
				where id_movimiento='$datos[0]'";

		        $resultDet   = mysqli_query($conexion,$sqlDetCab);
				while($rowDet = $resultDet->fetch_row()) {
                    $vsubtotal     = $rowDet[0];
				    $vdecuento     = $rowDet[1];
				    $viva          = $rowDet[2]; 
			        $vtotal        = $rowDet[3]; 
			    }	
				
			 /*5. Actualizamos la cabezera dle movimiento*/  
                $sqlUpdateCabezera="UPDATE movimientos set 
				      subtotal_mto='$vsubtotal',
					  desc_mto='$vdecuento',
					  iva_mto='$viva',
					  total_mto='$vtotal'
				where id_movimiento='$datos[0]'"; 
			 return mysqli_query($conexion,$sqlUpdateCabezera); 
		}
		public function actualizarMovimientoMontos($datos){
			$c=new conectar();
			$conexion=$c->conexion(); 

			   /*1. Asignamos los porcentajes de los descuento e iva*/ 
			   $PorcIva  = $datos[1]/(100);
			   $PorcDesc = $datos[2]/(100);
 
			   $sqlDet="SELECT 
				        id_movimientos_detalle_tmp, 
					    precio,
					    cantidad
				from movimientos_detalle_tmp mov 
				where id_movimiento='$datos[0]'";
				$result   = mysqli_query($conexion,$sqlDet);
				
				/*2. Recorremos el detalle del movimientos para actualizar los totales*/ 
				while($row = $result->fetch_row()) {

				  /*2.1 Se asignan los valores del item del producto*/
                  $idMovimientoDetalle     = $row[0];
				  $precio                  = $row[1];
				  $cantidadTotal           = $row[2]; 

                  /*2.2 Se recalculan los valores del item del producto*/
				  $MontoTotalBruto         = ($cantidadTotal * $precio);
				  $MontoTotalConDescuento  = round($MontoTotalBruto * $PorcDesc,4);
				  $MontoTotalConIva        = round((($MontoTotalBruto - $MontoTotalConDescuento) * $PorcIva),4);
                  $MontoTotal              = round(($MontoTotalBruto - $MontoTotalConDescuento) + $MontoTotalConIva,4);

				  /*2.3 Actualizamos el descuento/iva y totales el detalle*/ 
				  $sqlUpdateDet ="UPDATE movimientos_detalle_tmp 
				  set decuento = '$MontoTotalConDescuento',
					  iva      = '$MontoTotalConIva',
					  total    = '$MontoTotal',
					  iva_porc    = '$datos[1]',
					  desc_porc    = '$datos[2]'
				  where id_movimientos_detalle_tmp = '$idMovimientoDetalle'";
				  mysqli_query($conexion,$sqlUpdateDet); 
              }	 

                /*3. Obtener montos del detalle para acuualizarlos en la cabezera*/
				$sqlDetCab="SELECT 
			           ROUND(sum(mov.subtotal),4) subtotal,
					   ROUND(sum(mov.decuento),4) decuento,
					   ROUND(sum(mov.iva),4) iva,
					   ROUND(sum(mov.total),4) total
				from movimientos_detalle_tmp mov 
				where id_movimiento='$datos[0]'";

		        $resultDet   = mysqli_query($conexion,$sqlDetCab);
				while($rowDet = $resultDet->fetch_row()) {
                    $vsubtotal     = $rowDet[0];
				    $vdecuento     = $rowDet[1];
				    $viva          = $rowDet[2]; 
			        $vtotal        = $rowDet[3]; 
			    }	
				
				/*4. Actualizamos la cabezera dle movimiento*/  
                $sqlUpdateCabezera="UPDATE movimientos set iva_porc='$datos[1]', 
										desc_porc='$datos[2]',
										subtotal_mto='$vsubtotal',
										desc_mto='$vdecuento',
										iva_mto='$viva',
										total_mto='$vtotal'
				where id_movimiento='$datos[0]'";
				//echo $sqlUpdateCabezera;
				return mysqli_query($conexion,$sqlUpdateCabezera); 
		}
		public function actualizarMovimientoDetalle($datos){
			$c= new conectar();
			$conexion=$c->conexion();
     
			 /*1. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			 $sqlDetMovimiento="SELECT 
			            id_movimiento
				from movimientos_detalle_tmp mov 
			    where id_movimientos_detalle_tmp='$datos[0]'";  

             $resultDetMov   = mysqli_query($conexion,$sqlDetMovimiento);
				while($rowDetMov = $resultDetMov->fetch_row()) {
                    $idMovimiento  = $rowDetMov[0]; 
			  }	
                
             $sqlMovimiento="SELECT   
					    mov.iva_porc,
					    mov.desc_porc,
						id_articulo
			 from movimientos_detalle_tmp mov  
			 where id_movimientos_detalle_tmp='$datos[0]'";  
 
		     $resultMov   = mysqli_query($conexion,$sqlMovimiento); 

             /*2. Asignamos los porcentajes de los descuento e iva*/ 
             if(mysqli_num_rows($resultMov) > 0){   

              while($row = $resultMov->fetch_row()) {  
				   $PorcIva  = $row[0]/(100);
                   $PorcDesc = $row[1]/(100);
				   $PorcIvaInt  = $row[0];
				   $PorcDescInt = $row[1]; 
				   $vidarticulo = $row[2]; 
                 }	 
              } 
               
			  /*2.1 PARA VALIDAR SI DISPONBILIDAD DE ARTICULO A AGREGAR ES MAYOR A CERO*/
			  $sqlTipo = "SELECT id_tipo_movimiento FROM movimientos WHERE id_movimiento = '$idMovimiento'";
              $resTipo = mysqli_query($conexion, $sqlTipo);
              $rowTipo = mysqli_fetch_assoc($resTipo);
              $tipoMovimiento = $rowTipo ? $rowTipo['id_tipo_movimiento'] : null;

              // Solo validar si es SALIDA
              if ($tipoMovimiento == 2) {
                 $stockDisponible = $this->obtenerStockDisponible($vidarticulo, $datos[1]);
                if ($datos[3] > $stockDisponible) {
                    return "2";
                 }
             }

              /*3. Se recalculan los valores del item del producto*/
		      $MontoTotalBruto         = ($datos[3] * $datos[2]);
		      $MontoTotalConDescuento  = round($MontoTotalBruto * $PorcDesc,4);
		      $MontoTotalConIva        = round((($MontoTotalBruto - $MontoTotalConDescuento) * $PorcIva),4);
              $MontoTotal              = round(($MontoTotalBruto - $MontoTotalConDescuento) + $MontoTotalConIva,4);
	
              /*4. Actualizamos el detalle del movimiento*/ 
			  $sql="UPDATE movimientos_detalle_tmp 
				          SET id_bodega_lugar ='$datos[1]',
				              precio ='$datos[2]',
				              cantidad ='$datos[3]',
				              lote ='$datos[4]',
							  subtotal ='$datos[5]',
							  total  ='$MontoTotal',
							  decuento ='$MontoTotalConDescuento',
							  iva ='$MontoTotalConIva',
							  iva_porc ='$PorcIvaInt',
							  desc_porc ='$PorcDescInt'
						where id_movimientos_detalle_tmp='$datos[0]'"; 
			   mysqli_query($conexion,$sql);
  
               /*5. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			    $sqlDetCab="SELECT 
			           ROUND(sum(mov.subtotal),4) subtotal,
					   ROUND(sum(mov.decuento),4) decuento,
					   ROUND(sum(mov.iva),4) iva,
					   ROUND(sum(mov.total),4) total
				from movimientos_detalle_tmp mov 
				where id_movimiento='$idMovimiento'";

		        $resultDet   = mysqli_query($conexion,$sqlDetCab);
				while($rowDet = $resultDet->fetch_row()) {
                    $vsubtotal     = $rowDet[0];
				    $vdecuento     = $rowDet[1];
				    $viva          = $rowDet[2]; 
			        $vtotal        = $rowDet[3]; 
			    }	
				
			   /*6. Actualizamos la cabezera dle movimiento*/  
                $sqlUpdateCabezera="UPDATE movimientos set 
						subtotal_mto='$vsubtotal',
						desc_mto='$vdecuento',
					    iva_mto='$viva',
						total_mto='$vtotal'
				where id_movimiento='$idMovimiento'"; 
			 return mysqli_query($conexion,$sqlUpdateCabezera);  

		}
		public function actualizarMovimientoItem($datos){
			$c= new conectar();
			$conexion=$c->conexion();
 
			 /*1. Asignamos los porcentajes de los descuento e iva*/ 
			   $PorcIva  = $datos[1]/(100);
			   $PorcDesc = $datos[2]/(100);

			 /*2. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			 $sqlDetMovimiento="SELECT 
			            id_movimiento,
						cantidad,
						precio
				from movimientos_detalle_tmp mov 
			    where id_movimientos_detalle_tmp='$datos[0]'";  
             $resultDetMov   = mysqli_query($conexion,$sqlDetMovimiento);
				while($rowDetMov = $resultDetMov->fetch_row()) {
                    $idMovimiento  = $rowDetMov[0]; 
				    $cantidadItem  = $rowDetMov[1]; 
				    $precioItem    = $rowDetMov[2]; 
			  }	 
              
			  /*3. Se recalculan los valores del item del producto*/
		      $MontoTotalBruto         = ($cantidadItem * $precioItem);
		      $MontoTotalConDescuento  = round($MontoTotalBruto * $PorcDesc,4);
		      $MontoTotalConIva        = round((($MontoTotalBruto - $MontoTotalConDescuento) * $PorcIva),4);
              $MontoTotal              = round(($MontoTotalBruto - $MontoTotalConDescuento) + $MontoTotalConIva,4);
	
              /*4. Actualizamos el detalle del movimiento*/ 
			  $sql="UPDATE movimientos_detalle_tmp 
				          SET subtotal ='$MontoTotalBruto',
							  total  ='$MontoTotal ',
							  decuento ='$MontoTotalConDescuento',
							  iva ='$MontoTotalConIva',
							  iva_porc ='$datos[1]',
							  desc_porc ='$datos[2]'
						where id_movimientos_detalle_tmp='$datos[0]'"; 
			   mysqli_query($conexion,$sql);
  
               /*5. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			    $sqlDetCab="SELECT 
			           ROUND(sum(mov.subtotal),4) subtotal,
					   ROUND(sum(mov.decuento),4) decuento,
					   ROUND(sum(mov.iva),4) iva,
					   ROUND(sum(mov.total),4) total
				from movimientos_detalle_tmp mov 
				where id_movimiento='$idMovimiento'";

		        $resultDet   = mysqli_query($conexion,$sqlDetCab);
				while($rowDet = $resultDet->fetch_row()) {
                    $vsubtotal     = $rowDet[0];
				    $vdecuento     = $rowDet[1];
				    $viva          = $rowDet[2]; 
			        $vtotal        = $rowDet[3]; 
			    }	
				
			   /*6. Actualizamos la cabezera dle movimiento*/  
                $sqlUpdateCabezera="UPDATE movimientos set 
						subtotal_mto='$vsubtotal',
						desc_mto='$vdecuento',
					    iva_mto='$viva',
						total_mto='$vtotal'
				where id_movimiento='$idMovimiento'"; 
			 return mysqli_query($conexion,$sqlUpdateCabezera);  

		}
		public function eliminarMovimientoArticuloTmp($idmovimiento){ 
			$c= new conectar();
			$conexion=$c->conexion(); 

           /*1. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			 $sqlDetMovimiento="SELECT 
			            id_movimiento
				from movimientos_detalle_tmp mov 
			    where id_movimientos_detalle_tmp='$idmovimiento'"; 

             $resultDetMov   = mysqli_query($conexion,$sqlDetMovimiento);
				while($rowDetMov = $resultDetMov->fetch_row()) {
                    $idMovimientoCabezera  = $rowDetMov[0]; 
			  }	

		   /*2. Eliminamos el detalle del producto*/
			$sql="DELETE FROM movimientos_detalle_tmp where id_movimientos_detalle_tmp='$idmovimiento'"; 
			mysqli_query($conexion,$sql); 
 
            /*3. Obtener montos del detalle para acuualizarlos en la cabezera*/ 
			 $sqlDetCab="SELECT 
			           ROUND(sum(mov.subtotal),4) subtotal,
					   ROUND(sum(mov.decuento),4) decuento,
					   ROUND(sum(mov.iva),4) iva,
					   ROUND(sum(mov.total),4) total
				from movimientos_detalle_tmp mov 
				where id_movimiento='$idMovimientoCabezera'";
             
		        $resultDet   = mysqli_query($conexion,$sqlDetCab);
				while($rowDet = $resultDet->fetch_row()) {
                    $vsubtotal     = $rowDet[0];
				    $vdecuento     = $rowDet[1];
				    $viva          = $rowDet[2]; 
			        $vtotal        = $rowDet[3]; 
			    }	
				
			   /*. Actualizamos la cabezera dle movimiento*/  
                $sqlUpdateCabezera="UPDATE movimientos set 
						subtotal_mto='$vsubtotal',
						desc_mto='$vdecuento',
					    iva_mto='$viva',
						total_mto='$vtotal'
				where id_movimiento='$idMovimientoCabezera'"; 
			 return mysqli_query($conexion,$sqlUpdateCabezera);  

		} 
		public function obtenDatosMovimientoResumen($idmovimiento){   
			$c=new conectar();
			$conexion=$c->conexion();

			$sql="SELECT 
			            0 cantidad,
					    ROUND(mov.subtotal_mto,2)subtotal_mto,
					    ROUND(mov.total_mto,2)total_mto,
					    ROUND(mov.desc_mto,2)desc_mto,
					    ROUND(mov.iva_mto,2)iva_mto,
					    mov.desc_porc,
					    mov.iva_porc
				from movimientos mov 
				where id_movimiento='$idmovimiento'";
		  
		   $result   = mysqli_query($conexion,$sql);
		   $jsonData = array();

           if(mysqli_num_rows($result) > 0){  
 
           while($row = $result->fetch_row()) {
                  $rows[]=array(
					            "cantidad" => $row[0], 
					            "subtotal" => $row[1],
							    "total" => $row[2],
								"descuento" => $row[3],
								"iva" => $row[4],
								"descuentoPorc" => $row[5],
								"ivaPorc" => $row[6]
						);
              }	 
              $json = json_encode($rows);    
              return $json ;    
		}  
	    } 
		public function obtenDatosMovimientoResumenItem($iddetalle, $idmovimiento){   
			$c=new conectar();
			$conexion=$c->conexion();

			$sql="SELECT 
			            round(mov.cantidad,2) cantidad,
					    round(mov.subtotal,2) subtotal,
					    round(mov.total,2) total,
					    round(mov.decuento,2) decuento,
					    round(mov.iva,2) iva,
					    round(mov.desc_porc,2) desc_porc,
					    round(mov.iva_porc,2) iva_porc
				from movimientos_detalle_tmp mov 
				where id_movimientos_detalle_tmp='$iddetalle' and id_movimiento ='$idmovimiento'";
		  
		   $result   = mysqli_query($conexion,$sql);
		   $jsonData = array();

           if(mysqli_num_rows($result) > 0){  
 
           while($row = $result->fetch_row()) {
                  $rows[]=array(
					            "cantidad" => $row[0], 
					            "subtotal" => $row[1],
							    "total" => $row[2],
								"descuento" => $row[3],
								"iva" => $row[4],
								"descuentoPorc" => $row[5],
								"ivaPorc" => $row[6]
						);
              }	 
              $json = json_encode($rows);    
              return $json ;    
		}  
	    } 
        public function guardarDefinitivo($idmovimiento){
			$c= new conectar();
			$conexion=$c->conexion(); 
			$fecha=date('Y-m-d');

			/*1. Se inserta detalle de items*/
			$sql="INSERT into movimientos_detalle(id_movimiento,
				                    id_articulo,
				                    id_bodega_lugar,
				                    precio,
				                    cantidad,
				                    lote,
									subtotal,
									decuento,
									iva, 
									total,
									iva_porc,
									desc_porc) 
				  SELECT id_movimiento,
				                    id_articulo,
				                    id_bodega_lugar,
				                    precio,
				                    cantidad,
				                    lote,
									subtotal,
									decuento,
									iva, 
									total,
									iva_porc,
									desc_porc
				  FROM movimientos_detalle_tmp where id_movimiento='$idmovimiento'";
 
			$result = mysqli_query($conexion,$sql); 
			$resultado = 0;
			 
			/*Validamos que el insert al detalle del producto se hizo con existo*/
			if ($result)
			{
				/*2. Actualizamos los sotosk de los productos*/ 
			    $sql2="INSERT into stock_productos(
			                                    id_movimiento,
				                                id_tipo_movimiento ,
				                                id_sub_tipo_movimiento , 
			                                	no_documento,
			                                	id_bodega_principal,
				                                id_bodega_secundaria, 
				                                tipo_movimiento ,
				                                sub_tipo_movimiento ,
                                                id_movimiento_detalle, 
                                                id_producto,
                                                producto,
                                                lote,
				                                precio,
				                                cantidad,
                                                subtotal,
                                                decuento,
                                                iva,
                                                total, 
                                                stock,
				                                id_stock_credito,
												id_usuario,
				                                fecha_captura
			                                 ) 
				        SELECT  det.id_movimiento,
                                mov.id_tipo_movimiento,
                                mov.id_sub_tipo_movimiento,
                                mov.no_documento,
                                mov.id_bodega idBodegaPrincipal,
								det.id_bodega_lugar IdBodegaSecundaria,
                                tm.tipo TipoMovimiento,
                                stm.sub_tipo SubTipoMovimiento,
                                det.id_movimiento_detalle,
	                            art.id_producto,
	                            art.nombre,
				                det.lote,
	                            det.precio,
				                det.cantidad,
				                det.subtotal,
							    det.decuento,
								det.iva,
				                det.total, 
								det.cantidad stock,
								0 id_stock_credito,
                               mov.id_usuario,
							   '$fecha' fecha_captura
		               from movimientos_detalle as det
                       inner join movimientos mov on det.id_movimiento = mov.id_movimiento
                       inner join tipo_movimiento tm on mov.id_tipo_movimiento = tm.id_tipo_movimiento
                       inner join sub_tipo_movimiento stm on mov.id_sub_tipo_movimiento = stm.id_sub_tipo_movimiento
		               inner join articulos art on det.id_articulo= art.id_producto
                       inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega
		               where det.id_movimiento='$idmovimiento'";
			    $resultadoStock =  mysqli_query($conexion,$sql2); 

				 /*Validamos que se inserto el stock del producto*/
				if ($resultadoStock)
				{
                    /*3. Actualizamos el proceso y lo colocamos como guardado*/
			    	 $sql3="UPDATE movimientos set guardarDefinitivo=1
				     where id_movimiento='$idmovimiento'"; 
			         $resultadoUpdate = mysqli_query($conexion,$sql3); 

					 /*Validamos que se actualializo el movimiento como definitivo*/ 
					 if ($resultadoUpdate )
					 {
                        /*4. Se elimina el temporal de los items agregados*/
			           	$sql4="DELETE FROM movimientos_detalle_tmp   
				        where id_movimiento='$idmovimiento'";
			            $resultado  = mysqli_query($conexion,$sql4);   
						
					 } 
					  else{

					     /*5. Se elimina detalle de producto en caso que se haga rollback*/
			           	  $sql5="DELETE FROM movimientos_detalle   
				          where id_movimiento='$idmovimiento'";
			              mysqli_query($conexion,$sql5);  

						 /*6. Se elimina detalle de producto en caso que se haga rollback*/
			           	  $sql6="DELETE FROM stock_productos   
				          where id_movimiento='$idmovimiento'";
			              mysqli_query($conexion,$sql6);  
						   
						 /*7. Se marca el movimiento como no guardado*/
			    	      $sql7="UPDATE movimientos set guardarDefinitivo=0
				          where id_movimiento='$idmovimiento'"; 
			              mysqli_query($conexion,$sql7); 
				        }
				 } 
				 else {
					    /*8. Se elimina detalle de producto en caso que se haga rollback*/
			           	$sql8="DELETE FROM movimientos_detalle   
				        where id_movimiento='$idmovimiento'";
			            mysqli_query($conexion,$sql8);  

						/*9. Se elimina detalle de producto en caso que se haga rollback*/
			           	$sql9="DELETE FROM stock_productos   
				        where id_movimiento='$idmovimiento'";
			            mysqli_query($conexion,$sql9);    
				 }

				/*10. Actualizamos la disponbilidad de los articulos*/ 
				$sql10="CALL actualizar_cantidad_articulos_por_movimiento($idmovimiento)";
                mysqli_query($conexion,$sql10);
			}
           return $resultado;
		} 
		private function actualizar_stock($idmovimiento){
			 $c= new conectar();
			 $conexion=$c->conexion();
	         $fecha=date('Y-m-d');

			 /*1. Se inserta detalle de items*/
			 $sql="INSERT into stock_productos(
			                                    id_movimiento,
				                                id_tipo_movimiento ,
				                                id_sub_tipo_movimiento , 
			                                	no_documento,
			                                	id_bodega_principal,
				                                id_bodega_secundaria, 
				                                tipo_movimiento ,
				                                sub_tipo_movimiento ,
                                                id_movimiento_detalle, 
                                                id_producto,
                                                producto,
                                                lote,
				                                precio,
				                                cantidad,
                                                subtotal,
                                                decuento,
                                                iva,
                                                total, 
                                                stock,
				                                id_stock_credito,
				                                fecha_captura
			                                 ) 
				        SELECT  det.id_movimiento,
                                mov.id_tipo_movimiento,
                                mov.id_sub_tipo_movimiento,
                                mov.no_documento,
                                mov.id_bodega idBodegaPrincipal,
								det.id_bodega_lugar IdBodegaSecundaria,
                                tm.tipo TipoMovimiento,
                                stm.sub_tipo SubTipoMovimiento,
                                det.id_movimiento_detalle,
	                            art.id_producto,
	                            art.nombre,
				                det.lote,
	                            det.precio,
				                det.cantidad,
				                det.subtotal,
							    det.decuento,
								det.iva,
				                det.total, 
								det.cantidad stock,
								0 id_stock_credito,
                               mov.id_usuario,
							   '$fecha' fecha_captura
		               from movimientos_detalle as det
                       inner join movimientos mov on det.id_movimiento = mov.id_movimiento
                       inner join tipo_movimiento tm on mov.id_tipo_movimiento = tm.id_tipo_movimiento
                       inner join sub_tipo_movimiento stm on mov.id_sub_tipo_movimiento = stm.id_sub_tipo_movimiento
		               inner join articulos art on det.id_articulo= art.id_producto
                       inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega
		               where det.id_movimiento='$idmovimiento'";
			return mysqli_query($conexion,$sql); 
		} 
		public function filtrarSubTipoMovimiento($filtro){
			$c=new conectar();
			$conexion=$c->conexion();
            
			/*Si aplica filtro si tiene resultados*/
			 $sql="SELECT id_sub_tipo_movimiento,   
						sub_tipo
				from sub_tipo_movimiento
				where estado = 1 and id_tipo_movimiento ='$filtro'";
            
            $result   = mysqli_query($conexion,$sql);
			$jsonData = array();

            if(mysqli_num_rows($result) > 0){  

             while($row = $result->fetch_row()) {
                  $rows[]=array(
					         "id_sub_tipo_movimiento" => $row[0], 
					         "sub_tipo" => $row[1]
						);
              }	 
              $json = json_encode($rows);    
              return $json ; 
		} 
   }
   private function obtenerStockDisponible($idProducto, $idBodega) {
    $c = new conectar();
    $conexion = $c->conexion();
    $sql = "SELECT cantidad FROM stock_productos_bodega WHERE cantidad > 0 and id_producto = '$idProducto' AND id_bodega_principal = '$idBodega'";
    $result = mysqli_query($conexion, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row ? floatval($row['cantidad']) : 0;
 } 
}
 ?>