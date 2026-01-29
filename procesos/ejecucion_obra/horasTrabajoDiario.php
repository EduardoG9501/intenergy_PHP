<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$ejecucionObra = new EjecucionObra();

switch ($accion) {
        case 'getFeriado':
            $id_ejecucion_obra = intval($_GET['id_ejecucion_obra'] ?? 0);
            $row = $ejecucionObra->getEjecucionObra($id_ejecucion_obra);
            if ($row) {
                echo json_encode(['success' => true, 'feriado' => $row['feriado']]);
            } else {
                echo json_encode(['success' => false]);
            }
            break;
    case 'listar':
        $id_ejecucion_obra = intval($_GET['id_ejecucion_obra'] ?? 0);
        $data = $ejecucionObra->listarHorasTrabajoDiario($id_ejecucion_obra);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
    case 'agregar':
        $id_ejecucion_obra = intval($_POST['id_ejecucion_obra']);
        $id_empleado = intval($_POST['id_empleado']);
        $hora_entrada = $_POST['hora_entrada'];
        $hora_salida = $_POST['hora_salida'];
        $feriado = intval($_POST['feriado']);
        $ok = $ejecucionObra->agregarHorasTrabajoDiario($id_ejecucion_obra, $id_empleado, $hora_entrada, $hora_salida, $feriado, null);
        echo json_encode(['success' => $ok]);
        break;
    case 'editar':
        $id = intval($_POST['id']);
        $hora_entrada = $_POST['hora_entrada'];
        $hora_salida = $_POST['hora_salida'];
        $feriado = intval($_POST['feriado']);
        $id_empleado = isset($_POST['id_empleado']) ? intval($_POST['id_empleado']) : null;
        $ok = $ejecucionObra->editarHorasTrabajoDiario($id, $hora_entrada, $hora_salida, $feriado, null, $id_empleado);
        echo json_encode(['success' => $ok]);
        break;
    case 'eliminar':
        $id = intval($_POST['id']);
        $ok = $ejecucionObra->eliminarHorasTrabajoDiario($id);
        echo json_encode(['success' => $ok]);
        break;
    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
