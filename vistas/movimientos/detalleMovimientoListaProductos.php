<?php
   require_once "../../clases/Conexion.php";
   $c= new conectar();
   $conexion=$c->conexion(); 
   $idmovimiento = $_GET['idmovimiento'];
   $estado = $_GET['estado'];
   $guardado = $_GET['guardado'];
   // Obtener el subtipo de movimiento para exponerlo a JS (si no viene de la vista padre)
   if (!isset($subtipoMovimiento) || !$subtipoMovimiento) {
	   $subtipoMovimiento = '';
	   if (!empty($idmovimiento)) {
		   $idmovimiento_sql = trim($idmovimiento, "'");
		   $sqlSubtipo = "SELECT sub.sub_tipo FROM movimientos mov INNER JOIN sub_tipo_movimiento sub ON mov.id_sub_tipo_movimiento = sub.id_sub_tipo_movimiento WHERE mov.id_movimiento = '$idmovimiento_sql' LIMIT 1";
		   $resultSubtipo = mysqli_query($conexion, $sqlSubtipo);
		   if ($resultSubtipo && $row = mysqli_fetch_row($resultSubtipo)) {
			   $subtipoMovimiento = $row[0];
		   }
	   }
   }
?>
<?php
// Obtener el valor del IVA
$valorIva = 0;
$sqlIva = "SELECT monto FROM iva LIMIT 1";
$resultIva = mysqli_query($conexion, $sqlIva);
if ($resultIva && $rowIva = mysqli_fetch_row($resultIva)) {
	$valorIva = $rowIva[0];
}
?>
<?php
// Consulta para obtener los artículos del movimiento
if ($guardado=="'0'") {
	$sqlArticulos = "SELECT   det.id_movimientos_detalle_tmp id_movimiento_detalle,
				  art.id_producto,
				  art.nombre,
				  det.lote,
				  ROUND(det.precio,2) precio,
				  ROUND(det.cantidad,2) cantidad,
				  ROUND(det.subtotal,2) subtotal,
				  ROUND(det.total,2) total,
				  bod.nombreBodega,
				  det.id_bodega_lugar,
				  ROUND(det.decuento,2) decuento,
				  ROUND(det.iva,2) iva
		 from movimientos_detalle_tmp as det
		 inner join articulos art on det.id_articulo= art.id_producto
		 inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega
		 where id_movimiento =  $idmovimiento ";
} else {
	$sqlArticulos = "SELECT   det.id_movimiento_detalle id_movimiento_detalle,
			   art.id_producto,
			   art.nombre,
			   det.lote,
			   ROUND(det.precio,2) precio,
			   ROUND(det.cantidad,2) cantidad,
			   ROUND(det.subtotal,2) subtotal,
			   ROUND(det.total,2) total,
			   bod.nombreBodega,
			   det.id_bodega_lugar,
			   ROUND(det.decuento,2) decuento,
			   ROUND(det.iva,2) iva
	  from movimientos_detalle as det
	  inner join articulos art on det.id_articulo= art.id_producto
	  inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega
	  where id_movimiento =  $idmovimiento ";
}
 $resultListaArticulo = mysqli_query($conexion, $sqlArticulos);
?>
<table id="tbDatosMaestrosDetalle" class="table table-striped nowrap" style="width:100%">
<caption><hash class="hash-custom" style=" border-bottom: 5px solid#337ab7 !important">LISTA DE ARTICULOS:</hash></caption>  
<thead>
<tr>
	<th>ID</th>
	<th>ARTICULO</th>
	<th>BODEGA</th>
	<th>CANTIDAD</th>
	<?php if (strtoupper($subtipoMovimiento) === 'COMPRAS') : ?>
	<th>PRECIO</th>
	<th>SUBTOTAL</th>
	<th>DESCUENTO</th>
	<th>IVA</th>
	<th>TOTAL</th>
	<?php endif; ?>
	<th>ACCIONES</th>
