<?php
    require_once "../../clases/Conexion.php";
    require_once "../../clases/Bodegas.php";

$bodega = new bodegas();
echo json_encode($bodega->listarpermisos());
?>