<?php 
	session_start();
	require_once "../../clases/Conexion.php";
	$c= new conectar();
	$conexion=$c->conexion();
    $accesoVistaPrecio = isset($_SESSION['accesoVistaPrecio']) ? $_SESSION['accesoVistaPrecio'] : false;
	$accesoAgregarArticulo = isset($_SESSION['accesoAgregarArticulo']) ? $_SESSION['accesoAgregarArticulo'] : false;
    $accesoEditarArticulo = isset($_SESSION['accesoEditarArticulo']) ? $_SESSION['accesoEditarArticulo'] : false;
	$accesoEliminarArticulo = isset($_SESSION['accesoEliminarArticulo']) ? $_SESSION['accesoEliminarArticulo'] : false;
	// Tomar la bodega seleccionada de la sesión
	$id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;
	$nombre_bodega = '';
	if ($id_bodega) {
		$sqlNombreBodega = "SELECT nombreBodega FROM bodegas WHERE id_bodega = $id_bodega LIMIT 1";
		$resNombreBodega = mysqli_query($conexion, $sqlNombreBodega);
		if ($rowNombreBodega = mysqli_fetch_assoc($resNombreBodega)) {
			$nombre_bodega = $rowNombreBodega['nombreBodega'];
		}
	}

	$sql="SELECT    art.id_producto,
	                prov.razon_social,
	                art.nombre,
	                art.codigobarra,
					art.descripcion,
					art.referencia,
					cat.nombreCategoria,
					subcat.nombreCategoria subcategoria,
					art.cantidad,
					art.precio,
					img.ruta,  
					art.estado  
		  from articulos as art 
		  left join imagenes as img
		  on art.id_imagen=img.id_imagen
		  left join categorias as cat
		  on art.id_categoria=cat.id_categoria
		  left join categorias as subcat
		  on art.id_subcategoria=subcat.id_categoria
		  left join proveedor as prov
		  on art.id_proveedor=prov.id_proveedor";
	$result=mysqli_query($conexion,$sql);
