
<?php 
	require_once "../../clases/Conexion.php";

	$obj= new conectar();
	$conexion= $obj->conexion();

	$sql="SELECT id_empleados, 
				nombres_apellidos,
				cedula,
				gen.genero
				direccion,
				email,
				telefono,
				fecha_nacimiento,
				cargo.cargo,
				emp.estado
		from empleados emp
		left join genero  as gen on emp.id_genero  = gen.id_genero
		left join cargo_empleado  as cargo on emp.id_cargo=cargo.id_cargo";
	$result=mysqli_query($conexion,$sql);
 ?>

<div class="table-responsive">
	<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	 	<caption><label>Lista de Empleados</label></caption>
		<thead>
	 	<tr>
			<td>ID</td>
	 		<td>CEDULA</td>
	 		<td>NOMBRES Y APELLIDOS</td>
	 		<td>GENERO</td>
	 		<td>NACIMIENTO</td>
	 		<td>CARGO/PUESTO</td> 
			<td>ESTADO</td>
	 		<td>ACCIONES</td> 
	 	</tr> 
		</thead> 
		<tbody>
	 	<?php while($ver=mysqli_fetch_row($result)): ?> 
	 	<tr>
			<td><?php echo $ver[0]; ?></td>
	 		<td><?php echo $ver[2]; ?></td>
	 		<td><?php echo $ver[1]; ?></td>
	 		<td><?php echo $ver[3]; ?></td>
	 		<td><?php echo $ver[6]; ?></td>
	 		<td><?php echo $ver[7]; ?></td> 
			<td>
		    <?php
			if($ver[8]=="1"){
				echo "ACTIVO";
			}else{
				   echo "INACTIVO";
				}
			 ?>
			 </td>
		  <td> 
	 		 <?php if($ver[8]=="1"){?>
	 		<div class="btn-group" style="width: 80px;">   
			   <button title="Editar empleado" class="btn btn-warning" onclick="agregaDatosEmpleado('<?php echo $ver[0] ?>')" data-toggle="modal" data-target="#abremodalEmpleadosUpdate"><i class="glyphicon glyphicon-edit" ></i></button>
			   <button title="Eliminar empleado" class="btn btn-danger"  onclick="eliminarEmpleado('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-remove"></i></button>
			</div> 
		 <?php }?> 
		  <?php if($ver[8]=="0"){?>
			  <div class="btn-group"style="width: 80px;">  
                   <button title="Activar empleado"  class="btn btn-info"  onclick="activarEmpleado('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-check"></i></button>
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
	        	 destroy: true,
		         order: [[0, 'desc']]
           });
</script>