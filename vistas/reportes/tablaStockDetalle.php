<?php 
	require_once "../../clases/Conexion.php";
	$c= new conectar();
	$conexion=$c->conexion();
	
	$varticuloSelect = $_GET['varticuloSelect'];
	$valmacenSelect = $_GET['valmacenSelect'];
	$valmacenSelectSecundaria = $_GET['valmacenSelectSecundaria'];
	$vfinicio = $_GET['vfinicio'];
	$vffin = $_GET['vffin'];

	$varticuloSelect = str_replace(["'", "\\"], "", $varticuloSelect);
	$valmacenSelect = str_replace(["'", "\\"], "", $valmacenSelect);
	$valmacenSelectSecundaria = str_replace(["'", "\\"], "", $valmacenSelectSecundaria);

	// Ordena por producto para agrupar en el mismo grid
	$sql = "SELECT  
	    tipo_movimiento,
	    sub_tipo_movimiento,
	    bop.nombreBodega AS BodegaPrincipal,
	    bos.nombreBodega AS BodegaSecundaria,
	    no_documento,
	    producto,
	    ROUND(precio,2) AS precio,
	    ROUND(cantidad,2) AS cantidad,
	    ROUND(subtotal,2) AS subtotal,
	    ROUND(decuento,2) AS decuento,
	    ROUND(iva,2) AS iva,
	    ROUND(total,2) AS total,
	    fecha_captura
	FROM stock_productos AS mov
	INNER JOIN bodegas bop ON mov.id_bodega_principal = bop.id_bodega
	INNER JOIN bodegas bos ON mov.id_bodega_secundaria = bos.id_bodega
	WHERE 1=1";

	if ((int)$varticuloSelect !== 0) {
	    $sql .= " AND mov.id_producto = '" . mysqli_real_escape_string($conexion, $varticuloSelect) . "'";
	}
	if ((int)$valmacenSelect !== 0) {
	    $sql .= " AND mov.id_bodega_principal = '" . mysqli_real_escape_string($conexion, $valmacenSelect) . "'";
	}
	if ((int)$valmacenSelectSecundaria !== 0) {
	    $sql .= " AND mov.id_bodega_secundaria = '" . mysqli_real_escape_string($conexion, $valmacenSelectSecundaria) . "'";
	}
	if (!empty($vfinicio) && !empty($vffin)) {
	    $vfinicio = str_replace(["'", "\\"], "", $vfinicio);
	    $vffin = str_replace(["'", "\\"], "", $vffin);
	    $sql .= " AND DATE(mov.fecha_captura) BETWEEN '" . mysqli_real_escape_string($conexion, $vfinicio) . "' AND '" . mysqli_real_escape_string($conexion, $vffin) . "'";
	}
	$sql .= " ORDER BY producto ASC, fecha_captura DESC";

	$resultStock = mysqli_query($conexion, $sql);

	// Variables para totales generales
	$totalGenCantidad = $totalGenSubtotal = $totalGenDescuento = $totalGenIva = $totalGenTotal = 0;
?>  
<div class="container p-3 my-5 bg-light border border-primary">
	<hr>
	<table id="tbDatosStock" class="table table-striped nowrap" style="width:100%">
		<caption><hash class="hash-custom">STOCK DE ARTICULOS</hash></caption>
		<thead>
			<tr>
				<th>Tipo Mov</th>
				<th>Sub Tipo Mov</th>
				<th>Lugar</th>
				<th>Ubicaciòn</th>
				<th>No.Doc</th>
				<th>Producto</th>
				<th>Precio</th>
				<th>Cantidad</th>
				<th>Subtotal</th>
				<th>Descuento</th>
				<th>Iva</th>
				<th>Total</th>
				<th>Fecha</th>
			</tr>
		</thead>
		<tbody>
<?php
$lastProducto = null;
$itemEntradaCantidad = $itemSalidaCantidad = 0;
$itemEntradaSubtotal = $itemSalidaSubtotal = 0;
$itemEntradaDescuento = $itemSalidaDescuento = 0;
$itemEntradaIva = $itemSalidaIva = 0;
$itemEntradaTotal = $itemSalidaTotal = 0;

$totalGenCantidad = $totalGenSubtotal = $totalGenDescuento = $totalGenIva = $totalGenTotal = 0;

// Para controlar el bloque de detalles por producto
$movimientosPorProducto = [];

