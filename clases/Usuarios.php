<?php 

	class usuarios{
		public function registroUsuario($datos){
			$c=new conectar();
			$conexion=$c->conexion();

			$fecha=date('Y-m-d');

			$sql="INSERT into usuarios (nombre,
								apellido,
								email,
								password,
								fechaCaptura)
						values ('$datos[0]',
								'$datos[1]',
								'$datos[2]',
								'$datos[3]',
								'$fecha')";
			return mysqli_query($conexion,$sql);
		}
		public function loginUser($datos){
			$c=new conectar();
			$conexion=$c->conexion();
			$password=sha1($datos[1]);

			$_SESSION['usuario']=$datos[0];
			$_SESSION['iduser']=self::traeID($datos);

			$sql="SELECT * 
					from usuarios 
				where email='$datos[0]'
				and password='$password'
				and estado = 1";
			$result=mysqli_query($conexion,$sql);

			if(mysqli_num_rows($result) > 0){
				return 1;
			}else{
				return 0;
			}
		}
		public function traeID($datos){
			$c=new conectar();
			$conexion=$c->conexion();

			$password=sha1($datos[1]);

			$sql="SELECT id_usuario 
					from usuarios 
					where email='$datos[0]'
					and password='$password'"; 
			 $result=mysqli_query($conexion,$sql); 
			 return mysqli_fetch_row($result)[0]; 
		}

		public function obtenDatosUsuario($idusuario){

			$c=new conectar();
			$conexion=$c->conexion();

			$sql="SELECT id_usuario,
							nombre,
							apellido,
							email
					from usuarios 
					where id_usuario='$idusuario'";
			$result=mysqli_query($conexion,$sql);

			$ver=mysqli_fetch_row($result);

			$datos=array(
						'id_usuario' => $ver[0],
							'nombre' => $ver[1],
							'apellido' => $ver[2],
							'email' => $ver[3]
						);

			return $datos;
		}

		public function actualizaUsuario($datos){
			$c=new conectar();
			$conexion=$c->conexion(); 
			$password=sha1($datos[4]);
 
		   /*Si contraseña viene vacia, signfica que no hay cambio de contraseña y solo actualiza los datos generales del usuario*/ 
           if ($datos[4]==='') {
			$sql="UPDATE usuarios set nombre='$datos[1]',
									apellido='$datos[2]',
									email='$datos[3]'
						where id_usuario='$datos[0]'";
			  return mysqli_query($conexion,$sql);	 
			}
			else{
                $sql="UPDATE usuarios set nombre='$datos[1]',
									apellido='$datos[2]',
									email='$datos[3]',
									password='$password'
						where id_usuario='$datos[0]'";
			  return mysqli_query($conexion,$sql);	 
			}
		}

		public function eliminaUsuario($idusuario){
			$c=new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE usuarios SET Estado = 0
					where id_usuario='$idusuario'";
			return mysqli_query($conexion,$sql);
		}
		public function activarUsuario($idusuario){
			$c=new conectar();
			$conexion=$c->conexion();

			$sql="UPDATE usuarios SET Estado = 1
					where id_usuario='$idusuario'";
			return mysqli_query($conexion,$sql);
		}
		// -------------------------
		// NUEVOS MÉTODOS DE PERMISOS
		// -------------------------

		// Asignar permisos (menús) a usuario (sobreescribe todos los permisos)
		public function asignarPermisos($id_usuario, $menus) {
			$c = new conectar();
			$conexion = $c->conexion();

			// Eliminar permisos anteriores del usuario
			$sqlDel = "DELETE FROM permisos_usuario_menu WHERE id_usuario = '$id_usuario'";
			mysqli_query($conexion, $sqlDel);

			// Insertar nuevos permisos
			if (!empty($menus)) {
				foreach ($menus as $id_menu) {
					$id_menu = intval($id_menu);
					$sqlIns = "INSERT INTO permisos_usuario_menu (id_usuario, id_menu) VALUES ('$id_usuario', '$id_menu')";
					mysqli_query($conexion, $sqlIns);
				}
			}
			return true;
		}

		public function obtenerPermisosUsuario($id_usuario) {
        $c = new conectar();
        $conexion = $c->conexion();

        // Si el usuario es 1:admin (su id_usuario es '1:admin')
        if ($id_usuario === '1') {
         $sqlMenus = "SELECT id_menu FROM menu WHERE estado = 1";
         $resMenus = mysqli_query($conexion, $sqlMenus);
          $permisos = [];
           while ($row = mysqli_fetch_assoc($resMenus)) {
              $permisos[] = $row['id_menu'];
           }
           return $permisos;
        } 
        // Si NO es admin, recupera solo sus permisos
        $sql = "SELECT id_menu FROM permisos_usuario_menu WHERE id_usuario = '$id_usuario'";
        $res = mysqli_query($conexion, $sql);
        $permisos = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $permisos[] = $row['id_menu'];
        }
        return $permisos;
       }

		// Eliminar permiso individual de usuario
		public function eliminarPermisoUsuario($id_usuario, $id_menu) {
			$c = new conectar();
			$conexion = $c->conexion();

			$sql = "DELETE FROM permisos_usuario_menu WHERE id_usuario = '$id_usuario' AND id_menu = '$id_menu'";
			return mysqli_query($conexion, $sql);
		}

        //Obtener la lista de los menu
		public function obtenerMenus() { 
        	$c = new conectar();
	        $conexion = $c->conexion();
            $sql = "SELECT id_menu, menu_padre, menu_hijo FROM menu  where menu_hijo not in ('BACKUP','IVA') ORDER BY menu_padre, menu_hijo";
            $result = mysqli_query($conexion, $sql);

           $menus = array();
           while($row = mysqli_fetch_assoc($result)){
              $menus[] = $row;
           }
          return $menus;
        }
	public function usuarioTieneAcceso($usuario, $nombreMenu) {
    $c = new conectar();
    $conexion = $c->conexion();

    // 1. Obtener id_usuario
    $sqlId = "SELECT id_usuario FROM usuarios WHERE email = '$usuario' and estado = 1 LIMIT 1";
    $resId = mysqli_query($conexion, $sqlId);
    $id_usuario = null;
    if ($row = mysqli_fetch_assoc($resId)) {
        $id_usuario = $row['id_usuario'];
    } else {
        return false;
    }

    // 2. Si es admin, acceso total
    if ($usuario === 'admin') {
        return true;
    }

    // 3. Buscar id_menu por nombre del menú
    $sqlMenu = "SELECT id_menu FROM menu WHERE menu_hijo = '$nombreMenu' AND estado = 1 and menu_hijo not in ('BACKUP','IVA') LIMIT 1";
    $resMenu = mysqli_query($conexion, $sqlMenu);
    $id_menu = null;
    if ($rowMenu = mysqli_fetch_assoc($resMenu)) {
        $id_menu = $rowMenu['id_menu'];
    } else {
        return false;
    }

    // 4. Verificar permiso
    $sqlPermiso = "SELECT 1 FROM permisos_usuario_menu WHERE id_usuario = '$id_usuario' AND id_menu = '$id_menu' LIMIT 1";
    $resPermiso = mysqli_query($conexion, $sqlPermiso);
    if (mysqli_fetch_assoc($resPermiso)) {
        return true;
    } else {
        return false;
    }
   }
   // Asignar permisos de bodegas (sobreescribe todos los permisos de bodegas)
   public function asignarPermisosBodega($id_usuario, $bodegas) {
    $c = new conectar();
    $conexion = $c->conexion();

    // Elimina permisos anteriores
    $sqlDel = "DELETE FROM permiso_bodega WHERE id_usuario = '$id_usuario'";
    mysqli_query($conexion, $sqlDel);

    // Inserta nuevos permisos
    if (!empty($bodegas) && is_array($bodegas)) {
        foreach ($bodegas as $id_bodega) {
            $id_bodega = intval($id_bodega);
            $sqlIns = "INSERT INTO permiso_bodega (id_usuario, id_bodega, estado) VALUES ('$id_usuario', '$id_bodega', 1)";
            mysqli_query($conexion, $sqlIns);
        }
     }
     return true;
    }

   // Obtener permisos de bodegas de un usuario
   public function obtenerPermisosBodegaUsuario($id_usuario) {
    $c = new conectar();
    $conexion = $c->conexion();
    $res = mysqli_query($conexion, "SELECT id_bodega FROM permiso_bodega WHERE id_usuario = '$id_usuario' AND estado = 1");
    $ids = [];
    while($row = mysqli_fetch_assoc($res)) {
        $ids[] = $row['id_bodega'];
    }
    return $ids;
   }
  } 
 ?>