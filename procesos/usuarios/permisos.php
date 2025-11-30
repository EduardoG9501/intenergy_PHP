<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Usuarios.php";

$usuario = new usuarios();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {

    // Obtener permisos actuales del usuario
    case 'listar':
        $id_usuario = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0;
        echo json_encode($usuario->obtenerPermisosUsuario($id_usuario));
        break;

    // Asignar permisos (sobreescribe todos, pero no más de dos iguales)
    case 'asignar':
        $id_usuario = $_POST['id_usuario'] ?? 0;
        $menus = isset($_POST['menus']) ? $_POST['menus'] : [];

        // Validación: no asignar más de dos veces el mismo permiso
        // Solo permite máximo 2 registros únicos por id_menu
        ///$menus = array_slice(array_unique($menus), 0, 2); // máximo dos diferentes

        $result = $usuario->asignarPermisos($id_usuario, $menus);
        echo json_encode(['success' => $result]);
        break;

    // Eliminar permiso individual
    case 'eliminar':
        $id_usuario = $_POST['id_usuario'] ?? 0;
        $id_menu = $_POST['id_menu'] ?? 0;
        $result = $usuario->eliminarPermisoUsuario($id_usuario, $id_menu);
        echo json_encode(['success' => $result]);
        break;
    // Listar todos los menús usando la clase Usuarios
    case 'listar_menus':
        $menus = $usuario->obtenerMenus();
        echo json_encode($menus);
        break;
    // --------- NUEVO: Validar acceso ---------
    case 'validar_acceso':
        // Recibe usuario y nombre del menú
        $usuarioname = $_POST['usuario'] ?? $_GET['usuario'] ?? '';
        $nombreMenu = $_POST['menu'] ?? $_GET['menu'] ?? '';
        $acceso = $usuario->usuarioTieneAcceso($usuarioname, $nombreMenu);
        echo json_encode(['acceso' => $acceso]);
        break;

   // -------- Permisos de bodegas -------- 
   case 'listar_bodegas':
    $id_usuario = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0;
    echo json_encode($usuario->obtenerPermisosBodegaUsuario($id_usuario));
    break;

   case 'asignar_bodegas':
    $id_usuario = $_POST['id_usuario'] ?? 0;
    $bodegas = isset($_POST['bodegas']) ? $_POST['bodegas'] : [];
    $result = $usuario->asignarPermisosBodega($id_usuario, $bodegas);
    echo json_encode(['success' => $result]);
    break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
   }
?>