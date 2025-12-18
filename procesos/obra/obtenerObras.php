<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Obra.php";
$obra = new Obra();
$datos = $obra->obtenerObras();
echo json_encode($datos);
