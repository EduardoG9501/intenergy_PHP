<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();

$id_tipo = isset($_GET['id_tipo_movimiento']) ? $_GET['id_tipo_movimiento'] : '';

$sql = "SELECT id_sub_tipo_movimiento, sub_tipo 
        FROM sub_tipo_movimiento 
        WHERE estado=1 ";

if($id_tipo) {
    $sql .= " AND id_tipo_movimiento = '".intval($id_tipo)."' ";
}

$sql .= " ORDER BY sub_tipo ASC";

$res = mysqli_query($conexion, $sql);

$options = '<option value="">Seleccione...</option>';
while($row = mysqli_fetch_assoc($res)){
    $options .= "<option value='{$row['id_sub_tipo_movimiento']}'>{$row['sub_tipo']}</option>";
}
echo $options;
?>