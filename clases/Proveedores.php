<?php 

	class proveedores{

		public function agregaProveedor($datos){
			$c= new conectar();
			$conexion=$c->conexion();

			$idusuario=$_SESSION['iduser'];

			$sql="INSERT into proveedor (id_usuario,
										tipo_identificacion,
										numero_identificacion,
										razon_social,
										direccion,
										telefono,
										email,
										nombre_comercial,
										tipo_contribuyente,
										agente_retencion)
							values ('$idusuario',
									'$datos[0]',
									'$datos[1]',
									'$datos[2]',
									'$datos[3]',
									'$datos[4]',
									'$datos[5]',
									'$datos[6]',
									'$datos[7]',
									'$datos[8]')";
			return mysqli_query($conexion,$sql);	
		}

		public function obtenDatosProveedor($idproveedor){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT id_proveedor, 
							id_usuario,
							tipo_identificacion,
							numero_identificacion,
							razon_social,
							direccion,
							telefono,
							email,
							nombre_comercial,
							tipo_contribuyente,
							agente_retencion,
							estado
				from proveedor
				where id_proveedor='$idproveedor'";
				
			$result=mysqli_query($conexion,$sql);
			$ver=mysqli_fetch_row($result);

			$datos=array(
					'id_proveedor' => $ver[0], 
					'id_usuario' => $ver[1],
					'tipo_identificacion' => $ver[2],
					'numero_identificacion' => $ver[3],
					'razon_social' => $ver[4],
					'direccion' => $ver[5],
					'telefono' => $ver[6],
					'email' => $ver[7],
					'nombre_comercial' => $ver[8],
					'tipo_contribuyente' => $ver[9],
					'agente_retencion' => $ver[10],
					'estado' => $ver[11]
						);
			return $datos;
		}

		public function actualizaProveedor($datos){
			$c= new conectar();
			$conexion=$c->conexion();
			$sql="UPDATE proveedor set tipo_identificacion='$datos[1]',
										numero_identificacion='$datos[2]',
										razon_social='$datos[3]',
										direccion='$datos[4]',
										telefono='$datos[5]',
										email='$datos[6]',
										nombre_comercial='$datos[7]',
										tipo_contribuyente='$datos[8]',
										agente_retencion='$datos[9]' 
								where id_proveedor='$datos[0]'";
			return mysqli_query($conexion,$sql);
		}

		public function eliminaProveedor($idproveedor){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE proveedor SET Estado = 0 where id_proveedor ='$idproveedor'";

			return mysqli_query($conexion,$sql);
		}
		public function activarProveedor($idproveedor){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE proveedor SET Estado = 1 where id_proveedor ='$idproveedor'";

			return mysqli_query($conexion,$sql);
		}

		// --- Validación de duplicados ---
		public function existeDuplicadoProveedor($razonSocial, $nombreComercial, $idProveedor = 0) {
			$c = new conectar();
			$conexion = $c->conexion();

			// Validar Razón Social (solo proveedores activos)
			if ($idProveedor > 0) {
				$sql1 = "SELECT COUNT(*) FROM proveedor WHERE LOWER(razon_social) = LOWER(?) AND estado = 1 AND id_proveedor != ?";
				$stmt1 = $conexion->prepare($sql1);
				$stmt1->bind_param("si", $razonSocial, $idProveedor);
			} else {
				$sql1 = "SELECT COUNT(*) FROM proveedor WHERE LOWER(razon_social) = LOWER(?) AND estado = 1";
				$stmt1 = $conexion->prepare($sql1);
				$stmt1->bind_param("s", $razonSocial);
			}
			$stmt1->execute();
			$stmt1->bind_result($existeRS);
			$stmt1->fetch();
			$stmt1->close();

			// Validar Nombre Comercial (solo proveedores activos)
			if ($idProveedor > 0) {
				$sql2 = "SELECT COUNT(*) FROM proveedor WHERE LOWER(nombre_comercial) = LOWER(?) AND estado = 1 AND id_proveedor != ?";
				$stmt2 = $conexion->prepare($sql2);
				$stmt2->bind_param("si", $nombreComercial, $idProveedor);
			} else {
				$sql2 = "SELECT COUNT(*) FROM proveedor WHERE LOWER(nombre_comercial) = LOWER(?) AND estado = 1";
				$stmt2 = $conexion->prepare($sql2);
				$stmt2->bind_param("s", $nombreComercial);
			}
			$stmt2->execute();
			$stmt2->bind_result($existeNC);
			$stmt2->fetch();
			$stmt2->close();

			// 4 = ambos, 2 = razón social, 3 = nombre comercial, 0 = ninguno
			if ($existeRS > 0 && $existeNC > 0) return 4;
			if ($existeRS > 0) return 2;
			if ($existeNC > 0) return 3;
			return 0;
		}

	}

 ?>