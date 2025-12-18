<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/Obra.php";
$obra = new Obra();
$id = $_POST['id'] ?? 0;
$resultado = $obra->eliminaObra($id);
echo $resultado ? 'ok' : 'error';