?>  
<div class="container p-3 my-5 bg-light border border-primary">
    <?php if ($accesoAgregarArticulo): ?>
	   <button title="Agregar nuevo articulo" id="btnAgregaArticulo" class="btn btn-primary" data-toggle="modal" data-target="#abremodalUpdateAgregarArticulo">Nuevo Articulo</button> 
	  <hr>  
	<?php endif; ?> 
	<table id="tbDatosMaestros" class="table table-striped nowrap" style="width:100%">
	<caption><hash class="hash-custom">Administrar articulos</hash></caption>  
	<thead>
	<tr>   
		<td>Id</td> 
		<td>Proveedor</td> 
		<td>Nombre</td>
		<td>Cod.Barra</td> 
		<td>Categoria</td>  
		<td style="color: green;font-weight: bold;">
			Stock<?php echo $nombre_bodega ? ' ('.htmlspecialchars($nombre_bodega).')' : ''; ?>
		</td>
		<?php if ($accesoVistaPrecio): ?>
		  <td>Precio</td>
	    <?php endif; ?> 
		<td>Imagen</td> 
		<td>Estado</td>
	   <?php if ($accesoEditarArticulo || $accesoEliminarArticulo): ?> 
         <td>Acciones</td>
       <?php endif; ?> 
	</tr> 
	</thead>
	<tbody>
	<?php while($ver=mysqli_fetch_row($result)): ?> 
	<tr> 
		<td><?php echo $ver[0]; ?></td>
		<td><?php echo $ver[1]; ?></td>
		<td><?php echo $ver[2]; ?></td>
		<td><?php echo $ver[3]; ?></td>  
		<td><?php echo $ver[6]; ?></td> 
		<td style="color: green;font-weight: bold;">
			<?php
				$stock = 0;
				if ($id_bodega) {
					$sqlStock = "SELECT round(cantidad,2) cantidad FROM stock_productos_bodega WHERE id_producto = {$ver[0]} AND id_bodega_principal = $id_bodega LIMIT 1";
					$resStock = mysqli_query($conexion, $sqlStock);
					if ($rowStock = mysqli_fetch_assoc($resStock)) {
						$stock = $rowStock['cantidad'];
					}
				}
				echo $stock;
			?>
		</td>
		<?php if ($accesoVistaPrecio): ?>
		   <td><?php echo $ver[9]; ?></td>
	    <?php endif; ?> 
		<td>
			<?php if (!empty($ver[10])): ?>
				<?php 
			      $imgVer=explode("/", $ver[10]) ; 
			         $imgruta=$imgVer[1]."/".$imgVer[2]."/".$imgVer[3];
			     ?>
				<img width="50" height="50" src="<?php echo $imgruta ?>"
					 style="cursor:pointer;"
					 onmouseover="mostrarVistaPreviaImagen('<?php echo $imgruta ?>', event)"
					 onmouseout="ocultarVistaPreviaImagen()"
				>
			<?php else: ?>
				<span class="text-muted">Sin imagen</span>
			<?php endif; ?>
		</td>
		<td>
		   <?php
			if($ver[11]=="1"){
				echo "ACTIVO";
			}else{
				echo "INACTIVO";
			}
			?>
		</td>
		 <?php if ($accesoEditarArticulo || $accesoEliminarArticulo): ?> 
		<td>  
		  <?php if($ver[11]=="1"){?>
			  <div class="btn-group" style="width: 80px;">  
				   <?php if ($accesoEditarArticulo): ?>
					 <button title="Editar articulo" class="btn btn-warning" onclick="agregaDatosArticulo('<?php echo $ver[0] ?>')" data-toggle="modal" data-target="#abremodalUpdateArticulo"><i class="glyphicon glyphicon-edit" ></i></button> 
				   <?php endif; ?> 
                   <?php if ($accesoEliminarArticulo): ?>
				       <button title="Eliminar articulo" class="btn btn-danger"  onclick="eliminaArticulo('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-remove"></i></button>
		            <?php endif; ?> 
		  </div>  
		  <?php }?>  

		  <?php if($ver[11]=="0"){?>
			  <div class="btn-group"style="width: 80px;">  
				 <?php if ($accesoEliminarArticulo): ?>
                     <button title="Activar articulo"  class="btn btn-info"  onclick="activarArticulo('<?php echo $ver[0] ?>')"><i class="glyphicon glyphicon-check"></i></button>
		          <?php endif; ?> 
			 </div>  
		  <?php }?>   
		</td> 
	  <?php endif; ?> 
	</tr>
	<?php endwhile; ?>
	</tbody>
	</table> 

	<!-- Visualizador de imagen en hover -->
	<div id="vistaPreviaImagen" style="display:none; position:absolute; z-index:9999; border:2px solid #007bff; background:#fff; padding:3px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
		<img id="imagenGrande" src="" style="max-width:520px; max-height:520px;">
	</div>
</div>

<script type="text/javascript">
	$("#tbDatosMaestros").DataTable({
		responsive: true,
		destroy: true,
		order: [[0, 'desc']]
	});

	// Visualizador de imagen grande al pasar el mouse
	function mostrarVistaPreviaImagen(ruta, event){
		var vista = document.getElementById("vistaPreviaImagen");
		var img = document.getElementById("imagenGrande");
		img.src = ruta;
		vista.style.display = "block";
		// Posición cerca del cursor
		vista.style.left = (event.pageX - 630) + "px";
		vista.style.top = (event.pageY - 200) + "px";
	}
	function ocultarVistaPreviaImagen(){
		var vista = document.getElementById("vistaPreviaImagen");
		vista.style.display = "none";
	}
</script>
<style> 
hr {
    border-top: 2px solid #eadfdf;
}
</style>