</tr>
</thead>
<tbody>
	<?php while($ver=mysqli_fetch_row($resultListaArticulo)): ?> 
	<tr> 
		<td><?php echo $ver[1]; ?></td>
		<td><?php echo $ver[2]; ?></td>
		<td><?php echo $ver[8]; ?></td>  
		<td><?php echo $ver[5]; ?></td> 
		<?php if (strtoupper($subtipoMovimiento) === 'COMPRAS') : ?>
		<td><?php echo $ver[4]; ?></td>   
		<td><?php echo $ver[6]; ?></td> 
		<td><?php echo $ver[10]; ?></td>   
		<td><?php echo $ver[11]; ?></td>     
		<td style = "color: #1a36e0;font-size: small;"><?php echo $ver[7]; ?></td>   
		<?php endif; ?>
		<td>  
		  <?php if($estado=="'1'" && $guardado=="'0'" ){?>
			  <div class="btn-group">  
					<?php if (strtoupper($subtipoMovimiento) === 'COMPRAS') : ?>
					<button style="background: black !important;" title="MODIFICAR IVA/DESCUENTO" class="btn btn-warning" onclick="buscarDetalleResumeItems('<?php echo $ver[0] ?>',<?php echo $idmovimiento ?>,'<?php echo $ver[2] ?>')" data-toggle="modal" data-target="#modificardescuentoproducto"><i class="glyphicon glyphicon-menu-hamburger" ></i></button> 
					<?php endif; ?>
					<button title="Editar datos articulo" class="btn btn-warning" onclick="agregaDatosDetalleArticulo('<?php echo $ver[0] ?>','<?php echo $ver[9] ?>','<?php echo $ver[4] ?>','<?php echo $ver[5] ?>','<?php echo $ver[3] ?>','<?php echo $ver[2] ?>')" data-toggle="modal" data-target="#abremodalUpdateProduct"><i class="glyphicon glyphicon-edit" ></i></button>
					<button title="Eliminar articulo" class="btn btn-danger"  onclick="eliminaMovimientoArticulo('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-remove"></i></button>
				</div>  
		  <?php }?>     
		</td> 
	</tr>
	<?php endwhile; ?>
	</tbody>
	</table>
<!-- MODAL EDITAR MOVIMIENTO --> 
<div class="modal fade" id="abremodalUpdateProduct" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">EDITAR DATOS DE PRODUCTO:  
							 <input style="font-size: 14px;background: #132d3d; text-align: center;" disabled="disabled" type="text"   id="nombreProducto" name="nombreProducto">
						</h4> 
					</div>
					<div class="modal-body"> 
					  <form id="frmDetalleArticulo" enctype="multipart/form-data">
					    <input type="text" hidden="" id="idmovimientoDetalle" name="idmovimientoDetalle">
					    <div class="row">
					      <div class="col-sm-6">
						     <label class="label-custom">LUGAR DE ALMACEN (BODEGA): </label>
						     <select class="form-control input-sm" id="almacenSelectU" name="almacenSelectU">
						     <option value="A">Selecciona la ubicacion</option>
			            	   <?php 
					              $sql="SELECT id_bodega,nombreBodega
		                          from bodegas
					              where estado = 1";
		                          $result=mysqli_query($conexion,$sql);
					              while($ver=mysqli_fetch_row($result)): ?>
					          <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
				            <?php endwhile; ?>
                           </select>    
						   <label class="label-custom">CANTIDAD:</label>
					  	   <input type="number" class="form-control input-sm" id="cantidadU" name="cantidadU">
				  	  </div> 
					<div class="col-sm-6" id="colPrecioLote">
						<!-- El input de precioU siempre existe, pero solo es visible para COMPRAS -->
						<?php if (in_array(strtoupper($subtipoMovimiento), ['COMPRAS'])) : ?>
							<label class="label-custom">PRECIO:</label>
							<input type="number" class="form-control input-sm" id="precioU" name="precioU">
							<label class="label-custom">LOTE:</label>
							<input type="text" class="form-control input-sm" id="loteU" name="loteU">  
						<?php else: ?>
							<input type="hidden" id="precioU" name="precioU">
							<input type="hidden" id="loteU" name="loteU">
						<?php endif; ?>
					</div>
					 </div> 
					 <p></p>  
					</form>
					</div>
					<p></p>  
					<p></p>  
					<div class="modal-footer">
						<div style="text-align: center;"> 
						   <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >CANCELAR</button>
                           <button title="Actualizar articulo" id="btnActualizarDetalleMovimiento" type="button" class="btn btn-success" data-dismiss="modal">GUARDAR</button>
						</div> 
				    </div>  
		     </div>
	  </div>