while($ver = mysqli_fetch_row($resultStock)):
    $productoActual = $ver[5];

    // Si cambia el producto y no es la primera fila, imprime primero los movimientos y luego la fila de totales
    if ($lastProducto !== null && $productoActual !== $lastProducto) {
        foreach ($movimientosPorProducto as $row) {
            echo $row;
        }
        // Imprime la fila de totales por producto (sombreado celeste)
        $itemCantidad  = $itemEntradaCantidad - $itemSalidaCantidad;
        $itemSubtotal  = $itemEntradaSubtotal - $itemSalidaSubtotal;
        $itemDescuento = $itemEntradaDescuento - $itemSalidaDescuento;
        $itemIva       = $itemEntradaIva - $itemSalidaIva;
        $itemTotal     = $itemEntradaTotal - $itemSalidaTotal;
        echo '<tr class="totales-item totales-celeste" style="background:#cde6fe;font-weight:bold;">
            <td>TOTAL</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align:right;">TOTALES ' . htmlspecialchars($lastProducto) . ':</td>
            <td></td>
            <td>' . number_format($itemCantidad,2) . '</td>
            <td>' . number_format($itemSubtotal,2) . '</td>
            <td>' . number_format($itemDescuento,2) . '</td>
            <td>' . number_format($itemIva,2) . '</td>
            <td>' . number_format($itemTotal,2) . '</td>
            <td></td>
        </tr>';
        // Reinicia totales/item y bloque de movimientos
        $itemEntradaCantidad = $itemSalidaCantidad = 0;
        $itemEntradaSubtotal = $itemSalidaSubtotal = 0;
        $itemEntradaDescuento = $itemSalidaDescuento = 0;
        $itemEntradaIva = $itemSalidaIva = 0;
        $itemEntradaTotal = $itemSalidaTotal = 0;
        $movimientosPorProducto = [];
    }

    // Acumula totales por producto actual
    if (strtoupper($ver[0]) === "ENTRADA") {
        $itemEntradaCantidad  += floatval($ver[7]);
        $itemEntradaSubtotal  += floatval($ver[8]);
        $itemEntradaDescuento += floatval($ver[9]);
        $itemEntradaIva       += floatval($ver[10]);
        $itemEntradaTotal     += floatval($ver[11]);
    } elseif (strtoupper($ver[0]) === "SALIDA") {
        $itemSalidaCantidad  += floatval($ver[7]);
        $itemSalidaSubtotal  += floatval($ver[8]);
        $itemSalidaDescuento += floatval($ver[9]);
        $itemSalidaIva       += floatval($ver[10]);
        $itemSalidaTotal     += floatval($ver[11]);
    }

    // Acumula totales generales
    if (strtoupper($ver[0]) === "ENTRADA") {
        $totalGenCantidad  += floatval($ver[7]);
        $totalGenSubtotal  += floatval($ver[8]);
        $totalGenDescuento += floatval($ver[9]);
        $totalGenIva       += floatval($ver[10]);
        $totalGenTotal     += floatval($ver[11]);
    } elseif (strtoupper($ver[0]) === "SALIDA") {
        $totalGenCantidad  -= floatval($ver[7]);
        $totalGenSubtotal  -= floatval($ver[8]);
        $totalGenDescuento -= floatval($ver[9]);
        $totalGenIva       -= floatval($ver[10]);
        $totalGenTotal     -= floatval($ver[11]);
    }

    // Guarda el movimiento para impresión
    ob_start();
    ?>
    <tr>
        <td><?php echo $ver[0]; ?></td>
        <td><?php echo $ver[1]; ?></td>
        <td><?php echo $ver[2]; ?></td>
        <td><?php echo $ver[3]; ?></td>
        <td><?php echo $ver[4]; ?></td>
        <td><?php echo $ver[5]; ?></td>
        <td><?php echo $ver[6]; ?></td>
        <td><?php echo $ver[7]; ?></td>
        <td><?php echo $ver[8]; ?></td>
        <td><?php echo $ver[9]; ?></td>
        <td><?php echo $ver[10]; ?></td>
        <td><?php echo $ver[11]; ?></td>
        <td><?php echo $ver[12]; ?></td>
    </tr>
    <?php
    $movimientosPorProducto[] = ob_get_clean();

    $lastProducto = $productoActual;
endwhile;

