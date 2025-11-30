

	<?php 
			require_once "../../clases/Conexion.php";
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT id_cargo,cargo 
					FROM cargo_empleado
					where estado = 1";
			$result=mysqli_query($conexion,$sql);
	 ?>
 
<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	<caption><label>Administrar Cargos Empleados</label></caption>
	<thead>
	<tr>
		<td>Id</td>
		<td>Cargo</td>
		<td>Editar</td>
		<td>Eliminar</td>
	</tr>
   </thead>
   <tbody>
	<?php
	while ($ver=mysqli_fetch_row($result)):
	 ?>

	<tr>
		<td><?php echo $ver[0] ?></td>
		<td><?php echo $ver[1] ?></td>
		<td>
			<span class="btn btn-warning btn-xs" data-toggle="modal" data-target="#actualizaEmpleado" onclick="agregaDato('<?php echo $ver[0] ?>','<?php echo $ver[1] ?>')">
				<span class="glyphicon glyphicon-pencil"></span>
			</span>
		</td>
		<td>
			<span class="btn btn-danger btn-xs" onclick="eliminaCargo('<?php echo $ver[0] ?>')">
				<span class="glyphicon glyphicon-remove"></span>
			</span>
		</td>
	</tr>

<?php endwhile; ?>
</tbody>
</table>
<script type="text/javascript">
		$("#tbDatosMaestros").DataTable({
                 responsive: true,
	        	 destroy: true,
		         order: [[0, 'desc']]
           });
</script>
 