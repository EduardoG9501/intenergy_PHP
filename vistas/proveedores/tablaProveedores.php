
<?php 
	require_once "../../clases/Conexion.php";

	$obj= new conectar();
	$conexion= $obj->conexion();

	$sql="SELECT pro.id_proveedor, 
				tipoid.tipo tipoidentificacion,
				pro.numero_identificacion,
				pro.razon_social,
				pro.direccion,
				pro.telefono,
				pro.email,
				pro.nombre_comercial,
				tipocon.tipo tipocontribuyente,
				pro.agente_retencion,
				pro.estado
		from proveedor pro
		inner join tipoidentificacion as tipoid on pro.tipo_identificacion=tipoid.id_tipo_identificacion
		inner join tipocontribuyente as tipocon on pro.tipo_contribuyente=tipocon.id_tipo_contribuyente";
	$result=mysqli_query($conexion,$sql);
 ?>

<div class="table-responsive">
	<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	 	<caption><label>Lista de Proveedores</label></caption>
		<thead>
	 	<tr>
	 		<td>Tipo Identificacion</td>
	 		<td>No.Identificacion</td>
	 		<td>Razon Social</td> 
			<td>Nombre Comercial</td>
			<td>Tipo Contribuyente</td> 
			<td>Estado</td>
	 		<td>Acciones</td>
	 	</tr>
        </thead> 
		<tbody>
	 	<?php while($ver=mysqli_fetch_row($result)): ?>

	 	<tr>
	 		<td><?php echo $ver[1]; ?></td>
	 		<td><?php echo $ver[2]; ?></td>
	 		<td><?php echo $ver[3]; ?></td> 
			<td><?php echo $ver[7]; ?></td>
			<td><?php echo $ver[8]; ?></td> 
			<td>
		   <?php
			if($ver[10]=="1"){
				echo "ACTIVO";
			}else{
				   echo "INACTIVO";
				}
			 ?>
		</td>
		<td> 
		 <?php if($ver[10]=="1"){?>
	 		<div class="btn-group" style="width: 80px;">   
			   <button title="Editar proveedor" class="btn btn-warning" onclick="agregaDatosProveedor('<?php echo $ver[0] ?>')" data-toggle="modal" data-target="#abremodalProveedoresUpdate"><i class="glyphicon glyphicon-edit" ></i></button>
			   <button title="Eliminar proveedor" class="btn btn-danger"  onclick="eliminarProveedor('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-remove"></i></button>
			</div> 
		 <?php }?> 
		  <?php if($ver[10]=="0"){?>
			  <div class="btn-group"style="width: 80px;">  
                   <button title="Activar proveedor"  class="btn btn-info"  onclick="activarProveedor('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-check"></i></button>
		      </div>  
		  <?php }?>  
		</td> 
	 	</tr>
	 <?php endwhile; ?>
</tbody>
</table>
</div>
<script type="text/javascript">
		$("#tbDatosMaestros").DataTable({
                 responsive: true,
           });
</script>