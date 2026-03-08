<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Reportes.php";

$reportes = new Reporte();
$conexionObj = new conectar();
$conexion = $conexionObj->conexion();
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$filtros = isset($_POST['filtros']) ? $_POST['filtros'] : [];

switch ($accion) {
    case 'materiales':
        echo json_encode($reportes->reporteMaterialesEjecucionObra($filtros));
        break;

    case 'horas':
        echo json_encode($reportes->reporteHorasTrabajoEjecucionObra($filtros));
        break;

    case 'informes':
        echo json_encode($reportes->reporteInformesDiariosEjecucionObra($filtros));
        break;

    case 'listar_proyectos':
        echo json_encode($reportes->listarProyectos());
        break;

    case 'listar_obras':
        $proyecto = isset($_POST['proyecto']) ? $_POST['proyecto'] : '';
        echo json_encode($reportes->listarObras($proyecto));
        break;

    case 'listar_bodegas':
        echo json_encode($reportes->listarBodegas());
        break;

    case 'obtener_liquidacion':
        echo json_encode($reportes->obtenerLiquidacionTrabajo($filtros));
        break;

    case 'guardar_liquidacion':
        if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'msg' => 'Sesión no válida. Inicie sesión nuevamente.']);
            break;
        }

        $usuarioSesion = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
        $sqlUsuario = "SELECT id_usuario FROM usuarios WHERE email = '$usuarioSesion' LIMIT 1";
        $rsUsuario = mysqli_query($conexion, $sqlUsuario);
        $rowUsuario = $rsUsuario ? mysqli_fetch_assoc($rsUsuario) : null;
        $idUsuario = $rowUsuario ? intval($rowUsuario['id_usuario']) : 0;

        if ($idUsuario <= 0) {
            echo json_encode(['success' => false, 'msg' => 'No se pudo identificar el usuario de la sesión.']);
            break;
        }

        $filtrosGuardar = [
            'proyecto' => isset($_POST['proyecto']) ? $_POST['proyecto'] : '',
            'obra' => isset($_POST['obra']) ? $_POST['obra'] : '',
            'bodega' => isset($_POST['bodega']) ? $_POST['bodega'] : '',
            'fecha_inicial' => isset($_POST['fecha_inicial']) ? $_POST['fecha_inicial'] : '',
            'fecha_final' => isset($_POST['fecha_final']) ? $_POST['fecha_final'] : ''
        ];

        $manoObra = isset($_POST['mano_obra']) ? $_POST['mano_obra'] : '';
        $importante = isset($_POST['importante']) ? $_POST['importante'] : '';

        $resultado = $reportes->guardarLiquidacionTrabajo($filtrosGuardar, $manoObra, $importante, $idUsuario);

        if (!empty($resultado['success']) && !empty($resultado['id_liquidacion_trabajo']) && isset($_FILES['imagenes'])) {
            $uploadDir = '../../archivos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $files = $_FILES['imagenes'];
            $total = is_array($files['name']) ? count($files['name']) : 1;
            $imagenesGuardadas = [];

            for ($i = 0; $i < $total; $i++) {
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

                if ($error !== UPLOAD_ERR_OK || empty($name)) {
                    continue;
                }

                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $nombreUnico = uniqid('liq_', true) . ($extension ? '.' . $extension : '');
                $rutaRelativa = 'archivos/' . $nombreUnico;
                $rutaDestino = $uploadDir . $nombreUnico;

                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $resImg = $reportes->agregarImagenLiquidacionTrabajo(intval($resultado['id_liquidacion_trabajo']), $nombreUnico, $rutaRelativa);
                    if (!empty($resImg['success'])) {
                        $imagenesGuardadas[] = [
                            'id_liquidacion_trabajo_imagen' => $resImg['id_liquidacion_trabajo_imagen'],
                            'nombre' => $nombreUnico,
                            'ruta' => $rutaRelativa
                        ];
                    }
                }
            }

            $resultado['imagenes_guardadas'] = $imagenesGuardadas;
        }

        if (empty($resultado['success']) && !empty($resultado['error'])) {
            $resultado['msg'] = 'No se pudo guardar la liquidación: ' . $resultado['error'];
        }

        echo json_encode($resultado);
        break;

    case 'eliminar_imagen_liquidacion':
        $idImagen = isset($_POST['id_liquidacion_trabajo_imagen']) ? intval($_POST['id_liquidacion_trabajo_imagen']) : 0;
        if ($idImagen <= 0) {
            echo json_encode(['success' => false, 'msg' => 'Imagen inválida']);
            break;
        }

        $ruta = $reportes->obtenerRutaImagenLiquidacionTrabajo($idImagen);
        $ok = $reportes->eliminarImagenLiquidacionTrabajo($idImagen);

        if ($ok && $ruta && file_exists('../../' . $ruta)) {
            @unlink('../../' . $ruta);
        }

        echo json_encode(['success' => $ok]);
        break;

    default:
        echo json_encode([]);
        break;
}
