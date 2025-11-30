<?php 
	
	require_once "../../clases/Conexion.php";
	$c= new conectar();
	$conexion=$c->conexion();

	$sql="SELECT id_usuario,
					nombre,
					apellido,
					email
			from usuarios";
	$result=mysqli_query($conexion,$sql);

 ?>


<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	<caption><label>Lista de Usuarios</label></caption>
	<thead>
	<tr>
		<td>Nombre</td>
		<td>Apellido</td>
		<td>Usuario</td>
		<td>Editar</td>
		<td>Eliminar</td>
	</tr>
    </thead> 
    <tbody>
	<?php while($ver=mysqli_fetch_row($result)): ?>

	<tr>
		<td><?php echo $ver[1]; ?></td>
		<td><?php echo $ver[2]; ?></td>
		<td><?php echo $ver[3]; ?></td>
		<td>
			<span data-toggle="modal" data-target="#actualizaUsuarioModal" class="btn btn-warning btn-xs" onclick="agregaDatosUsuario('<?php echo $ver[0]; ?>')">
				<span class="glyphicon glyphicon-pencil"></span>
			</span>
		</td>
		<td>
			<span class="btn btn-danger btn-xs" onclick="eliminarUsuario('<?php echo $ver[0]; ?>')">
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
           });
</script>