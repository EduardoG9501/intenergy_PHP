<?php 

	class empleados{

		public function agregaEmpleado($datos){
			$c= new conectar();
			$conexion=$c->conexion();

			$idusuario=$_SESSION['iduser'];

			$cedula = trim($datos[1]);
			// Validar si la cédula ya existe
			$sql = "SELECT COUNT(*) FROM empleados WHERE cedula = ?";
			$stmt = $conexion->prepare($sql);
			$stmt->bind_param("s", $cedula);
			$stmt->execute();
			$stmt->bind_result($existe);
			$stmt->fetch();
			$stmt->close();

			if ($existe > 0) {
				return 2; // Cedula repetida
			}

			$sql="INSERT into empleados (id_usuario,
										nombres_apellidos,
										cedula,
										id_genero,
										direccion,
										email,
										telefono,
										fecha_nacimiento,
										id_cargo)
							values ('$idusuario',
									'$datos[0]',
									'$datos[1]',
									'$datos[2]',
									'$datos[3]',
									'$datos[4]',
									'$datos[5]',
									'$datos[6]',
									'$datos[7]')";
			return mysqli_query($conexion,$sql) ? 1 : 0;	
		}

		public function obtenDatosEmpleado($idempleado){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="SELECT id_empleados, 
							id_usuario,
							nombres_apellidos,
							cedula,
							id_genero,
							direccion,
							email,
							telefono,
							fecha_nacimiento,
							id_cargo,
							estado
				from empleados
				where id_empleados='$idempleado'";
				
			$result=mysqli_query($conexion,$sql);
			$ver=mysqli_fetch_row($result);

			$datos=array(
					'id_empleados' => $ver[0], 
					'id_usuario' => $ver[1],
					'nombres_apellidos' => $ver[2],
					'cedula' => $ver[3],
					'id_genero' => $ver[4],
					'direccion' => $ver[5],
					'email' => $ver[6],
					'telefono' => $ver[7], 
					'fecha_nacimiento' => $ver[8],
					'id_cargo' => $ver[9], 
					'estado' => $ver[10]
						);
			return $datos;
		}

		public function actualizaEmpleado($datos){
			$c= new conectar();
			$conexion=$c->conexion();

			$id_empleado = intval($datos[0]);
			$cedula = trim($datos[2]);
			// Validar si la cédula ya existe en otro empleado
			$sql = "SELECT COUNT(*) FROM empleados WHERE cedula = ? AND id_empleados != ?";
			$stmt = $conexion->prepare($sql);
			$stmt->bind_param("si", $cedula, $id_empleado);
			$stmt->execute();
			$stmt->bind_result($existe);
			$stmt->fetch();
			$stmt->close();

			if ($existe > 0) {
				return 2; // Cedula repetida
			}

			$sql="UPDATE empleados set nombres_apellidos='$datos[1]', 
										cedula='$datos[2]',
										id_genero='$datos[3]',
										direccion='$datos[4]',
										email='$datos[5]',
										telefono='$datos[6]',
										fecha_nacimiento='$datos[7]',
										id_cargo='$datos[8]'
								where id_empleados='$datos[0]'";
			return mysqli_query($conexion,$sql) ? 1 : 0;
		}

		public function eliminaEmpleado($idempleado){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE empleados SET estado = 0 where id_empleados ='$idempleado'";

			return mysqli_query($conexion,$sql);
		}

		public function activarEmpleado($idempleado){
			$c= new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE empleados SET estado = 1 where id_empleados ='$idempleado'";
			return mysqli_query($conexion,$sql);
		}

	// Listar empleados activos (id_empleados, nombres_apellidos)
	public function listar() {
		$c= new conectar();
	    $conexion=$c->conexion();
		$sql = "SELECT id_empleados, nombres_apellidos FROM empleados WHERE estado = 1 ORDER BY nombres_apellidos ASC";
		$result = mysqli_query($conexion, $sql);
		$datos = array();
		while ($fila = mysqli_fetch_assoc($result)) {
			$datos[] = $fila;
		}
		return $datos;
	}
	}

 ?>