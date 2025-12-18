<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Proyecto.php";
$proyecto = new Proyecto();
$id = $_POST['id'] ?? 0;
$nombre = $_POST['nombre'] ?? '';
$resultado = $proyecto->actualizaProyecto([$id, $nombre]);
if ($resultado === "EXISTE") {
    echo "EXISTE";
} elseif ($resultado) {
    echo 'ok';
} else {
    echo 'error';
}
