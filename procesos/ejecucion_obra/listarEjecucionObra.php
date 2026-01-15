<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/EjecucionObra.php";
$ejecucionObra = new EjecucionObra();
echo json_encode($ejecucionObra->listar());
