<?php 
	require_once "../../clases/Conexion.php";
	$c= new conectar();
	$conexion=$c->conexion(); 
	$idmovimiento = $_GET['idmovimiento'];
	$estado = $_GET['estado'];
	$guardado = $_GET['guardado'];
    
	 /*Se carga el valor del iva*/
	 $sql ="SELECT monto
		  from iva as det"; 
            
     $result  = mysqli_query($conexion,$sql);

     if(mysqli_num_rows($result) > 0){   
             while($row = $result->fetch_row()) {
               $valorIva =  $row[0]; 
              }	 
	 }
	 ?>

    <!-- Select2 CSS (agregado) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 JS (agregado) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="container p-3 my-5 bg-light border border-primary">
	<button title="Regresar" class="btn btn-danger" onclick="regresar()" ><i class="glyphicon glyphicon-step-backward" ></i>Regresar</button> 
	<table>
		<div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
		    <input type="text" hidden="" id="idmovimiento" name="idmovimiento"> 
		    <!--<div class="col-sm-3">  
               <label class="label-custom">FILTROS:</label> 
			   <input style="width: 100%;background: #fff4ce;" type="text" class="form-control input-sm" id="buscar" name="buscar">    
			   <p></p>       
			   <button style="width: 100%;" title="Buscar articulo" id="btnBuscar" class="btn btn-primary"><i class="glyphicon glyphicon-search"></i> BUSCAR</button>  
			</div>  -->
			<div class="col-sm-6">   
			 <label class="label-custom">ARTICULO: </label>
			 <select class="form-control input-sm" id="articuloSelect" name="articuloSelect">
			 <option value="A">Selecciona articulo</option>
				<?php 
					$sql="SELECT id_producto,nombre
		            from articulos
					where estado = 1";
		            $result=mysqli_query($conexion,$sql);
					while($ver=mysqli_fetch_row($result)): ?>
					 <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
				 <?php endwhile; ?>
			 </select>  
			 <label style="margin-top: 5px;" class="label-custom">UBICACIÓN: </label>
			 <select class="form-control input-sm" id="almacenSelect" name="almacenSelect">
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
            </div>
			<div class="col-sm-3">   
				<label style="margin-top: 5px;" class="label-custom">PRECIO</label>
			    <input style = "color: #1a36e0;font-size: small;" type="number" class="form-control input-sm" id="precio" name="precio">  
				<label class="label-custom">CANTIDAD</label>
			    <input style = "color: #1a36e0;font-size: small;" type="number" class="form-control input-sm" id="cantidad" name="cantidad">
		    </div>
			<div class="col-sm-3">  
			   <label class="label-custom">LOTE</label>
			   <input type="text" class="form-control input-sm" id="lote" name="lote">
			  <label style="margin-top: 5px;" class="label-custom">ACCIONES:</label>
			  <div>
				  <?php if ($estado=="'1'" && $guardado=="'0'" ){ ?>
					<button title="Agregar articulo" id="btnAgregaMovimientoTemp" class="btn btn-info"><i class="glyphicon glyphicon-plus-sign"></i> AGREGAR</button>
					 <?php }?>  
                   <?php if ($guardado=="'0'" && $estado=="'1'"){?> 
					<button title="Guardar todo" id="btnAgregaMovimientoDefinitivo" class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> GUARDAR</button>
				   <?php }?>  
			  </div> 
			</div>
		</div>
	</table> 
	<p></p>  
	<div id="tablaListaArticulos"></div>
<table>
<label class="label-custom" style="border-bottom: 5px solid #337ab7 !important;">TOTALES:</label> 
<div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
  <div class="col-sm-4"> 
	<label class="label-custom">DESCUENTO/IVA:</label>
	<div>
	   <?php if($guardado=="'0'" ){?>
           <button style="background: black !important;" title="MODIFICAR IVA/DESCUENTO" class="btn btn-warning" onclick="cargardatosIva()" data-toggle="modal" data-target="#agregarivadescuento"><i class="glyphicon glyphicon-menu-hamburger" ></i></button> 
	    <?php } else {  ?>
           <button disabled style="background: black !important;" title="MODIFICAR IVA/DESCUENTO" class="btn btn-warning"  data-toggle="modal" data-target="#"><i class="glyphicon glyphicon-menu-hamburger" ></i></button> 
	    <?php }?>  
   </div> 
  </div> 
  <div class="col-sm-2">  
	<label class="label-custom">SUB TOTAL:</label>
	<input type="number" disabled class="form-control input-sm text-resumen" style="text-align: right !important;" id="subtotalResumen" name="subtotalResumen">  
  </div>
   <div class="col-sm-2">   
	<label class="label-custom">DESCUENTO:</label>
	<input type="number" disabled class="form-control input-sm text-resumen"  style="text-align: right !important;"  id="descuentoResumen" name="descuentoResumen"> 
  </div>
   <div class="col-sm-2">   
	<label class="label-custom">IVA:</label>
	<input type="number" disabled class="form-control input-sm text-resumen"  style="text-align: right !important;"  id="ivaResumen" name="iva"> 
  </div>
  <div class="col-sm-2">   
	<label class="label-custom">TOTAL A PAGAR:</label>
	<input type="number" disabled class="form-control input-sm text-resumen"  style="text-align: right !important;"  id="totalResumen" name="totalResumen"> 
  </div>
