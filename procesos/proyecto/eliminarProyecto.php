<?php
include '../../clases/Conexion.php';
include '../../clases/Proyecto.php';
$proyecto = new Proyecto();
$id = $_POST['id'] ?? 0;
if ($proyecto->eliminaProyecto($id)) {
    echo 'ok';
} else {
    echo 'error';
}
