<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Proyecto.php";
$proyecto = new Proyecto();
$nombre = $_POST['nombre'] ?? '';
$resultado = $proyecto->agregaProyecto([$nombre]);
if ($resultado === "EXISTE") {
    echo "EXISTE";
} elseif ($resultado) {
    echo 'ok';
} else {
    // Mostrar error de MySQL para depuración
    $c = new conectar();
    $conexion2 = $c->conexion();
    echo mysqli_error($conexion2);
}
