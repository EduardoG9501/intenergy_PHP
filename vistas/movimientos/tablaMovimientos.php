<?php 
	session_start();
	require_once "../../clases/Conexion.php";
	$c= new conectar();
	$conexion=$c->conexion();
	$id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;

	$sql="SELECT mov.id_movimiento,
	            prov.razon_social,
	            bod.nombreBodega, 
				tipomov.tipo, 
				subtipomov.sub_tipo, 
				mov.no_documento,
				mov.fechaDocumento, 
				tipopago.forma_pago,
				mov.fechaPago,
				mov.cant_dia_pago,
				mov.estado,
				mov.guardarDefinitivo
	  FROM movimientos as mov 
	  LEFT JOIN proveedor as prov ON mov.id_proveedor=prov.id_proveedor
	  LEFT JOIN bodegas as bod ON mov.id_bodega=bod.id_bodega
	  LEFT JOIN tipo_movimiento as tipomov ON tipomov.id_tipo_movimiento=mov.id_tipo_movimiento
	  LEFT JOIN sub_tipo_movimiento as subtipomov ON subtipomov.id_sub_tipo_movimiento=mov.id_sub_tipo_movimiento
	  LEFT JOIN forma_pago as tipopago ON tipopago.id_forma_pago=mov.id_forma_pago
	  WHERE mov.id_bodega = '$id_bodega'
	  ORDER BY mov.id_movimiento DESC";
	$result=mysqli_query($conexion,$sql);
?>  
<div class="container p-3 my-5 bg-light border border-primary">
	<button title="Agregar nuevo movimiento" id="btnAgregaMovimiento" class="btn btn-primary" data-toggle="modal" data-target="#abremodalUpdateAgregarMovimeinto">Nuevo Movimiento</button> 
	<hr>  
	<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	<caption><hash class="hash-custom">Movimientos de inventario</hash></caption>  
	<thead>
	<tr>   
		<td>Id</td> 
		<td>Tipo</td> 
		<td>Subtipo</td>
		<td>Proveedor</td> 
		<td>Forma Pago</td> 
		<td>Bodega</td>  
		<td>F.Documento</td> 
		<td>Estado</td>
		<td>Guardado</td>
		<td>Acciones</td>
	</tr> 
	</thead>
	<tbody>
	<?php while($ver=mysqli_fetch_row($result)): ?> 
	<tr> 
	    <td><?php echo $ver[0]; ?></td>
		<td><?php echo $ver[3]; ?></td>  
		<td><?php echo $ver[4]; ?></td>   
		<td><?php echo $ver[1]; ?></td>
		<td><?php echo $ver[7]; ?></td>  
		<td><?php echo $ver[2]; ?></td>   
		<td><?php echo $ver[6]; ?></td> 
		<td style="text-align: center !important; <?php  if($ver[10]=="1"){ echo "background-color: rgb(196, 244, 196);";}else{ echo "background-color: rgb(238, 203, 203);";} ?>">
		   <?php
			if($ver[10]=="1"){
				echo "ACTIVO";
			}else{
				   echo "INACTIVO";
				}
			 ?>
		</td>
		<td style="text-align: center !important; <?php  if($ver[11]=="1"){ echo "background-color: rgb(196, 244, 196);";}else{ echo "background-color: rgb(243 249 208);";} ?>">
		   <?php
			if($ver[11]=="1"){
				echo "SI";
			}else{
				   echo "NO";
				}
			 ?>
		</td>
		<td>  
		  <?php if($ver[10]=="1" && $ver[11]=="0"  ){?>
			  <div class="btn-group">  
                   <button title="Editar movimiento" class="btn btn-warning" onclick="agregaDatosMovimiento('<?php echo $ver[0] ?>')" data-toggle="modal" data-target="#abremodalUpdateMovimiento"><i class="glyphicon glyphicon-edit" ></i></button>
				   <button title="Ver Detalle" class="btn btn-info"  onclick="detalleMovimiento('<?php echo $ver[0] ?>','<?php echo $ver[10] ?>','<?php echo $ver[11] ?>')"><i class="glyphicon glyphicon-search"></i></button>  
				   <button title="Eliminar movimiento" class="btn btn-danger"  onclick="eliminaMovimiento('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-remove"></i></button>
				</div>  
		  <?php }?>  

		  <?php if($ver[10]=="0" || $ver[11]=="1"  ){?>
			  <div class="btn-group">    
			       <button title="Ver movimiento" class="btn btn-success" onclick="agregaDatosMovimientoDetalle('<?php echo $ver[0] ?>')" data-toggle="modal" data-target="#abremodalDetalledateMovimiento"><i class="glyphicon glyphicon-folder-open" ></i></button>
				   <button title="Ver Detalle" class="btn btn-info"  onclick="detalleMovimiento('<?php echo $ver[0] ?>','<?php echo $ver[10] ?>','<?php echo $ver[11] ?>')"><i class="glyphicon glyphicon-search"></i></button>  
		     </div>  
		  <?php }?>  
		</td> 
	</tr>
<?php endwhile; ?>
</tbody>
</table> 
<script type="text/javascript">
$(document).ready(function(){
  $('#btnAgregaMovimiento').click(function(){ 
  	  setDateValue();
	   resetFormNuevoMovimiento();
   });
});

$("#tbDatosMaestros").DataTable({
                 responsive: true,
				  destroy: true,
                  order: [[0, 'desc']]
});
		  

var t = document.getElementById('tbDatosMaestros');
			if(t) {
					Array.from(t.rows).forEach((td, rowIdx) => {
					Array.from(td.cells).forEach((cell, cellIdx) => {
					if (cell.innerText == 'ACTIVO') {
						cell.style.backgroundColor = '#c4f4c4';
					}else if (cell.innerText == 'INACTIVO') {
						cell.style.backgroundColor = '#eecbcb';
					} 
				});
			});
		} 
</script>
 <style> 
hr {
    border-top: 2px solid #eadfdf;
}
#tbDatosMaestros tbody tr:hover {
    background-color: #c8f5f8ff;
    cursor: pointer;
    transition: background 0.2s;
} 
</style>