<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();

// Recibir filtros igual que tablaKardex.php
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$bodega_principal = isset($_GET['bodega_principal']) ? $_GET['bodega_principal'] : '';
$bodega_secundaria = isset($_GET['bodega_secundaria']) ? $_GET['bodega_secundaria'] : '';
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
        <th>Fecha</th>
        <th>Lugar</th>
        <th>Ubicaciòn</th>
        <th>Tipo Movimiento</th>
        <th>Sub Tipo Movimiento</th>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio</th>
        <th>Subtotal</th>
        <th>Descuento</th>
        <th>IVA</th>
        <th>Total</th>
        <th>No Documento</th>
    </tr>
<?php
$sql = "
SELECT  
    mov.tipo_movimiento,
    mov.sub_tipo_movimiento,
    bop.nombreBodega AS BodegaPrincipal,
    bos.nombreBodega AS BodegaSecundaria,
    mov.no_documento,
    mov.producto,
    ROUND(mov.precio,2) AS precio,
    ROUND(mov.cantidad,2) AS cantidad,
    ROUND(mov.subtotal,2) AS subtotal,
    ROUND(mov.decuento,2) AS decuento,
    ROUND(mov.iva,2) AS iva,
    ROUND(mov.total,2) AS total,
    mov.fecha_captura
FROM stock_productos AS mov
INNER JOIN bodegas bop ON mov.id_bodega_principal = bop.id_bodega
INNER JOIN bodegas bos ON mov.id_bodega_secundaria = bos.id_bodega
WHERE 1=1
";
if($fecha_desde) $sql .= " AND mov.fecha_captura >= '$fecha_desde' ";
if($fecha_hasta) $sql .= " AND mov.fecha_captura <= '$fecha_hasta' ";
if($bodega_principal) $sql .= " AND mov.id_bodega_principal = '$bodega_principal' ";
if($bodega_secundaria) $sql .= " AND mov.id_bodega_secundaria = '$bodega_secundaria' ";
if($producto) $sql .= " AND mov.id_producto = '$producto' ";
if($tipo_movimiento) $sql .= " AND mov.id_tipo_movimiento = '$tipo_movimiento' ";
if($sub_tipo_movimiento) $sql .= " AND mov.id_sub_tipo_movimiento = '$sub_tipo_movimiento' ";

$sql .= " ORDER BY mov.fecha_captura DESC";
$res = mysqli_query($conexion, $sql);

while($row = mysqli_fetch_assoc($res)){
    echo "<tr>";
    echo "<td>{$row['fecha_captura']}</td>
        <td>{$row['BodegaPrincipal']}</td>
        <td>{$row['BodegaSecundaria']}</td>
        <td>{$row['tipo_movimiento']}</td>
        <td>{$row['sub_tipo_movimiento']}</td>
        <td>{$row['producto']}</td>
        <td>{$row['cantidad']}</td>
        <td>{$row['precio']}</td>
        <td>{$row['subtotal']}</td>
        <td>{$row['decuento']}</td>
        <td>{$row['iva']}</td>
        <td>{$row['total']}</td>
        <td>{$row['no_documento']}</td>";
    echo "</tr>";
}
?>
</table>
<script>
window.print();
</script>
</body>
</html>