</div>
</table>
<footer>
    <hr>
</footer>
<!-- MODAL EDITAR MOVIMIENTO --> 
		<div class="modal fade" id="agregarivadescuento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div  class="modal-dialog modal-sm" role="document">
				<div style="width:100% !important;" class="modal-content">
					<input type="text" hidden="" id="idPorcIVA" name="idPorcIVA">  
					<input type="text" hidden="" id="idPorcDesc" name="idPorcDesc">  
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">DESCUENTO/IVA PARA TODOS LOS PRODUCTOS</h4>
					</div>
					<div class="modal-body"> 
					 <form id="frmUpdateIvaDescuento" enctype="multipart/form-data">
					   <input type="text" hidden="" id="idmovimientoDetalle" name="idmovimientoDetalle">
					   <div class="row"> 
					    <div class="col-sm-12">
						   <label class="label-custom">% IVA:</label>&nbsp;<input type="checkbox" id="ckaplicariva" name="ckaplicariva">
						   <input type="number" disabled class="form-control input-sm" id="porceIVA" name="porceIVA">
						   <label class="label-custom">% DESCUENTO:</label>&nbsp;<input type="checkbox" id="ckaplicardescuento" name="ckaplicardescuento">
						   <input type="number"  class="form-control input-sm" id="porceDESCUENTO" name="porceDESCUENTO">  
					    </div> 
					  </div> 
					<p></p>  
					</form>
					</div>
					<div class="modal-footer">
						<div style="text-align: center;"> 
						   <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >CANCELAR</button>
                           <button title="Actualizar descuento" id="btactualizariva" type="button" class="btn btn-success"  data-dismiss="modal">GUARDAR</button>
						</div> 
				    </div>  
				</div>
			</div>
		</div>
<script type="text/javascript">
		$("#tbDetalleMovimiento").DataTable({
                 responsive: true,
           });
