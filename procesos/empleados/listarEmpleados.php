<?php
session_start();
require_once "../../clases/Conexion.php";
require_once "../../clases/Empleados.php";

$obj= new empleados();

$data = $obj->listar();
echo json_encode(['success' => true, 'data' => $data]);
