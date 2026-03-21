<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();

$id_proyecto = isset($_GET['id_proyecto']) ? intval($_GET['id_proyecto']) : 0;

$sql = "SELECT DISTINCT o.id, o.nombre
        FROM obra o
        LEFT JOIN ordenes_trabajo ot ON ot.id_obra = o.id AND ot.estado = 1
        WHERE o.estado = 1";

if ($id_proyecto > 0) {
    $sql .= " AND ot.id_proyecto = $id_proyecto";
}

$sql .= " ORDER BY o.nombre ASC";

$res = mysqli_query($conexion, $sql);

$options = '<option value="">Todos</option>';
while ($row = mysqli_fetch_assoc($res)) {
    $options .= "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
}

echo $options;
?>