// Imprime la última tanda de movimientos y la fila de totales del último producto
if ($lastProducto !== null) {
    foreach ($movimientosPorProducto as $row) {
        echo $row;
    }
    $itemCantidad  = $itemEntradaCantidad - $itemSalidaCantidad;
    $itemSubtotal  = $itemEntradaSubtotal - $itemSalidaSubtotal;
    $itemDescuento = $itemEntradaDescuento - $itemSalidaDescuento;
    $itemIva       = $itemEntradaIva - $itemSalidaIva;
    $itemTotal     = $itemEntradaTotal - $itemSalidaTotal;
    echo '<tr class="totales-item totales-celeste" style="background:#cde6fe;font-weight:bold;">
        <td>TOTAL</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align:right;">TOTALES ' . htmlspecialchars($lastProducto) . ':</td>
        <td></td>
        <td>' . number_format($itemCantidad,2) . '</td>
        <td>' . number_format($itemSubtotal,2) . '</td>
        <td>' . number_format($itemDescuento,2) . '</td>
        <td>' . number_format($itemIva,2) . '</td>
        <td>' . number_format($itemTotal,2) . '</td>
        <td></td>
    </tr>';
}
?>
		</tbody>
		<tfoot>
			<tr style="background:#2986c5;color:#fff;font-weight:bold;">
				<td colspan="6" style="text-align:right;">TOTALES GENERALES:</td>
				<td></td>
				<td id="ftCantidad">0.00</td>
				<td id="ftSubtotal">0.00</td>
				<td id="ftDescuento">0.00</td>
				<td id="ftIva">0.00</td>
				<td id="ftTotal">0.00</td>
				<td></td>
			</tr>
		</tfoot>
	</table>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#tbDatosStock')) {
        $('#tbDatosStock').DataTable().destroy();
    }
    function agregarTotalesPorProducto() {
        var table = $('#tbDatosStock').DataTable();
        var rows = table.rows({search:'applied'}).nodes();
        var productos = {};
        var filasPorProducto = {};
        $(rows).each(function(idx, row){
            var d = table.row(row).data();
            var producto = d[5];
            if (!productos[producto]) {
                productos[producto] = {
                    entrada: { cantidad:0, subtotal:0, descuento:0, iva:0, total:0 },
                    salida:  { cantidad:0, subtotal:0, descuento:0, iva:0, total:0 },
                    indices: []
                };
                filasPorProducto[producto] = [];
            }
            filasPorProducto[producto].push(row);
            productos[producto].indices.push(idx);
            if (d[0].toUpperCase() === "ENTRADA") {
                productos[producto].entrada.cantidad    += parseFloat(d[7])  || 0;
                productos[producto].entrada.subtotal    += parseFloat(d[8])  || 0;
                productos[producto].entrada.descuento   += parseFloat(d[9])  || 0;
                productos[producto].entrada.iva         += parseFloat(d[10]) || 0;
                productos[producto].entrada.total       += parseFloat(d[11]) || 0;
            } else if (d[0].toUpperCase() === "SALIDA") {
                productos[producto].salida.cantidad    += parseFloat(d[7])  || 0;
                productos[producto].salida.subtotal    += parseFloat(d[8])  || 0;
                productos[producto].salida.descuento   += parseFloat(d[9])  || 0;
                productos[producto].salida.iva         += parseFloat(d[10]) || 0;
                productos[producto].salida.total       += parseFloat(d[11]) || 0;
            }
        });
        $(rows).each(function(idx, row){
            if ($(row).find('td:first').text().trim() === 'TOTAL') {
                $(row).remove();
            }
        });
        Object.keys(productos).forEach(function(producto){
            var grupo = productos[producto];
            var idxUltimo = grupo.indices[grupo.indices.length-1];
            var rowUltimo = filasPorProducto[producto][grupo.indices.length-1];
            var tds = [
                '<td class="totales-item totales-celeste">TOTAL</td>',
                '<td></td>',
                '<td></td>',
                '<td></td>',
                '<td></td>',
                `<td style="text-align:right;">TOTALES ${producto}:</td>`,
                '<td></td>',
                `<td>${(grupo.entrada.cantidad-grupo.salida.cantidad).toFixed(2)}</td>`,
                `<td>${(grupo.entrada.subtotal-grupo.salida.subtotal).toFixed(2)}</td>`,
                `<td>${(grupo.entrada.descuento-grupo.salida.descuento).toFixed(2)}</td>`,
                `<td>${(grupo.entrada.iva-grupo.salida.iva).toFixed(2)}</td>`,
                `<td>${(grupo.entrada.total-grupo.salida.total).toFixed(2)}</td>`,
                '<td></td>'
            ];
            var $tr = $('<tr class="totales-item totales-celeste" style="background:#cde6fe;font-weight:bold;">'+tds.join('')+'</tr>');
            $(rowUltimo).after($tr);
        });
    }
    var tableDT = $("#tbDatosStock").DataTable({
        responsive: true,
        searching: true,
        paging: true,
        ordering: false,
        info: true,
        pageLength: 100,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"] ],
        "drawCallback": function(settings) {
            agregarTotalesPorProducto();
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    parseFloat(i.replace(/[\$,]/g, '')) || 0 :
                    typeof i === 'number' ? i : 0;
            };
            var colCantidad = 7, colSubtotal = 8, colDescuento = 9, colIva = 10, colTotal = 11;
            var totalCantidad = 0, totalSubtotal = 0, totalDescuento = 0, totalIva = 0, totalTotal = 0;
            api.rows({search:'applied'}).every(function(rowIdx, tableLoop, rowLoop){
                var d = this.data();
                if (d[0] !== "TOTAL") {
                    if (d[0].toUpperCase() === "ENTRADA") {
                        totalCantidad  += intVal(d[colCantidad]);
                        totalSubtotal  += intVal(d[colSubtotal]);
                        totalDescuento += intVal(d[colDescuento]);
                        totalIva       += intVal(d[colIva]);
                        totalTotal     += intVal(d[colTotal]);
                    } else if (d[0].toUpperCase() === "SALIDA") {
                        totalCantidad  -= intVal(d[colCantidad]);
                        totalSubtotal  -= intVal(d[colSubtotal]);
                        totalDescuento -= intVal(d[colDescuento]);
                        totalIva       -= intVal(d[colIva]);
                        totalTotal     -= intVal(d[colTotal]);
                    }
                }
            });
            $('#ftCantidad').html(totalCantidad.toFixed(2));
            $('#ftSubtotal').html(totalSubtotal.toFixed(2));
            $('#ftDescuento').html(totalDescuento.toFixed(2));
            $('#ftIva').html(totalIva.toFixed(2));
            $('#ftTotal').html(totalTotal.toFixed(2));
        }
    });
    // --- Exportar PDF y Excel con sombreado en totales por producto ---
    $(document).off('click', '#btnExportPDF').on('click', '#btnExportPDF', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var headers = [];
        $('#tbDatosStock thead tr th').each(function() {
            headers.push($(this).text().trim());
        });
        var bodyRows = [], bodyStyles = [];
        var table = $('#tbDatosStock').DataTable();
        table.rows({search:'applied'}).every(function (rowIdx, tableLoop, rowLoop) {
            var d = this.data();
            var row = [];
            for (var i = 0; i < d.length; i++) {
                row.push(d[i]);
            }
            bodyRows.push(row);
            // Sombrear totales por producto
            if (d[0] === "TOTAL") {
                bodyStyles.push({fillColor: [205,230,254], fontStyle: 'bold', textColor: [44,62,80]});
            } else {
                bodyStyles.push({});
            }
        });
        var totalCantidad = 0, totalSubtotal = 0, totalDescuento = 0, totalIva = 0, totalTotal = 0;
        var colCantidad = 7, colSubtotal = 8, colDescuento = 9, colIva = 10, colTotal = 11;
        bodyRows.forEach(function(d) {
            if (d[0] !== "TOTAL") {
                if (d[0].toUpperCase() === "ENTRADA") {
                    totalCantidad  += parseFloat(d[colCantidad]) || 0;
                    totalSubtotal  += parseFloat(d[colSubtotal]) || 0;
                    totalDescuento += parseFloat(d[colDescuento]) || 0;
                    totalIva       += parseFloat(d[colIva]) || 0;
                    totalTotal     += parseFloat(d[colTotal]) || 0;
                } else if (d[0].toUpperCase() === "SALIDA") {
                    totalCantidad  -= parseFloat(d[colCantidad]) || 0;
                    totalSubtotal  -= parseFloat(d[colSubtotal]) || 0;
                    totalDescuento -= parseFloat(d[colDescuento]) || 0;
                    totalIva       -= parseFloat(d[colIva]) || 0;
                    totalTotal     -= parseFloat(d[colTotal]) || 0;
                }
            }
        });
        var footRow = ["", "", "", "", "", "TOTALES GENERALES:", "", totalCantidad.toFixed(2), totalSubtotal.toFixed(2), totalDescuento.toFixed(2), totalIva.toFixed(2), totalTotal.toFixed(2), ""];
        var doc = new window.jspdf.jsPDF('l', 'pt', 'a4');
        doc.text("STOCK DE ARTICULOS AGRUPADO", 40, 40);
        doc.autoTable({
    head: [headers],
    body: bodyRows,
    startY: 60,
    foot: [footRow],
    theme: 'striped',
    headStyles: { fillColor: [41, 134, 197], textColor: [255,255,255], fontStyle: 'bold'},
    footStyles: { fillColor: [41, 134, 197], textColor: [255,255,255], fontStyle: 'bold'},
    styles: { fontSize: 8 },
    didParseCell: function(data) {
        // Detecta si la fila es TOTAL por producto (primera columna igual a "TOTAL")
        if (
            data.section === 'body' &&
            data.cell.raw !== undefined &&
            data.row.raw !== undefined &&
            data.row.raw[0] &&
            data.row.raw[0].trim().toUpperCase() === "TOTAL"
        ) {
            data.cell.styles.fillColor = [205,230,254]; // Celeste
            data.cell.styles.textColor = [44,62,80]; // Oscuro
            data.cell.styles.fontStyle = 'bold';
        }
    }
});
        saveAs(doc.output('blob'), 'StockArticulos.pdf');
    });
    $(document).off('click', '#btnExportExcel').on('click', '#btnExportExcel', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var headers = [];
        $('#tbDatosStock thead tr th').each(function() {
            headers.push($(this).text().trim());
        });
        var bodyRows = [];
        var table = $('#tbDatosStock').DataTable();
        table.rows({search:'applied'}).every(function (rowIdx, tableLoop, rowLoop) {
            var d = this.data();
            var row = [];
            for (var i = 0; i < d.length; i++) {
                row.push(d[i]);
            }
            bodyRows.push(row);
        });
        var totalCantidad = 0, totalSubtotal = 0, totalDescuento = 0, totalIva = 0, totalTotal = 0;
        var colCantidad = 7, colSubtotal = 8, colDescuento = 9, colIva = 10, colTotal = 11;
        bodyRows.forEach(function(d) {
            if (d[0] !== "TOTAL") {
                if (d[0].toUpperCase() === "ENTRADA") {
                    totalCantidad  += parseFloat(d[colCantidad]) || 0;
                    totalSubtotal  += parseFloat(d[colSubtotal]) || 0;
                    totalDescuento += parseFloat(d[colDescuento]) || 0;
                    totalIva       += parseFloat(d[colIva]) || 0;
                    totalTotal     += parseFloat(d[colTotal]) || 0;
                } else if (d[0].toUpperCase() === "SALIDA") {
                    totalCantidad  -= parseFloat(d[colCantidad]) || 0;
                    totalSubtotal  -= parseFloat(d[colSubtotal]) || 0;
                    totalDescuento -= parseFloat(d[colDescuento]) || 0;
                    totalIva       -= parseFloat(d[colIva]) || 0;
                    totalTotal     -= parseFloat(d[colTotal]) || 0;
                }
            }
        });
        var footRow = ["", "", "", "", "", "TOTALES GENERALES:", "", totalCantidad.toFixed(2), totalSubtotal.toFixed(2), totalDescuento.toFixed(2), totalIva.toFixed(2), totalTotal.toFixed(2), ""];
        var workbook = new ExcelJS.Workbook();
        var worksheet = workbook.addWorksheet('StockArticulos');
        let headerRow = worksheet.addRow(headers);
        headerRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
            cell.alignment = { horizontal: "center", vertical: "middle" };
            cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
        });
        bodyRows.forEach(row => {
            let excelRow = worksheet.addRow(row);
            if (row[0] === "TOTAL") {
                excelRow.eachCell(cell => {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FFCDE6FE" } };
                    cell.font = { bold: true, color: { argb: "FF2C3E50" } };
                });
            }
        });
        let totalRow = worksheet.addRow(footRow);
        totalRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
            cell.alignment = { horizontal: "right", vertical: "middle" };
            cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
        });
        worksheet.columns.forEach(function(column, i) {
            let maxLength = 10;
            column.eachCell({ includeEmpty: true }, function(cell) {
                let cellValue = cell.value ? cell.value.toString() : "";
                maxLength = Math.max(maxLength, cellValue.length + 2);
            });
            column.width = maxLength;
        });
        workbook.xlsx.writeBuffer().then(function(buffer) {
            saveAs(new Blob([buffer], { type: "application/octet-stream" }), "StockArticulos.xlsx");
        });
    });
});
</script>
<style>
hr {
    border-top: 2px solid #eadfdf;
}
tbody tr.totales-celeste {
    background: #cde6fe !important;
    color: #2c3e50;
    font-weight: bold;
}
tfoot tr {
    background: #2986c5 !important;
    color: #fff !important;
    font-weight: bold;
}
</style>