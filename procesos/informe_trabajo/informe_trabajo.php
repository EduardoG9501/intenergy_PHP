<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/InformeTrabajo.php";

$informe = new InformeTrabajo();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
        case 'actualizar_descripcion':
            $id_informe = $_POST['id_informe'] ?? 0;
            $descripcion = $_POST['descripcion'] ?? '';
            echo json_encode($informe->actualizarDescripcion($id_informe, $descripcion));
            break;
    case 'listar':
        // Permitir filtro por ejecución de obra o por bodega
        $id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;
        $id_ejecucion_obra = $_POST['id_ejecucion_obra'] ?? null;
        echo json_encode($informe->listar($id_bodega, $id_ejecucion_obra));
        break;
    case 'obtener':
        $id = $_POST['id_informe'] ?? 0;
        echo json_encode($informe->obtener($id));
        break;
    case 'guardar':
        $id_usuario = $_SESSION['id_usuario'] ?? 1;
        if (isset($_SESSION['bodega_seleccionada']) && $_SESSION['bodega_seleccionada'] !== '') {
            $_POST['id_bodega_principal'] = $_SESSION['bodega_seleccionada'];
            $_POST['id_bodega_secundaria'] = $_SESSION['bodega_seleccionada'];
        } else {
            $idPrincipal = $_POST['id_bodega_principal'] ?? '';
            $_POST['id_bodega_secundaria'] = $idPrincipal;
        }
        echo json_encode($informe->guardar($_POST, $id_usuario));
        break;
    case 'eliminar':
        $id = $_POST['id_informe'] ?? 0;
        echo json_encode($informe->eliminar($id));
        break;
    case 'listar_bodegas':
        echo json_encode($informe->listarBodegas());
        break;
    case 'listar_empleados':
        echo json_encode($informe->listarEmpleados());
        break;
    case 'listar_ejecuciones':
        echo json_encode($informe->listarEjecuciones());
        break;

    // Empleados internos
    case 'listar_empleados_internos':
        $id_informe = $_POST['id_informe'] ?? 0;
        echo json_encode($informe->listarEmpleadosInternos($id_informe));
        break;
    case 'agregar_empleado_interno':
        $id_informe = $_POST['id_informe'] ?? 0;
        $id_empleado = $_POST['id_empleado'] ?? 0;
        echo json_encode($informe->agregarEmpleadoInterno($id_informe, $id_empleado));
        break;

    case 'editar_empleado_interno':
        $id_informe_empleado = $_POST['id_informe_empleado'] ?? 0;
        $id_empleado = $_POST['id_empleado'] ?? 0;
        echo json_encode($informe->editarEmpleadoInterno($id_informe_empleado, $id_empleado));
        break;

    case 'eliminar_empleado_interno':
        $id_informe_empleado = $_POST['id_informe_empleado'] ?? 0;
        echo json_encode($informe->eliminarEmpleadoInterno($id_informe_empleado));
        break;

    // Empleados externos
    case 'listar_empleados_externos':
        $id_informe = $_POST['id_informe'] ?? 0;
        echo json_encode($informe->listarEmpleadosExternos($id_informe));
        break;
    case 'agregar_empleado_externo':
        $id_informe = $_POST['id_informe'] ?? 0;
        $id_empleado_externo = $_POST['id_empleado_externo'] ?? 0;
        echo json_encode($informe->agregarEmpleadoExterno($id_informe, $id_empleado_externo));
        break;
    case 'editar_empleado_externo':
        $id_informe_empleado_externo = $_POST['id_informe_empleado_externo'] ?? 0;
        $id_empleado_externo = $_POST['id_empleado_externo'] ?? 0;
        echo json_encode($informe->editarEmpleadoExterno($id_informe_empleado_externo, $id_empleado_externo));
        break;
    case 'eliminar_empleado_externo':
        $id_informe_empleado_externo = $_POST['id_informe_empleado_externo'] ?? 0;
        echo json_encode($informe->eliminarEmpleadoExterno($id_informe_empleado_externo));
        break;

    // Artículos
    case 'listar_articulos_combo':
        echo json_encode($informe->listarArticulosCombo());
        break;
    case 'listar_articulos':
        $id_informe = $_POST['id_informe'] ?? 0;
        echo json_encode($informe->listarArticulos($id_informe));
        break;
    case 'agregar_articulo':
        $id_informe = $_POST['id_informe'] ?? 0;
        $id_producto = $_POST['id_producto'] ?? 0;
        $cantidad = $_POST['cantidad'] ?? 1;
        $lote = $_POST['lote'] ?? '';
        echo json_encode($informe->agregarArticulo($id_informe, $id_producto, $cantidad, $lote));
        break;
    case 'editar_articulo':
        $id_informe_articulo = $_POST['id_informe_articulo'] ?? 0;
        $id_informe = $_POST['id_informe'] ?? 0;
        $id_producto = $_POST['id_producto'] ?? 0;
        $cantidad = $_POST['cantidad'] ?? 0;
        $lote = $_POST['lote'] ?? '';
        echo json_encode($informe->editarArticulo($id_informe_articulo, $id_informe, $id_producto, $cantidad, $lote));
        break;
    case 'eliminar_articulo':
        $id_informe_articulo = $_POST['id_informe_articulo'] ?? 0;
        echo json_encode($informe->eliminarArticulo($id_informe_articulo));
        break;

    // Imágenes
    case 'listar_imagenes':
        $id_informe = $_POST['id_informe'] ?? 0;
        echo json_encode($informe->listarImagenes($id_informe));
        break;
    case 'agregar_imagen':
        $id_informe = $_POST['id_informe'] ?? 0;
        $res = [];
        $upload_dir = '../../archivos/';

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (isset($_FILES['archivoImagen'])) {
            $files = $_FILES['archivoImagen'];
            $total = is_array($files['name']) ? count($files['name']) : 1;
            for ($i = 0; $i < $total; $i++) {
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $nombre_unico = uniqid() . '.' . $extension;
                $ruta_relativa = 'archivos/' . $nombre_unico;
                $ruta_destino = $upload_dir . $nombre_unico;

                if (move_uploaded_file($tmp_name, $ruta_destino)) {
                    // Llama al método de la clase
                    $resultado = $informe->agregarImagen($id_informe, $nombre_unico, $ruta_relativa);
                    $res[] = ['success' => $resultado, 'nombre' => $nombre_unico, 'ruta' => $ruta_relativa];
                } else {
                    $res[] = ['success' => false, 'error' => 'No se pudo subir la imagen'];
                }
            }
        } else {
            $res[] = ['success' => false, 'error' => 'No se recibió archivo'];
        }
        echo json_encode($res);
        break;

    case 'editar_imagen':
        $id_informe_imagen = $_POST['id_informe_imagen'] ?? 0;
        $id_informe = $_POST['id_informe'] ?? 0;
        $res = [];
        $upload_dir = '../../archivos/';

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Si hay nueva imagen, la sube y actualiza nombre y ruta
        if (isset($_FILES['archivoImagen'])) {
            $files = $_FILES['archivoImagen'];
            $name = is_array($files['name']) ? $files['name'][0] : $files['name'];
            $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][0] : $files['tmp_name'];
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $nombre_unico = uniqid() . '.' . $extension;
            $ruta_relativa = 'archivos/' . $nombre_unico;
            $ruta_destino = $upload_dir . $nombre_unico;

            if (move_uploaded_file($tmp_name, $ruta_destino)) {
                // Llama al método de la clase para actualizar nombre y ruta
                $resultado = $informe->editarImagen($id_informe_imagen, $id_informe, $nombre_unico, $ruta_relativa);
                $res = ['success' => $resultado, 'nombre' => $nombre_unico, 'ruta' => $ruta_relativa];
            } else {
                $res = ['success' => false, 'error' => 'No se pudo subir la imagen'];
            }
        } else {
            // Si no se sube nueva imagen, solo actualiza otros datos si lo requieres
            $resultado = $informe->editarImagen($id_informe_imagen, $id_informe);
            $res = ['success' => $resultado];
        }
        echo json_encode($res);
        break;
    case 'eliminar_imagen':
        $id_informe_imagen = $_POST['id_informe_imagen'] ?? 0;
        // 1. Obtener la ruta del archivo a eliminar
        $ruta_imagen = $informe->obtenerRutaImagen($id_informe_imagen);  
        $exito = false;
        // 2. Eliminar el archivo físico si existe
        if ($ruta_imagen && file_exists('../../' . $ruta_imagen)) {
            unlink('../../' . $ruta_imagen);
        }
        // 3. Eliminar el registro de la base de datos
        $exito = $informe->eliminarImagen($id_informe_imagen); 
        echo json_encode(['success' => $exito]);
        break;
    case 'listar_ejecuciones_obra':
        echo json_encode($informe->listarOrdenesTrabajo());
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
}
?>