</div>
<!-- MODAL MODIFICAR DESCUENTO POR PRODUCTO --> 
<div class="modal fade" id="modificardescuentoproducto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div  class="modal-dialog modal-sm" role="document">
				<div style="width:90% !important;" class="modal-content">
					<input type="text" hidden="" id="idPorcIVAItem" name="idPorcIVAItem">  
					<input type="text" hidden="" id="idPorcDescItem" name="idPorcDescItem">  
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabelItem">DESCUENTOS / IVA</h4>
						 <input style="font-size: 14px;background: #132d3d; text-align: center;    width: 100%;" disabled="disabled" type="text"   id="nombreProductoItem" name="nombreProductoItem">
					</div>
					<div class="modal-body"> 
					 <form id="frmUpdateIvaDescuento" enctype="multipart/form-data">
					   <input type="text" hidden="" id="idmovimientoDetalleItem" name="idmovimientoDetalleItem">
					   <input type="text" hidden="" id="idmovimiento" name="idmovimiento">
					   <div class="row"> 
						<div class="col-sm-12">
							<?php if (strtoupper($subtipoMovimiento) === 'COMPRAS') : ?>
							<label class="label-custom">% IVA:</label>&nbsp;<input type="checkbox" id="ckaplicarivaItem" name="ckaplicarivaItem">
							<input type="number" disabled class="form-control input-sm" id="porceIVAItem" name="porceIVAItem">
							<label class="label-custom">% DESCUENTO:</label>&nbsp;<input type="checkbox" id="ckaplicardescuentoItem" name="ckaplicardescuentoItem">
							<input type="number"  class="form-control input-sm" id="porceDESCUENTOItem" name="porceDESCUENTOItem">  
							<?php endif; ?>
						</div>
					  </div> 
					<p></p>  
					</form>
					</div>
					<div class="modal-footer">
						<div style="text-align: center;"> 
						   <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >CANCELAR</button>
                           <button title="Actualizar descuento/iva" id="btactualizarivaItem" type="button" class="btn btn-success"  data-dismiss="modal">GUARDAR</button>
						</div> 
				    </div>  
				</div>
			</div>
</div>
 <style> 
