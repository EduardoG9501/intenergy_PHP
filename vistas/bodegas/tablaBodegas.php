

	<?php 
			require_once "../../clases/Conexion.php";
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT b.id_bodega,b.identificador,b.nombreBodega,b.es_bodega_secundaria,bs.nombreBodega bodegasecundaria,b.es_bodega_secundaria,b.id_bodega_principal
					FROM bodegas b
					left join  bodegas bs on b.id_bodega_principal = bs.id_bodega
					where b.estado = 1";
			$result=mysqli_query($conexion,$sql);
	 ?>
 
<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	<caption><label>Administrar bodegas</label></caption>
	<thead>
	<tr>
		<td>Id</td>
		<td>Prefijo-Identificador</td>
		<td>Bodega</td>
		<td>¿Es Secundaria?</td>
		<td>Bodega Padre</td>
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
		<td><?php echo $ver[2] ?></td>  
		<td>
		   <?php
			if($ver[3]=="1"){
				echo "SI";
			}else{
				echo "NO";
			}
			?>
		</td>
		<td><?php echo $ver[4] ?></td>
		<td>
			<span class="btn btn-warning btn-xs" data-toggle="modal" data-target="#actualizaBodega" onclick="agregaDato('<?php echo $ver[0] ?>','<?php echo $ver[2] ?>','<?php echo $ver[5] ?>','<?php echo $ver[6] ?>')">
				<span class="glyphicon glyphicon-pencil"></span>
			</span>
		</td>
		<td>
			<span class="btn btn-danger btn-xs" onclick="eliminaBodega('<?php echo $ver[0] ?>')">
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
 