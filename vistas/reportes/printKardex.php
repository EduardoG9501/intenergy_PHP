<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();

// Recibir filtros igual que tablaKardex.php
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$bodega_principal = isset($_GET['bodega_principal']) ? $_GET['bodega_principal'] : '';
$obra = isset($_GET['obra']) ? $_GET['obra'] : '';
$proyecto = isset($_GET['proyecto']) ? $_GET['proyecto'] : '';
$producto = isset($_GET['producto']) ? $_GET['producto'] : '';
$tipo_movimiento = isset($_GET['tipo_movimiento']) ? $_GET['tipo_movimiento'] : '';
$sub_tipo_movimiento = isset($_GET['sub_tipo_movimiento']) ? $_GET['sub_tipo_movimiento'] : '';

// Puedes usar FPDF, TCPDF para generar PDF profesionalmente
// Aquí solo mostramos HTML para imprimir
?>
<html>
<head>
    <title>Imprimir KARDEX</title>
</head>
<body>
<h2>Reporte KARDEX</h2>
<table border="1" width="100%">
    <tr>
        <th>ID ORDEN</th>
        <th>PROYECTO</th>
        <th>NOMBRE DE LA OBRA</th>
        <th>Lugar</th>
        <th>Fecha</th>
        <th>Tipo Movimiento</th>
        <th>Sub Tipo Movimiento</th>
        <th>Articulo</th>
        <th>Cantidad</th>
        <th>#Documento</th>
    </tr>
<?php
$sql = "
SELECT  
    COALESCE(ot.identificador, '') AS id_orden,
    COALESCE(p.nombre, '') AS proyecto,
    COALESCE(ob.nombre, '') AS nombre_obra,
    bop.nombreBodega AS lugar,
    mov.fecha_captura,
    mov.tipo_movimiento,
    mov.sub_tipo_movimiento,
    mov.producto,
    ROUND(mov.cantidad,2) AS cantidad,
    mov.id_movimiento AS documento
FROM stock_productos AS mov
INNER JOIN bodegas bop ON mov.id_bodega_principal = bop.id_bodega
LEFT JOIN solicitud_materiales sm ON mov.no_documento = CONCAT('SOL-', sm.id_solicitud_material)
LEFT JOIN ejecucion_obra eo ON eo.id_ejecucion_obra = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(mov.no_documento, '-BOD-', 1), 'EJEC-', -1) AS UNSIGNED)
    AND mov.no_documento LIKE 'EJEC-%-BOD-%'
LEFT JOIN ordenes_trabajo ot ON ot.id_orden = COALESCE(sm.id_orden, eo.id_orden)
LEFT JOIN proyecto p ON ot.id_proyecto = p.id
LEFT JOIN obra ob ON ot.id_obra = ob.id
WHERE 1=1
";
if($fecha_desde) $sql .= " AND mov.fecha_captura >= '$fecha_desde' ";
if($fecha_hasta) $sql .= " AND mov.fecha_captura <= '$fecha_hasta' ";
if($bodega_principal) $sql .= " AND mov.id_bodega_principal = '$bodega_principal' ";
if($obra) $sql .= " AND ot.id_obra = '" . intval($obra) . "' ";
if($proyecto) $sql .= " AND ot.id_proyecto = '" . intval($proyecto) . "' ";
if($producto) $sql .= " AND mov.id_producto = '$producto' ";
if($tipo_movimiento) $sql .= " AND mov.id_tipo_movimiento = '$tipo_movimiento' ";
if($sub_tipo_movimiento) $sql .= " AND mov.id_sub_tipo_movimiento = '$sub_tipo_movimiento' ";

$sql .= " ORDER BY mov.fecha_captura DESC";
$res = mysqli_query($conexion, $sql);

while($row = mysqli_fetch_assoc($res)){
    echo "<tr>";
    echo "<td>{$row['id_orden']}</td>
        <td>{$row['proyecto']}</td>
        <td>{$row['nombre_obra']}</td>
        <td>{$row['lugar']}</td>
        <td>{$row['fecha_captura']}</td>
        <td>{$row['tipo_movimiento']}</td>
        <td>{$row['sub_tipo_movimiento']}</td>
        <td>{$row['producto']}</td>
        <td>{$row['cantidad']}</td>
        <td>{$row['documento']}</td>";
    echo "</tr>";
}
?>
</table>
<script>
window.print();
</script>
</body>
</html>