<?php
require_once "../../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();

// Recibir filtros
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$bodega_principal = isset($_GET['bodega_principal']) ? $_GET['bodega_principal'] : '';
$bodega_secundaria = isset($_GET['bodega_secundaria']) ? $_GET['bodega_secundaria'] : '';
$producto = isset($_GET['producto']) ? $_GET['producto'] : '';
$tipo_movimiento = isset($_GET['tipo_movimiento']) ? $_GET['tipo_movimiento'] : '';
$sub_tipo_movimiento = isset($_GET['sub_tipo_movimiento']) ? $_GET['sub_tipo_movimiento'] : '';

// Construir query dinámica
$sql = "
SELECT  
    mov.fecha_captura,
    bop.nombreBodega AS BodegaPrincipal,
    bos.nombreBodega AS BodegaSecundaria,
    mov.tipo_movimiento,
    mov.sub_tipo_movimiento,
    mov.producto,
    ROUND(mov.precio,2) AS precio,
    ROUND(mov.cantidad,2) AS cantidad,
    ROUND(mov.subtotal,2) AS subtotal,
    ROUND(mov.decuento,2) AS decuento,
    ROUND(mov.iva,2) AS iva,
    ROUND(mov.total,2) AS total,
    mov.no_documento
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

// Tabla HTML
?>
<table id="tablaKardex" class="table table-bordered table-striped"> 
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Ubicaciòn</th>
            <th>Tipo Movimiento</th>
            <th>Sub Tipo Movimiento</th>
            <th>Producto</th>
            <th>Precio</th>    <!-- Precio primero -->
            <th>Cantidad</th>  <!-- Cantidad después -->
            <th>Subtotal</th>
            <th>Descuento</th>
            <th>IVA</th>
            <th>Total</th>
            <th>No Documento</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?php echo $row['fecha_captura']; ?></td>
            <td><?php echo $row['BodegaPrincipal']; ?></td>
            <td><?php echo $row['BodegaSecundaria']; ?></td>
            <td><?php echo $row['tipo_movimiento']; ?></td>
            <td><?php echo $row['sub_tipo_movimiento']; ?></td>
            <td><?php echo $row['producto']; ?></td>
            <td><?php echo $row['precio']; ?></td>      <!-- Precio primero -->
            <td><?php echo $row['cantidad']; ?></td>    <!-- Cantidad después -->
            <td><?php echo $row['subtotal']; ?></td>
            <td><?php echo $row['decuento']; ?></td>
            <td><?php echo $row['iva']; ?></td>
            <td><?php echo $row['total']; ?></td>
            <td><?php echo $row['no_documento']; ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table> 
<script type="text/javascript">
 $(document).ready(function(){
 let $tabla = $('#tablaKardex'); 
   $tabla.DataTable({
            responsive: true,
            destroy: true,
            order: [[0, 'desc']]
        }); 
    });  
</script>