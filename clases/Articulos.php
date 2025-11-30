<?php 
	class articulos{
		public function agregaImagen($datos){
			$c=new conectar();
			$conexion=$c->conexion();

			$fecha=date('Y-m-d');

			$sql="INSERT into imagenes (id_categoria,
										nombre,
										ruta,
										fechaSubida)
							values ('$datos[0]',
									'$datos[1]',
									'$datos[2]',
									'$fecha')";
			$result=mysqli_query($conexion,$sql);

			return mysqli_insert_id($conexion);
		}

		// NUEVO: Validar nombre único
		public function articuloExisteNombre($nombre, $excludeId = null){
			$c=new conectar();
			$conexion=$c->conexion();
			$sql = "SELECT id_producto FROM articulos WHERE LTRIM(RTRIM(nombre)) = LTRIM(RTRIM('$nombre'))";
			if ($excludeId) {
				$sql .= " AND id_producto != '$excludeId'";
			}
			$result = mysqli_query($conexion, $sql);
			return mysqli_num_rows($result) > 0;
		}

		// NUEVO: Validar código de barra único
		public function articuloExisteCodigoBarra($codigobarra, $excludeId = null){
			$c=new conectar();
			$conexion=$c->conexion();
			$sql = "SELECT id_producto FROM articulos WHERE codigobarra = '$codigobarra'";
			if ($excludeId) {
				$sql .= " AND id_producto != '$excludeId'";
			}
			$result = mysqli_query($conexion, $sql);
			return mysqli_num_rows($result) > 0;
		}

		public function insertaArticulo($datos){
			$c=new conectar();
			$conexion=$c->conexion();

			$fecha=date('Y-m-d');

			$sql="INSERT into articulos (id_categoria,
										id_imagen,
										id_usuario,
										nombre,
										descripcion,
										cantidad,
										precio,
										id_subcategoria,
										codigobarra,
										id_proveedor,
										referencia,
										fechaCaptura) 
							values ('$datos[0]',
									'$datos[1]',
									'$datos[2]',
									LTRIM(RTRIM('$datos[3]')),
									'$datos[4]',
									'$datos[5]',
									'$datos[6]',
									'$datos[7]',
									LTRIM(RTRIM('$datos[8]')),
									'$datos[9]',
									'$datos[10]',
									'$fecha')";
			return mysqli_query($conexion,$sql);
		}
		public function obtenDatosArticulo($idarticulo){
			$c=new conectar();
			$conexion=$c->conexion();

			$sql="SELECT art.id_producto, 
						art.id_categoria, 
						art.nombre,
						art.descripcion,
						art.cantidad,
						art.precio,
						art.id_subcategoria,
						art.codigobarra,
						art.id_proveedor,
						art.referencia,
						img.ruta
				from articulos art
				inner join imagenes as img  on  art.id_imagen=img.id_imagen
				where id_producto='$idarticulo'";
			$result=mysqli_query($conexion,$sql);

			$ver=mysqli_fetch_row($result);

			$datos=array(
					"id_producto" => $ver[0],
					"id_categoria" => $ver[1],
					"nombre" => $ver[2],
					"descripcion" => $ver[3],
					"cantidad" => $ver[4],
					"precio" => $ver[5],
					"id_subcategoria" => $ver[6],
					"codigobarra" => $ver[7],
					"id_proveedor" => $ver[8],
					"referencia" => $ver[9],
					"ruta" => $ver[10]
						);

			return $datos;
		} 
		public function actualizaArticulo($datos){
			$c=new conectar();
			$conexion=$c->conexion();

			/*Producto con nueva imagen*/
			if ($datos[10] > 0)
			{
               $sql="UPDATE articulos set id_categoria='$datos[1]', 
										nombre='$datos[2]',
										descripcion='$datos[3]',
										cantidad='$datos[4]',
										precio='$datos[5]',
										id_subcategoria='$datos[6]',
										codigobarra='$datos[7]',
										id_proveedor='$datos[8]',
										referencia='$datos[9]',
										id_imagen ='$datos[10]'
				where id_producto='$datos[0]'";
				return mysqli_query($conexion,$sql);
			}
			else{
				/*Producto sin cambio de imagen*/
				$sql="UPDATE articulos set id_categoria='$datos[1]', 
										nombre='$datos[2]',
										descripcion='$datos[3]',
										cantidad='$datos[4]',
										precio='$datos[5]',
										id_subcategoria='$datos[6]',
										codigobarra='$datos[7]',
										id_proveedor='$datos[8]',
										referencia='$datos[9]' 
						where id_producto='$datos[0]'";
				
				return mysqli_query($conexion,$sql);
			}  
		} 
		public function eliminaArticulo($idarticulo){
			$c= new conectar();
			$conexion=$c->conexion();  

			$sql="UPDATE articulos set estado=0
				  	where id_producto='$idarticulo'";

			echo mysqli_query($conexion,$sql);  
		}
        public function activarArticulo($idarticulo){
			$c= new conectar();
			$conexion=$c->conexion();  

			$sql="UPDATE articulos set estado=1
				  	where id_producto='$idarticulo'";

			echo mysqli_query($conexion,$sql); 
		}
		public function eliminarImagen($idarticulo){
			$c=new conectar();
			$conexion=$c->conexion();
           
			/*Eliminamos la imagen actual del articulo*/
			$idimagen=self::obtenIdImg($idarticulo);
            $ruta=self::obtenRutaImagen($idimagen);
           
		   $sql="DELETE from imagenes where id_imagen='$idimagen'";
		   $result=mysqli_query($conexion,$sql);
		   
		   if($result){
			if(unlink($ruta))
			 {
				return 1;
			 }
		  } 
		}
		public function obtenIdImg($idProducto){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT id_imagen 
					from articulos 
					where id_producto='$idProducto'";
			$result=mysqli_query($conexion,$sql);

			return mysqli_fetch_row($result)[0];
		}
		public function obtenRutaImagen($idImg){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT ruta 
					from imagenes 
					where id_imagen='$idImg'";

			$result=mysqli_query($conexion,$sql);

			return mysqli_fetch_row($result)[0];
		} 
		public function filtrarArticulobynombre($filtro){
			$c=new conectar();
			$conexion=$c->conexion();
            
			/*Si aplica filtro si tiene resultados*/
			if ($filtro !== "*" && $filtro !== "")
			{ 
				$sql="SELECT art.id_producto,   
						art.nombre 
				from articulos art 
				left join proveedor as prov  on art.id_proveedor=prov.id_proveedor
				where nombre like '%$filtro%' or descripcion like '%$filtro%' or codigobarra like '%$filtro%' or prov.razon_social like '%$filtro%'";
		
			}
			else{
				  $sql="SELECT id_producto,   
					     	nombre 
				from articulos";		
			}  
            $result   = mysqli_query($conexion,$sql);
			$jsonData = array();

            if(mysqli_num_rows($result) > 0){  

             while($row = $result->fetch_row()) {
                  $rows[]=array(
					         "id" => $row[0], 
					         "name" => $row[1]
						);
              }	 
              $json = json_encode($rows);    
              return $json ; 
		} 
	}
	
	public function obtenerprecioarticulo($idarticulo){
			$c=new conectar();
			$conexion=$c->conexion();
			$sql = "SELECT precio FROM articulos WHERE id_producto = '$idarticulo'"; 
			$result = mysqli_query($conexion, $sql);
			if(mysqli_num_rows($result) > 0){
				$row = mysqli_fetch_row($result);
				// Devuelve el precio como valor (float/string)
				return $row[0];
			} else {
				return 0; // Si no hay registro, retorna 0
			}
		}
 }
 ?>