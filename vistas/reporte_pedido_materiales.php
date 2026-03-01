<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
if(isset($_SESSION['usuario'])){
require_once "../clases/Conexion.php";
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "INFORME DE PEDIDO DE MATERIALES");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>INFORME DE PEDIDO DE MATERIALES</title>
    <?php require_once "menu.php"; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <style>
        .panel-filtros { background: #f5f5f5; border-radius: 6px; padding: 16px 12px; margin-bottom: 25px; box-shadow: 0 1px 3px #ddd; }
        .form-inline .form-control, .form-inline .select2-container { margin-right: 10px; }
        .table th, .table td { text-align: center; font-size: 11px; padding: 3px 4px; }
        #btnExportarExcel, #btnImprimir { margin-left: 10px; }
        .select2-container { min-width: 200px; }
        .titulo-reporte { text-decoration: underline; color:#1c5d99; font-weight:bold; font-size:20px; margin-bottom:15px; }
        tfoot tr { background: #2986c5 !important; color: #fff !important; font-weight: bold; }
        tfoot td { font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">INFORME DE PEDIDO DE MATERIALES</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosPedidoMateriales" class="form-inline" autocomplete="off">
            <div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
                <div class="col-sm-2">
                    <label for="fecha_inicial">Desde:</label>
                    <input type="date" id="fecha_inicial" name="fecha_inicial" class="form-control">
                    <label for="fecha_final">Hasta:</label>
                    <input type="date" id="fecha_final" name="fecha_final" class="form-control">
                </div>
                <div class="col-sm-3">
                    <label for="proyecto">Proyecto:</label>
                    <select id="proyecto" name="proyecto" class="form-control" style="width:200px"></select>
                    <label for="obra">Nombre de la Obra:</label>
                    <select id="obra" name="obra" class="form-control" style="width:200px"></select>
                </div>
                <div class="col-sm-3">
                    <label for="articulo">Artículo:</label>
                    <select id="articulo" name="articulo" class="form-control" style="width:200px"><option value="">Todos</option></select>
                    <label for="lugar">B. Lugar:</label>
                    <select id="lugar" name="lugar" class="form-control" style="width: 200px;"><option value="">Todos</option></select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" style="width: 150px; margin-top: 15px;" class="btn btn-primary">Buscar</button>
                </div>
                <div class="col-sm-2">
                    <button type="button" style="width: 150px; margin-top: 15px;" id="btnExportarExcel" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button type="button" style="width: 150px; margin-top: 15px;" id="btnImprimir" class="btn btn-info">
                        <i class="fa-solid fa-file-pdf" style="color: red;"></i> Imprimir PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div style="text-align:right; margin-bottom:0; margin-top:-10px;">
        <input type="text" id="buscadorPersonalizado" class="form-control" style="display:inline-block; width:250px; margin-top:0; margin-bottom:0;" placeholder="Buscar en el reporte...">
    </div>
    <table id="tablaPedidoMateriales" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID ORDEN</th>
                <th>PROYECTO</th>
                <th>NOMBRE DE LA OBRA</th>
                <th>FECHA</th>
                <th>LUGAR</th>
                <th>ARTICULO</th>
                <th>CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se llena por JS -->
        </tbody>
        <tfoot>
            <tr style="background:#2986c5;color:#fff;font-weight:bold;">
                <td colspan="6" style="text-align:right;">TOTALES GENERALES:</td>
                <td id="ftTotalCantidad">0.00</td>
            </tr>
        </tfoot>
    </table>
</div>
<script>
function getBase64Image(img) {
    var canvas = document.createElement("canvas");
    canvas.width = img.naturalWidth || img.width;
    canvas.height = img.naturalHeight || img.height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    var dataURL = canvas.toDataURL("image/png");
    return dataURL.replace(/^data:image\/png;base64,/, "");
}
// Variable global para almacenar los datos originales del backend y los filtrados
var pedidosOriginal = [];
var pedidosFiltrados = [];

$(document).ready(function(){
        // Buscador personalizado fuera de DataTables
        $('#buscadorPersonalizado').on('keyup', function() {
            let search = $(this).val().toLowerCase();
            pedidosFiltrados = pedidosOriginal.filter(function(r) {
                if (r.subtotal || r.total_general) return false;
                return (
                    (r.id_orden && r.id_orden.toLowerCase().includes(search)) ||
                    (r.proyecto && r.proyecto.toLowerCase().includes(search)) ||
                    (r.obra && r.obra.toLowerCase().includes(search)) ||
                    (r.fecha && r.fecha.toLowerCase().includes(search)) ||
                    (r.lugar && r.lugar.toLowerCase().includes(search)) ||
                    (r.articulo && r.articulo.toLowerCase().includes(search))
                );
            });
            // Recalcular subtotales y total general sobre los filtrados
            let agrupados = {};
            let resultado = [];
            let totalGeneral = 0;
            pedidosFiltrados.forEach(r => {
                if (!agrupados[r.articulo]) agrupados[r.articulo] = { subtotal: 0, rows: [] };
                agrupados[r.articulo].subtotal += parseFloat(r.cantidad);
                agrupados[r.articulo].rows.push(r);
                totalGeneral += parseFloat(r.cantidad);
            });
            Object.keys(agrupados).forEach(art => {
                agrupados[art].rows.forEach(row => resultado.push(row));
                resultado.push({ subtotal: true, articulo: art, cantidad: agrupados[art].subtotal });
            });
            resultado.push({ total_general: true, cantidad: totalGeneral });
            pintarTablaPedidoMateriales(resultado);
            pedidosFiltrados = resultado;
        });
    // Inicializar combos
    cargarProyectos();
    cargarObras('');
    cargarArticulos();
    cargarLugares();
    // Fechas por defecto
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const fechaHoy = `${yyyy}-${mm}-${dd}`;
    document.getElementById('fecha_inicial').value = fechaHoy;
    document.getElementById('fecha_final').value = fechaHoy;

    $('#formFiltrosPedidoMateriales').on('submit', function(e){
        e.preventDefault();
        buscarPedidoMateriales();
        // Limpiar el buscador personalizado
        $('#buscadorPersonalizado').val('');
    });
    $('#btnExportarExcel').on('click', function(){
        exportarExcelPedidoMateriales();
    });
    $('#btnImprimir').on('click', function(){
        exportarTablaAPDF();
    });
    // Cargar obras al cambiar proyecto
    $('#proyecto').on('change', function(){
        cargarObras($(this).val());
    });
    // Inicial
    buscarPedidoMateriales();
});

function cargarProyectos() {
    $.post('../procesos/reportes/pedido_materiales_reporte.php', {action:'listar_proyectos'}, function(data){
        let proyectos = JSON.parse(data);
        let $proyecto = $('#proyecto');
        $proyecto.empty().append('<option value="">Todos</option>');
        proyectos.forEach(p => {
            $proyecto.append(`<option value="${p.id}">${p.nombre}</option>`);
        });
        $proyecto.select2({width:'200px', placeholder:'Proyecto', allowClear:true});
    });
}
function cargarObras(proyecto) {
    $.post('../procesos/reportes/pedido_materiales_reporte.php', {action:'listar_obras', proyecto: proyecto}, function(data){
        let obras = JSON.parse(data);
        let $obra = $('#obra');
        $obra.empty().append('<option value="">Todos</option>');
        obras.forEach(o => {
            $obra.append(`<option value="${o.id}">${o.nombre}</option>`);
        });
        $obra.select2({width:'200px', placeholder:'Obra', allowClear:true});
    });
}
function cargarArticulos() {
    $.post('../procesos/reportes/pedido_materiales_reporte.php', {action:'listar_articulos'}, function(data){
        let articulos = JSON.parse(data);
        let $articulo = $('#articulo');
        $articulo.empty().append('<option value="">Todos</option>');
        articulos.forEach(a => {
            $articulo.append(`<option value="${a.id_producto}">${a.nombre}</option>`);
        });
        $articulo.select2({width:'200px', placeholder:'Artículo', allowClear:true});
    });
}
function cargarLugares() {
    $.post('../procesos/reportes/pedido_materiales_reporte.php', {action:'listar_lugares'}, function(data){
        let lugares = JSON.parse(data);
        let $lugar = $('#lugar');
        $lugar.empty().append('<option value="">Todos</option>');
        lugares.forEach(l => {
            $lugar.append(`<option value="${l.id_bodega}">${l.nombreBodega}</option>`);
        });
        $lugar.select2({width:'200px', placeholder:'Lugar', allowClear:true});
    });
}

function buscarPedidoMateriales() {
    let datos = {
        action: 'listar_reporte_pedido_materiales',
        desde: $('#fecha_inicial').val(),
        hasta: $('#fecha_final').val(),
        proyecto: $('#proyecto').val(),
        obra: $('#obra').val(),
        articulo: $('#articulo').val(),
        lugar: $('#lugar').val()
    };
    $.post('../procesos/reportes/pedido_materiales_reporte.php', datos, function(data){
        let pedidos = JSON.parse(data);
        pedidosOriginal = pedidos; // Guardar para exportaciones
        pedidosFiltrados = pedidosOriginal;
        pintarTablaPedidoMateriales(pedidosOriginal);
    });
}


function pintarTablaPedidoMateriales(pedidos) {
    let $tabla = $('#tablaPedidoMateriales');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    let totalGeneral = 0;
    if(!Array.isArray(pedidos) || pedidos.length === 0){
        let vacio = '<tr>';
        for(let i=0;i<7;i++) vacio += '<td></td>';
        vacio += '</tr>';
        $tbody.append(vacio);
    } else {
        pedidos.forEach(r => {
                if (r.subtotal) {
                    let subtotalRow = `<tr style="background:#cde6fe;font-weight:bold;">
                        <td>TOTAL</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style='text-align:right;'>TOTALES ${r.articulo}:</td>
                        <td>${parseFloat(r.cantidad).toFixed(2)}</td>
                    </tr>`;
                    $tbody.append(subtotalRow);
            } else if (r.total_general) {
                // El total general se muestra en el footer, no como fila
                $('#ftTotalCantidad').text(parseFloat(r.cantidad).toFixed(2));
            } else {
                let rowHtml = `<tr>
                    <td>${r.id_orden ?? ''}</td>
                    <td>${r.proyecto ?? ''}</td>
                    <td>${r.obra ?? ''}</td>
                    <td>${r.fecha ?? ''}</td>
                    <td>${r.lugar ?? ''}</td>
                    <td>${r.articulo ?? ''}</td>
                    <td>${parseFloat(r.cantidad).toFixed(2)}</td>
                </tr>`;
                $tbody.append(rowHtml);
            }
        });
    }
    $tabla.DataTable({
        responsive: true,
        destroy: true,
        order: [],
        searching: false // Desactivar búsqueda interna de DataTables
    });
    // Reactivar buscador personalizado después de cada render
    setTimeout(function() {
        let $input = $('#tablaPedidoMateriales_filter input');
        if ($input.length && !$input.data('custom-search')) {
            $input.data('custom-search', true);
            $input.off('keyup').on('keyup', function() {
                let search = $(this).val().toLowerCase();
                pedidosFiltrados = pedidosOriginal.filter(function(r) {
                    if (r.subtotal || r.total_general) return false;
                    return (
                        (r.id_orden && r.id_orden.toLowerCase().includes(search)) ||
                        (r.proyecto && r.proyecto.toLowerCase().includes(search)) ||
                        (r.obra && r.obra.toLowerCase().includes(search)) ||
                        (r.fecha && r.fecha.toLowerCase().includes(search)) ||
                        (r.lugar && r.lugar.toLowerCase().includes(search)) ||
                        (r.articulo && r.articulo.toLowerCase().includes(search))
                    );
                });
                // Recalcular subtotales y total general sobre los filtrados
                let agrupados = {};
                let resultado = [];
                let totalGeneral = 0;
                pedidosFiltrados.forEach(r => {
                    if (!agrupados[r.articulo]) agrupados[r.articulo] = { subtotal: 0, rows: [] };
                    agrupados[r.articulo].subtotal += parseFloat(r.cantidad);
                    agrupados[r.articulo].rows.push(r);
                    totalGeneral += parseFloat(r.cantidad);
                });
                Object.keys(agrupados).forEach(art => {
                    agrupados[art].rows.forEach(row => resultado.push(row));
                    resultado.push({ subtotal: true, articulo: art, cantidad: agrupados[art].subtotal });
                });
                resultado.push({ total_general: true, cantidad: totalGeneral });
                pintarTablaPedidoMateriales(resultado);
                pedidosFiltrados = resultado;
            });
        }
    }, 100);
    // Buscador personalizado
    $('#tablaPedidoMateriales_filter input').off('keyup').on('keyup', function() {
        let search = $(this).val().toLowerCase();
        // Filtrar pedidosOriginal según el texto buscado en cualquier campo relevante
        pedidosFiltrados = pedidosOriginal.filter(function(r) {
            if (r.subtotal || r.total_general) return false;
            return (
                (r.id_orden && r.id_orden.toLowerCase().includes(search)) ||
                (r.proyecto && r.proyecto.toLowerCase().includes(search)) ||
                (r.obra && r.obra.toLowerCase().includes(search)) ||
                (r.fecha && r.fecha.toLowerCase().includes(search)) ||
                (r.lugar && r.lugar.toLowerCase().includes(search)) ||
                (r.articulo && r.articulo.toLowerCase().includes(search))
            );
        });
        // Recalcular subtotales y total general sobre los filtrados
        let agrupados = {};
        let resultado = [];
        let totalGeneral = 0;
        pedidosFiltrados.forEach(r => {
            if (!agrupados[r.articulo]) agrupados[r.articulo] = { subtotal: 0, rows: [] };
            agrupados[r.articulo].subtotal += parseFloat(r.cantidad);
            agrupados[r.articulo].rows.push(r);
            totalGeneral += parseFloat(r.cantidad);
        });
        Object.keys(agrupados).forEach(art => {
            agrupados[art].rows.forEach(row => resultado.push(row));
            resultado.push({ subtotal: true, articulo: art, cantidad: agrupados[art].subtotal });
        });
        resultado.push({ total_general: true, cantidad: totalGeneral });
        pintarTablaPedidoMateriales(resultado);
        pedidosFiltrados = resultado;
    });
}

function exportarTablaAPDF() {
    const imgElement = document.getElementById("logoEmpresa");
    const logoBase64 = getBase64Image(imgElement);
    const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });
    if (logoBase64) {
        doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
    }
    doc.setFontSize(24);
    doc.setTextColor(40, 55, 71);
    const y = 40;
    const pageWidth = doc.internal.pageSize.getWidth();
    doc.text("REPORTE DE PEDIDOS DE MATERIALES", pageWidth / 2, y, { align: "center" });
    const x = 70;
    let desde = $('#fecha_inicial').val();
    let hasta = $('#fecha_final').val();
    doc.setFontSize(16);
    doc.setTextColor(40, 55, 71);
    doc.text("DESDE: " +  desde + " HASTA: " + hasta , pageWidth / 2, x, { align: "center" });

    doc.setFontSize(12);
    doc.setTextColor(44, 62, 80);
    let filtroY = 100;
    const proyecto = $('#proyecto option:selected').text() || 'Todos';
    const obra = $('#obra option:selected').text() || 'Todos';
    const lugar = $('#lugar option:selected').text() || 'Todos';
    const articulo = $('#articulo option:selected').text() || 'Todos';
    doc.text(`Proyecto: ${proyecto}`, 50, filtroY); filtroY += 16;
    doc.text(`Nombre de la Obra: ${obra}`, 50, filtroY); filtroY += 16;
    doc.text(`Lugar: ${lugar}`, 50, filtroY); filtroY += 16;
    doc.text(`Artículos: ${articulo}`, 50, filtroY); filtroY += 10;

    // Obtener datos de la tabla (solo los visibles/filtrados)
    // Usar los datos del backend igual que el grid
    let body = [];
    // Usar los filtrados si hay búsqueda, si no los originales
    let datos = pedidosFiltrados && pedidosFiltrados.length > 0 ? pedidosFiltrados : pedidosOriginal;
    datos.forEach(r => {
        if (r.subtotal) {
            body.push([
                'TOTAL', '', '', '', '', `TOTALES ${r.articulo}:`, parseFloat(r.cantidad).toFixed(2)
            ]);
        } else if (r.total_general) {
            body.push(['', '', '', '', '', 'TOTALES GENERALES:', parseFloat(r.cantidad).toFixed(2)]);
        } else {
            let row = [
                r.id_orden ?? '',
                r.proyecto ?? '',
                r.obra ?? '',
                r.fecha ?? '',
                r.lugar ?? '',
                r.articulo ?? '',
                parseFloat(r.cantidad).toFixed(2)
            ];
            // row._highlight eliminado, ya no se resalta en amarillo
            body.push(row);
        }
    });

    doc.autoTable({
        head: [[
            'ID ORDEN', 'PROYECTO', 'NOMBRE DE LA OBRA', 'FECHA', 'LUGAR', 'ARTICULO', 'CANTIDAD'
        ]],
        body: body,
        startY: filtroY,
        styles: {
            fontSize: 11,
            cellPadding: 3,
            halign: 'center',
            valign: 'middle',
        },
        headStyles: {
            fillColor: [41, 134, 197],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'center',
            fontSize: 11
        },
        alternateRowStyles: {
            fillColor: [230, 240, 255]
        },
        didParseCell: function(data) {
            if (data.row.index !== undefined && data.row.raw && data.row.raw[5] && data.row.raw[5].startsWith('TOTALES')) {
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fillColor = [227, 242, 253];
                data.cell.styles.halign = 'right';
            }
            if (data.row.index !== undefined && data.row.raw && data.row.raw[5] === 'TOTALES GENERALES:') {
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fillColor = [41, 134, 197];
                data.cell.styles.textColor = [255,255,255];
                data.cell.styles.halign = 'right';
            }
        },
        margin: { left: 40, right: 40 }
    });
    const fecha = new Date().toLocaleString();
    doc.setFontSize(10);
    doc.setTextColor(127, 140, 141);
    doc.text(`Generado el: ${fecha} | Usuario:` +  '<?php echo $_SESSION['usuario']; ?>', 40, doc.internal.pageSize.getHeight() - 30);
    doc.save('ReportePedidoMateriales.pdf');
}

function obtenerDatosDeTablaPedido(tablaId) {
    const resultados = [];
    const table = $('#' + tablaId).DataTable();
    const allData = table.rows({search:'applied'}).data().toArray();
    allData.forEach(row => {
        resultados.push({
            campo1: row[0],
            campo2: row[1],
            campo3: row[2],
            campo4: row[3],
            campo5: row[4],
            campo6: row[5],
            campo7: row[6]
        });
    });
    return resultados;
}

function exportarExcelPedidoMateriales() {
    // Usar los datos del backend igual que el grid
    var headers = [
        'ID ORDEN', 'PROYECTO', 'NOMBRE DE LA OBRA', 'FECHA', 'LUGAR', 'ARTICULO', 'CANTIDAD'
    ];
    var pedidos = (pedidosFiltrados && pedidosFiltrados.length > 0) ? pedidosFiltrados : pedidosOriginal;
    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('PedidoMateriales');
    let headerRow = worksheet.addRow(headers);
    headerRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: "FF2986C5" }
        };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });
    pedidos.forEach(r => {
        let row;
        if (r.subtotal) {
            row = ['TOTAL', '', '', '', '', `TOTALES ${r.articulo}:`, parseFloat(r.cantidad).toFixed(2)];
        } else if (r.total_general) {
            row = ['', '', '', '', '', 'TOTALES GENERALES:', parseFloat(r.cantidad).toFixed(2)];
        } else {
            row = [
                r.id_orden ?? '',
                r.proyecto ?? '',
                r.obra ?? '',
                r.fecha ?? '',
                r.lugar ?? '',
                r.articulo ?? '',
                parseFloat(r.cantidad).toFixed(2)
            ];
        }
        let excelRow = worksheet.addRow(row);
        excelRow.eachCell((cell, colNumber) => {
            cell.font = { size: 11, name: 'Arial', bold: false };
            cell.alignment = { horizontal: 'center', vertical: 'middle' };
            cell.border = {
                top: {style:'thin'}, left: {style:'thin'},
                bottom: {style:'thin'}, right: {style:'thin'}
            };
        });
        if(row[5] && row[5].startsWith('TOTALES')) {
            excelRow.eachCell(cell => {
                cell.font = { size: 11, name: 'Arial', bold: true };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FFE3F2FD" } };
                cell.alignment = { horizontal: 'right', vertical: 'middle' };
            });
        }
        if(row[5] && row[5].startsWith('TOTALES GENERALES')) {
            excelRow.eachCell(cell => {
                cell.font = { size: 11, name: 'Arial', bold: true, color: { argb: "FFFFFFFF" } };
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
                cell.alignment = { horizontal: 'right', vertical: 'middle' };
            });
        }
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
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "ReportePedidoMateriales.xlsx");
    });
}
</script>
</body>
</html>
<?php 
}else{
    header("location:index.php");
}
?>