</script>
<script type="text/javascript">
		$(document).ready(function(){
			$('#btnBuscar').click(function(){ 
				 var filtro =$('#buscar').val(); 
			     buscarArticulo(filtro);
			});

			// --- INICIO CAMBIO: Cargar precio según artículo seleccionado ---
			$('#articuloSelect').on('change', function() {
				var idArticulo = $(this).val();
				if (idArticulo === "" || idArticulo === "A") {
					$('#precio').val('');
					return;
				}
				$.ajax({
					type: "POST",
					url: "../procesos/articulos/obtenerPrecioArticulo.php",
					data: { idarticulo: idArticulo },
					success: function(respuesta) {
						try {
							// La respuesta debería ser el precio como string/float
							var precio = JSON.parse(respuesta);
							$('#precio').val(precio);
						} catch (e) {
							$('#precio').val('');
						}
					},
					error: function() {
						$('#precio').val('');
					}
				});
			});
			// --- FIN CAMBIO ---

			// --- INICIO CAMBIO: Select2 en los combos para filtrar opciones ---
			$('#articuloSelect').select2({
				width: '100%',
				placeholder: 'Buscar artículo...',
				allowClear: true
			});
			$('#almacenSelect').select2({
				width: '100%',
				placeholder: 'Buscar bodega...',
				allowClear: true
			});
			// --- FIN CAMBIO SELECT2 ---

		});
      
	  function buscarArticulo(filtro){   
		$("#articuloSelect").empty();
	    $.ajax({
				type:"POST",
				data:"filtro=" + filtro,
				url:"../procesos/articulos/filtrarArticuloByNombre.php",
				success:function(r){   
					dato=jQuery.parseJSON(r);  
					const obj = JSON.parse(dato);
					if (obj != null)
			     	{
                        obj.forEach((option) => {
                        const optionElement = document.createElement("option"); 
                        const selectElement = document.getElementById("articuloSelect");
 
                         if (typeof option === "string") {
                             optionElement.value = option.id;
                             optionElement.text = option.name;
                        } else {
                              optionElement.value = option.id;
                              optionElement.text = option.name;
                         } 
                        selectElement.appendChild(optionElement);
                    }); 
					// Refresca select2 después de actualizar las opciones
					$('#articuloSelect').trigger('change.select2');
				  }
				  else{
					Swal.fire({
                               title: "No se encontraron resultados con el filtro: " + filtro,
                                icon: "warning",
                               draggable: true,
                             }); 
				  } 
				} 
			});
}
  function buscarDetalleResume(idmovimiento){    
	    $.ajax({
				type:"POST",
				data:"idmovimiento=" + idmovimiento,
				url:"../procesos/movimientos/obtenDatosMovimientoResumen.php",
				success:function(r){   
					dato=jQuery.parseJSON(r);   
					const obj = JSON.parse(dato); 
					if (obj != null)
			     	{ 
                        obj.forEach((option) => {   
                          $('#subtotalResumen').val(option.subtotal);
						  $('#totalResumen').val(option.total);
						  $('#descuentoResumen').val(option.descuento);
						  $('#ivaResumen').val(option.iva); 
						  $('#idPorcDesc').val(option.descuentoPorc);
					      $('#idPorcIVA').val(option.ivaPorc);  
					    }); 
				    } 
				} 
			});
}
function eliminaMovimientoArticulo(idmovimiento){ 
var vidmovimiento= <?php echo $idmovimiento ?>;
var vestado= <?php echo $estado ?>;
var vguardado= <?php echo $guardado ?>;
swal.fire({
       title: "¿Estás seguro que deseas eliminar este articulo?",
      text: "Se eliminara este articulo del movimiento en caso que presiones Sí, eliminar.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#10671e",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, eliminar!",
      cancelButtonText: "Cancelar"
    }).then(function(result) { 
        if (result.value) { 
			$.ajax({
					type:"POST",
					data:"idmovimiento=" + idmovimiento,
					url:"../procesos/movimientos/eliminarMovimientoArticuloTmp.php",
					success:function(r){   
						if(r==1){
							detalleMovimientoArticulo(vidmovimiento,vestado,vguardado);
							buscarDetalleResume(vidmovimiento) 
						}else{
							Swal.fire({
                               title: "No se pudo eliminar el articulo del movimiento!!",
                                icon: "error",
                               draggable: true,
                             }); 
						}
					}
				});
        }
    }); 	
}  
function detalleMovimientoArticulo(idmovimiento,estado,guardado){ 
	        $('#tablaListaArticulos').load("movimientos/detalleMovimientoListaProductos.php?idmovimiento='"+ idmovimiento+ "'&estado='"+ estado+ "'&guardado='"+ guardado+ "'"); 
		}
function cargardatosIva (){  
	        $("#porceIVA").val(<?php echo $valorIva ?>);
		   var ivaAplicado = $("#idPorcIVA").val();
		   var idPorcDecu = $("#idPorcDesc").val();
		   if (ivaAplicado>0)
		   { 
             $('#ckaplicariva').prop('checked', true);  
		   }
		   if (idPorcDecu>0)
		   { 
			 $('#porceDESCUENTO').val(idPorcDecu); 
             $('#ckaplicardescuento').prop('checked', true);  
		   }
	} 
