<?php 
	require_once "../../clases/Conexion.php";
	$c = new conectar();
	$conexion = $c->conexion();

	$sql = "SELECT id_usuario, nombre, apellido, email,estado FROM usuarios where email != 'admin'";
	$result = mysqli_query($conexion, $sql);
?>
<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%"> 
	<caption><label>Lista de Usuarios</label></caption>
	<thead>
		<tr>
		    <td>Id</td>
			<td>Nombre</td>
			<td>Apellido</td>
			<td>Usuario</td>
			<td>Estado</td>
			<td>Editar</td>
			<td>Eliminar</td>
			<td>Activar</td>
			<td>Permisos</td>
		</tr>
	</thead>
	<tbody>
		<?php while($ver = mysqli_fetch_row($result)): ?>
		<tr>
			<td><?php echo $ver[0]; ?></td>
			<td><?php echo $ver[1]; ?></td>
			<td><?php echo $ver[2]; ?></td>
			<td><?php echo $ver[3]; ?></td>
			<td>
		       <?php
			     if($ver[4]=="1"){
			     	echo "ACTIVO";
			     }else{
				      echo "INACTIVO";
				   }
			   ?>
	    	</td>
			<td>
			  <?php if($ver[4]=="1"){  ?>
				  <span  data-toggle="modal" data-target="#actualizaUsuarioModal" class="btn btn-warning btn-xs" onclick="agregaDatosUsuario('<?php echo $ver[0]; ?>')">
					<span class="glyphicon glyphicon-pencil"></span>
				 </span>
				<?php  } ?>
			</td>
			<td>
			  <?php if($ver[4]=="1"){  ?>
				 <span class="btn btn-danger btn-xs" onclick="eliminarUsuario('<?php echo $ver[0]; ?>')">
					<span class="glyphicon glyphicon-remove"></span>
				</span>
				<?php  } ?>
			</td>
			<td>
				<span class="btn btn-success btn-xs" onclick="activarUsuario('<?php echo $ver[0]; ?>')">
					<span class="glyphicon glyphicon-check"></span>
				</span>
			</td>
			<td>
				<?php if($ver[4]=="1"){  ?>
				  <button class="btn btn-success btn-sm" onclick="abrirPermisosUsuario('<?php echo $ver[0]; ?>')">
					Permisos
				  </button>
				 <?php  } ?>
			</td> 
		</tr>
		<?php endwhile; ?>
	</tbody>
</table>   
<script type="text/javascript"> 
	$('#tbDatosMaestros').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']] // Ordena por la primera columna (ID) ascendente
    });
</script>