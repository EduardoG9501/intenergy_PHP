<?php
include '../../clases/Conexion.php';
include '../../clases/Proyecto.php';
$proyecto = new Proyecto();
echo json_encode($proyecto->obtenerProyectos());