hr {
    border-top: 2px solid #eadfdf;
}
$container-max-widths: (
  sm: 540px,
  md: 720px,
  lg: 960px,
  xl: 1140px,
  xxl: 1320px
); 
</style> 
<script type="text/javascript">
$(document).ready(function(){
	// Si ya existe una instancia previa, destrúyela antes de inicializar
	if ($.fn.DataTable.isDataTable('#tbDatosMaestrosDetalle')) {
		$('#tbDatosMaestrosDetalle').DataTable().destroy();
	}
	$("#tbDatosMaestrosDetalle").DataTable({
		responsive: true,
	});
	// INICIO CAMBIO: Select2 para el combo de bodegas en el modal editar producto
	$('#almacenSelectU').select2({
		width: '100%',
		placeholder: 'Buscar bodega...',
		allowClear: true
	});
	// FIN CAMBIO

	$('#btnActualizarDetalleMovimiento').click(function(){   
		var vidmovimientoHeader= <?php echo $idmovimiento ?>;
		var vestado= <?php echo $estado ?>;
		var vguardado= <?php echo $guardado ?>;
		var vidmovimiento = $("#idmovimientoDetalle").val();
		var vidalmacen    = $("#almacenSelectU").val();
		var vcantidad     = $("#cantidadU").val();
		var subtipo = (window.subtipoMovimientoActivo || '').toUpperCase();
		var vprecio = $("#precioU").val(); // Siempre tomar del campo oculto
		var vlote = '';
		if(subtipo === 'COMPRAS') {
			vlote = $("#loteU").val();
		} else {
			vlote = '';
		}
		if (vidalmacen == "" || vidalmacen == "A") 
		{
			Swal.fire({
				title: "Seleccione la bodega agregar",
				icon: "error",
				text : "Favor intentar nuevamente.",
				draggable: true,
			}); 
			return false;
		}
		else if (vcantidad == "" || vcantidad == "0")
		{
			Swal.fire({
				title: "Ingresa la cantidad, esta debe de ser mayor a cero.",
				icon: "error",
				text : "Favor intentar nuevamente.",
				draggable: true,
			}); 
			return false;
		}
		// Validar precio para todos los subtipos
		if (vprecio === '' || vprecio == 0) {
			Swal.fire({
				title: "Ingresa el precio, este debe de ser mayor a cero.",
				icon: "error",
				text : "Favor intentar nuevamente.",
				draggable: true,
			});
			return false;
		}
		enviarEdicion();
		function enviarEdicion() {
			var inputValue = "id_movimiento_detalle="+vidmovimiento+"&id_bodega_lugar="+vidalmacen+"&precio="+vprecio+"&cantidad="+vcantidad+"&lote="+vlote;
			// El precio siempre se envía
			//alert(inputValue);
			$.ajax({
				type:"POST",
				data:inputValue, 
				url: "../procesos/movimientos/actualizarMovimientoDetalle.php",
				success:function(r){ 
					///alert(r);
					if(r==1){
						$("#idmovimientoDetalle").val('');
						$("#almacenSelectU").val('').trigger('change');
						$("#cantidadU").val('');
						$("#precioU").val('');
						$("#loteU").val('');
						detalleMovimientoArticulo(vidmovimientoHeader,vestado,vguardado);
						buscarDetalleResume(vidmovimientoHeader);
					}else{ 
						if (r == 2)
						{
							Swal.fire({
								title: "STOCK INSUFICIENTE",
								icon: "warning",
								text : "No tiene suficiente stok en el articulo para realizar salidas.",
								draggable: true,
							});  
						}
						else{
							Swal.fire({
								title: "Error al actualizar el articulo.",
								icon: "error",
								text : "Favor intentar nuevamente.",
								draggable: true,
							});  
						}
					}
				}
			});
		}
	});
});
function agregaDatosDetalleArticulo(idmovimientoDetalle, idubicacion, precio, cantidad, lote, producto) {
	$('#idmovimientoDetalle').val(idmovimientoDetalle);
	$('#almacenSelectU').val(idubicacion).trigger('change'); // actualiza select2 si está activo
	$('#cantidadU').val(cantidad);
	// Siempre cargar precio y lote, aunque luego se oculten o deshabiliten según subtipo
	$('#precioU').val(precio);
	$('#loteU').val(lote);
	$('#nombreProducto').val(producto);
}
	   $(document).ready(function(){ 
            // INICIO CAMBIO: Select2 para el combo de bodegas en el modal editar producto
            $('#almacenSelectU').select2({
                width: '100%',
                placeholder: 'Buscar bodega...',
                allowClear: true
            });
            // FIN CAMBIO

			$('#btnActualizarDetalleMovimiento').click(function(){   

			   var vidmovimientoHeader= <?php echo $idmovimiento ?>;
			   var vestado= <?php echo $estado ?>;
			   var vguardado= <?php echo $guardado ?>;

			   var vidmovimiento = $("#idmovimientoDetalle").val();
			   var vidalmacen    = $("#almacenSelectU").val();
			   var vcantidad     = $("#cantidadU").val();
			   var vprecio       = $("#precioU").val();
			   var vlote         = $("#loteU").val(); 

			   // Aplicar ocultamiento de columnas/campos según subtipo activo
			   if (typeof window.aplicarOcultamientoPorSubtipo === 'function') {
				   window.aplicarOcultamientoPorSubtipo();
			   }
			  
			   if (vidalmacen == "" || vidalmacen == "A") 
			   {
                       Swal.fire({
                           title: "Seleccione la bodega agregar",
                           icon: "error",
						   text : "Favor intentar nuevamente.",
                          draggable: true,
                        }); 
						return false;
			   }
			   else if (vcantidad == "" || vcantidad == "0")
			   {
                       Swal.fire({
                           title: "Ingresa la cantidad, esta debe de ser mayor a cero.",
                           icon: "error",
						   text : "Favor intentar nuevamente.",
                          draggable: true,
                        }); 
						return false;
			   }
               else if (vprecio == "" || vprecio == "0")
			   {
                       Swal.fire({
                           title: "Ingresa el precio, este debe de ser mayor a cero.",
                           icon: "error",
						   text : "Favor intentar nuevamente.",
                          draggable: true,
                        }); 
						return false;
			   } 
				const inputValue= "id_movimiento_detalle="+vidmovimiento+"&id_bodega_lugar="+vidalmacen+"&precio="+vprecio+"&cantidad="+vcantidad+"&lote="+vlote;				
				///alert(inputValue);
				$.ajax({
					type:"POST",
					data:inputValue, 
					url: "../procesos/movimientos/actualizarMovimientoDetalle.php",
					success:function(r){ 
						if(r==1){
							 $("#idmovimientoDetalle").val('');
			                 $("#almacenSelectU").val('').trigger('change');
			                 $("#cantidadU").val('');
							 $("#precioU").val('');
							 $("#loteU").val('');
							detalleMovimientoArticulo(vidmovimientoHeader,vestado,vguardado);
							buscarDetalleResume(vidmovimientoHeader) 
						}else{ 
						   if (r == 2)
							{
                               Swal.fire({
                                  title: "STOCK INSUFICIENTE",
                                  icon: "warning",
								  text : "No tiene suficiente stok en el articulo para realizar salidas.",
                                  draggable: true,
                             });  
							}
							else{
								 Swal.fire({
                                 title: "Error al actualizar el articulo.",
                                 icon: "error",
								 text : "Favor intentar nuevamente.",
                                 draggable: true,
                              });  
							}
						}
					}
				});
			});
		});

