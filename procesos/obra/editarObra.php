<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Obra.php";
$obra = new Obra();
$id = $_POST['id'] ?? 0;
$nombre = $_POST['nombre'] ?? '';
$resultado = $obra->actualizaObra([$id, $nombre]);
if ($resultado === "EXISTE") {
    echo "EXISTE";
} elseif ($resultado) {
    echo 'ok';
} else {
    echo 'error';
}