/*var inputArticulo = document.getElementById("buscar");*/
detalleMovimientoArticulo(<?php echo $idmovimiento ?>,<?php echo $estado ?>,<?php echo $guardado ?>);
buscarDetalleResume(<?php echo $idmovimiento ?>);
/*
// Execute a function when the user presses a key on the keyboard
inputArticulo.addEventListener("keypress", function(event) {
  // If the user presses the "Enter" key on the keyboard
  if (event.key === "Enter") {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("btnBuscar").click();
  }
}); */
</script> 
<script type="text/javascript">
		$(document).ready(function(){ 
			$('#btnAgregaMovimientoTemp').click(function(){   
			   var vidmovimiento= <?php echo $idmovimiento ?>;
			   var vestado= <?php echo $estado ?>;
			   var vguardado= <?php echo $guardado ?>;
			   var vidarticulo= $("#articuloSelect").val();
               var vidalmacen= $("#almacenSelect").val();
			   var vcantidad= $("#cantidad").val();
			   var vprecio= $("#precio").val();
			   var vlote= $("#lote").val(); 
			   ///alert(vidarticulo);
			   if (vidarticulo == "" || vidarticulo == "A" || vidarticulo == null ) 
			   {
				Swal.fire({
                           title: "Seleccione el articulo agregar",
                           icon: "error",
						   text : "Favor intentar nuevamente.",
                          draggable: true,
                        }); 
						return false;
			   }
			   else if (vidalmacen == "" || vidalmacen == "A" || vidalmacen == null) 
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
				const inputValue= "id_movimiento="+vidmovimiento+"&id_articulo="+vidarticulo+"&id_bodega_lugar="+vidalmacen+"&precio="+vprecio+"&cantidad="+vcantidad+"&lote="+vlote;				
				$.ajax({
					type:"POST",
					data:inputValue, 
					url: "../procesos/movimientos/agregaMovimientoDetalle.php",
					success:function(r){ 
						if(r==1){
							 $("#cantidad").val('');
			                 $("#precio").val('');
			                 $("#lote").val('');
							detalleMovimientoArticulo(vidmovimiento,vestado,vguardado);
							buscarDetalleResume(vidmovimiento);

							$("#articuloSelect").val('A'); 
							$('#articuloSelect').trigger('change.select2'); 
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
                                 title: "Error al agregar el articulo.",
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
</script> 
<script type="text/javascript">
		$(document).ready(function(){ 
			$('#btnAgregaMovimientoDefinitivo').click(function(){   
	  var vidmovimiento= <?php echo $idmovimiento ?>;
	  const inputValue= "idmovimiento="+vidmovimiento;		 	
		 
      swal.fire({
      title: "¿Estás seguro que deseas guardar este movimiento?",
      text: "Se guardara este movimiento de articulo en la base de datos en caso que presiones Sí, eliminar.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#10671e",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, guardar!",
      cancelButtonText: "Cancelar"
    }).then(function(result) { 
        if (result.value) { 
			$.ajax({
					type:"POST",
					data:inputValue, 
					url: "../procesos/movimientos/guardarDefinitivo.php",
					success:function(r){ 
						///alert(r);
						if(r==1){ 
							Swal.fire({
                               title: "¡Movimiento ha sido guardado con èxito!",
                                icon: "success",
                               draggable: true,
                             }); 
							regresar(vidmovimiento); 
						}else{
							Swal.fire({
                                title: "Error al agregar el articulo.",
                                icon: "error",
								text : "Favor intentar nuevamente.",
                               draggable: true,
                             });  
						}
					}
				});
             }
           }); 	         	
			});
		});
</script>  
<script type="text/javascript">
		$(document).ready(function(){ 
		$('#btactualizariva').click(function(){    
        var vidmovimiento= <?php echo $idmovimiento ?>;
        var vestado= <?php echo $estado ?>;
        var vguardado= <?php echo $guardado ?>;
        /**Capturamos los ck de iva y descuento**/
        var vckaplicariva= $("#ckaplicariva").prop("checked")==true?"1":"0"; 
        var vckaplicardescuento= $("#ckaplicardescuento").prop("checked")==true?"1":"0";
        /**Capturamos los porc de iva y descuento**/
        var vporceIVA =       $('#porceIVA').val(); 
        var vporceDESCUENTO = $('#porceDESCUENTO').val();
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
           const inputValue= "id_movimiento="+vidmovimiento+"&porc_iva="+vporceIVA+"&porc_descuento="+vporceDESCUENTO;				 
		   $.ajax({
          	 type:"POST",
             data:inputValue, 
         	 url: "../procesos/movimientos/actualizarMovimientoMontos.php",
             success:function(r){ 
			 ///alert(r);
        	 if(r==1){ 
        		Swal.fire({
                              title: "¡Movimiento actualizado con èxito!",
                               icon: "success",
                              draggable: true,
                            }); 

						 /*Cargamos datos despues de actualizar los valores*/ 
						 $("#idPorcIVA").val(vporceIVA);
		                 $("#idPorcDesc").val(vporceDESCUENTO);
						 $("#porceIVA").val(vporceIVA);
		                 $("#porceDESCUENTO").val(vporceDESCUENTO);
						 
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