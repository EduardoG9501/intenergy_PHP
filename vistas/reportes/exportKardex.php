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

// Corrección: Exportar como CSV para que Excel lo abra correctamente
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=kardex_" . date("Ymd_His") . ".csv");

// Encabezados de columnas
echo "Fecha,Bodega Principal,Bodega Secundaria,Tipo Movimiento,Sub Tipo Movimiento,Producto,Cantidad,Precio,Subtotal,Descuento,IVA,Total,No Documento\n";

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

// Imprimir las filas como CSV
while($row = mysqli_fetch_assoc($res)){
    // Escapar las comas y dobles comillas
    $values = [
        $row['fecha_captura'],
        $row['BodegaPrincipal'],
        $row['BodegaSecundaria'],
        $row['tipo_movimiento'],
        $row['sub_tipo_movimiento'],
        $row['producto'],
        $row['cantidad'],
        $row['precio'],
        $row['subtotal'],
        $row['decuento'],
        $row['iva'],
        $row['total'],
        $row['no_documento']
    ];
    // Si algún valor contiene una coma o comilla, lo encerramos entre comillas dobles y escapamos las comillas internas
    foreach ($values as &$val) {
        $val = str_replace('"', '""', $val); // Escapar comillas dobles
        if (strpos($val, ',') !== false || strpos($val, '"') !== false) {
            $val = '"' . $val . '"';
        }
    }
    echo implode(',', $values) . "\n";
}
?>