function cargardatosIvaItem (){  
		   var ivaAplicado = $("#idPorcIVAItem").val();
		   var idPorcDecu = $("#idPorcDescItem").val();
		    $("#porceIVAItem").val(<?php echo $valorIva ?>);
		   if (ivaAplicado>0)
		   { 
             $('#ckaplicarivaItem').prop('checked', true);  
			 $('#porceIVAItem').val(ivaAplicado); 
		   }
		   if (idPorcDecu>0)
		   { 
			 $('#porceDESCUENTOItem').val(idPorcDecu); 
             $('#ckaplicardescuentoItem').prop('checked', true);  
		   }
	} 
function buscarDetalleResumeItems(idDetalle,idmovimiento,producto){    
		$('#idmovimientoDetalleItem').val(idDetalle);
		$('#idmovimiento').val(idmovimiento);
	    $('#nombreProductoItem').val(producto);
	    const inputValue= "iddetalle="+idDetalle+"&idmovimiento="+idmovimiento;				
	    $.ajax({ 
				type:"POST",
				data:inputValue,
				url:"../procesos/movimientos/obtenDatosMovimientoResumenItem.php",
				success:function(r){    
					dato=jQuery.parseJSON(r);   
					const obj = JSON.parse(dato);
					if (obj != null)
			     	{ 
                        obj.forEach((option) => {   
						  $('#idPorcDescItem').val(option.descuentoPorc);
					      $('#idPorcIVAItem').val(option.ivaPorc);
						  cargardatosIvaItem();  
					    }); 
				    } 
				} 
			}); 
}
</script> 
<script type="text/javascript">
    $(document).ready(function(){ 
		 $('#btactualizarivaItem').click(function(){   
        var vidmovimientoItem = $("#idmovimientoDetalleItem").val();
		var vidmovimiento= <?php echo $idmovimiento ?>;
        var vestado= <?php echo $estado ?>;
        var vguardado= <?php echo $guardado ?>;
        /**Capturamos los ck de iva y descuento**/
        var vckaplicariva= $("#ckaplicarivaItem").prop("checked")==true?"1":"0"; 
        var vckaplicardescuento= $("#ckaplicardescuentoItem").prop("checked")==true?"1":"0";
        /**Capturamos los porc de iva y descuento**/
        var vporceIVA =       $('#porceIVAItem').val(); 
        var vporceDESCUENTO = $('#porceDESCUENTOItem').val();
         /**Convertimos lo ck a int para vlaidarlos como int (1 o 0)**/
        var vckaplicardescuentoVal =  parseInt(vckaplicardescuento,10);
        var vckaplicarivaVal =  parseInt(vckaplicariva,10);
        
        /*Se asigna el porc del iva a cero en caso que este no sea marcado para aplicarlo*/
        if (vckaplicarivaVal == 0)
        {
              vporceIVA = 0;
        }
        /*Se asigna el porc del descuento a cero en caso que este no sea marcado para aplicarlo*/
        if (vckaplicardescuentoVal == 0)
        {
                 vporceDESCUENTO = 0;
        }
          if (vckaplicardescuentoVal == 1 && vporceDESCUENTO <= 0){
                 Swal.fire({
                        title: "Ingresar descuento.",
                               icon: "error",
        				text : "Debe de ingresar el porcentaje del descuento.",
                              draggable: true,
                 });
        		$("#porceDESCUENTO").val('0');  
        		return false;
             }   
			 if (vporceDESCUENTO >= 100){
                 Swal.fire({
                        title: "Descuento no permitido.",
                               icon: "error",
        				text : "El descuento no puede ser mayor o igual al 100%.",
                              draggable: true,
                 }); 
        		return false;
             }     
           const inputValue= "id_movimiento_detalle="+vidmovimientoItem+"&porciva="+vporceIVA+"&pordescuento="+vporceDESCUENTO;				  	  
		   ///alert(inputValue);  
		   $.ajax({
          	 type:"POST",
             data:inputValue, 
         	 url: "../procesos/movimientos/actualizarMovimientoItem.php",
             success:function(r){ 
			 ///alert(r);
        	 if(r==1){ 
        		Swal.fire({
                              title: "¡Movimiento actualizado con èxito!",
                               icon: "success",
                              draggable: true,
                            }); 

						 /*Cargamos datos despues de actualizar los valores*/ 
						 $("#porceIVAItem").val(vporceIVA);
		                 $("#porceDESCUENTOItem").val(vporceDESCUENTO); 
						 /*Se cargan los datos del resumen*/ 
        	             detalleMovimientoArticulo(vidmovimiento,vestado,vguardado);
						 buscarDetalleResume(vidmovimiento)  
        	}else{
        		Swal.fire({
                             title: "Error al actualizar el articulo.",
                             icon: "error",
        				     text : "Favor intentar nuevamente.",
                             draggable: true,
                          });  
        		      }
                 	}
             }); 
			});
		}); 
</script> 