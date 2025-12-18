<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Obra.php";
$obra = new Obra();
$nombre = $_POST['nombre'] ?? '';
$resultado = $obra->agregaObra([$nombre]);
if ($resultado === "EXISTE") {
    echo "EXISTE";
} elseif ($resultado) {
    echo 'ok';
} else {
    echo 'error';
}
