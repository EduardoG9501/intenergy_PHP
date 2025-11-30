

	<?php 
			require_once "../../clases/Conexion.php";
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT id_categoria,nombreCategoria, subcategoria,estado
					FROM categorias
					where estado = 1";
			$result=mysqli_query($conexion,$sql);
	 ?>


<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	<caption><label>Administrar categorias</label></caption>
	<thead>
	<tr>
		<td>Categoria</td>
		<td>SubCategoria</td>
		<td>Estado</td>
		<td>Editar</td>
		<td>Eliminar</td>
	</tr>
   </thead> 
	<tbody>
	<?php
	while ($ver=mysqli_fetch_row($result)):
	 ?> 
	<tr>
		<td><?php echo $ver[1] ?></td>
		<td>
		   <?php
			if($ver[2]=="1"){
				echo "SI";
			}else{
				   echo "NO";
				}
			 ?>
		</td>
		<td>
		   <?php
			if($ver[3]=="1"){
				echo "ACTIVO";
			}else{
				   echo "INACTIVO";
				}
			 ?>
		</td>
		<td>
			<span class="btn btn-warning btn-xs" data-toggle="modal" data-target="#actualizaCategoria" onclick="agregaDato('<?php echo $ver[0] ?>','<?php echo $ver[1] ?>','<?php echo $ver[2] ?>')">
				<span class="glyphicon glyphicon-pencil"></span>
			</span>
		</td>
		<td>
			<span class="btn btn-danger btn-xs" onclick="eliminaCategoria('<?php echo $ver[0] ?>')">
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