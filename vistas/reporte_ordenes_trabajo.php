<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "INFORME ORDENES DE TRABAJO");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES → ÓRDENES DE TRABAJO</title>
    <?php require_once "menu.php"; ?> 
    <!-- Select2 y DataTables desde CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <!-- ExcelJS y FileSaver -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <style>
        .panel-filtros { background: #f5f5f5; border-radius: 6px; padding: 16px 12px; margin-bottom: 25px; box-shadow: 0 1px 3px #ddd; }
        .form-inline .form-control, .form-inline .select2-container { margin-right: 10px; }
        .table th, .table td { text-align: center; }
        #btnExportarExcel, #btnImprimir { margin-left: 10px; }
        .select2-container { min-width: 200px; }
        .titulo-reporte {  text-decoration: underline;color:#1c5d99; font-weight:bold; font-size:20px; margin-bottom:15px; }
        @media print {
            body * { visibility: hidden; }
            #tablaOrdenesTrabajo, #tablaOrdenesTrabajo * { visibility: visible; }
            #tablaOrdenesTrabajo { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .fa-file-pdf:before { color: red; }
        /* Footer azul */
        tfoot tr {
            background: #2986c5 !important;
            color: #fff !important;
            font-weight: bold;
        }
        tfoot td {
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">INFORME DE ORDENES DE TRABAJO</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosOrdenes" class="form-inline" autocomplete="off">
            <div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
               <div class="col-sm-2">  
               <label class="label-custom" for="fecha_inicial">Fecha inicial:</label>
               <input type="date" id="fecha_inicial" name="fecha_inicial" class="form-control">
               <label class="label-custom" for="fecha_final">Fecha final:</label>
               <input type="date" id="fecha_final" name="fecha_final" class="form-control"> 
            </div> 
            <div class="col-sm-3">  
               <label class="label-custom" for="lugar">B. Lugar:</label>
               <select id="lugar" name="lugar" class="form-control" style="width: 300px;"><option value="">Todos</option></select>
               <label class="label-custom" for="ubicacion">B. Ubicaciòn:</label>
               <select id="ubicacion" name="ubicacion" class="form-control" style="width:300px"><option value="">Todos</option></select>
             </div> 
             <div class="col-sm-3">  
                   <label class="label-custom" for="estado">Estado:</label>
                   <select id="estado" name="estado" class="form-control" style="width:200px">
                   <option value="">Todos</option>
                   <option value="1">Grabado</option>
                   <option value="2">Aprobado</option> 
                   <option value="3">Rechazado</option>
                   <option value="4">Pendiente</option>
                   <option value="5">Anulado</option> 
                </select>
                <button type="submit" style=" width: 200px;  margin-top: 15px;" class="btn btn-primary">Buscar</button>
            </div> 
            <div class="col-sm-4">   
              <button type="button" style="width: 200px; margin-top: 15px;" id="btnExportarExcel" class="btn btn-success">
                  <i class="fa-solid fa-file-excel"></i> Exportar a Excel
              </button>
              <button type="button" style="width: 200px; margin-top: 15px;" id="btnImprimir" class="btn btn-info">
                 <i class="fa-solid fa-file-pdf"></i> Imprimir PDF
              </button>
            </div> 
           </div>   
        </form>
    </div>
    <table id="tablaOrdenesTrabajo" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID Orden</th>
                <th>Costo</th>
                <th>Fecha Inicial</th>
                <th>Fecha Final</th>
                <th>Lugar</th>
                <th>Ubicación</th>
                <th>Observación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se llena por JS -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="1" style="text-align:right;">TOTAL:</td>
                <td id="ftCosto">0.00</td>
                <td colspan="6"></td>
            </tr>
        </tfoot>
    </table>
</div>

<script> 
$(document).ready(function(){ 
    // FECHAS POR DEFECTO
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const fechaHoy = `${yyyy}-${mm}-${dd}`;
    document.getElementById('fecha_inicial').value = fechaHoy;
    document.getElementById('fecha_final').value = fechaHoy;

    // Inicializar combos
    $('#lugar, #ubicacion, #estado').select2({width:'200px', placeholder:'Seleccione una opción', allowClear:true});
    cargarLugaresUbicaciones();

    $('#formFiltrosOrdenes').on('submit', function(e){
        e.preventDefault();
        buscarOrdenesTrabajo();
    });

    $('#btnExportarExcel').on('click', function(){
        exportarExcelOrdenes();
    });

    $('#btnImprimir').on('click', function(){
        fecha_inicial = $('#fecha_inicial').val();
        fecha_final = $('#fecha_final').val();
        var lugar = $('#lugar option:selected').text() || 'Todas';
        var ubicacion = $('#ubicacion option:selected').text() || 'Todas';
        var estado = $('#estado option:selected').text() || 'Todas';
        const filtrosAplicados = { 
              "Lugar": lugar,
              "Ubicacion": ubicacion,
              "Estado": estado,
        }; 
        const resultados = obtenerDatosDeTabla('tablaOrdenesTrabajo');
        exportarTablaAPDF(resultados, filtrosAplicados);
    }); 
    buscarOrdenesTrabajo();
});

function cargarLugaresUbicaciones() {
    $.post('../procesos/reportes/ordenes_trabajo_reporte.php', {action:'listar_bodegas'}, function(data){
        let bodegas = JSON.parse(data);
        let $lugar = $('#lugar');
        let $ubicacion = $('#ubicacion');
        $lugar.empty().append('<option value="">Todos</option>');
        $ubicacion.empty().append('<option value="">Todos</option>');
        bodegas.forEach(b => {
            $lugar.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
            $ubicacion.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
        });
        $lugar.select2({width:'200px', placeholder:'Lugar', allowClear:true});
        $ubicacion.select2({width:'200px', placeholder:'Ubicación', allowClear:true});
    });
}

function buscarOrdenesTrabajo() {
    let datos = {
        action: 'listar_reporte_ordenes',
        fecha_inicial: $('#fecha_inicial').val(),
        fecha_final: $('#fecha_final').val(),
        lugar: $('#lugar').val(),
        ubicacion: $('#ubicacion').val(),
        estado: $('#estado').val()
    }; 
    $.post('../procesos/reportes/ordenes_trabajo_reporte.php', datos, function(data){
        let ordenes = JSON.parse(data);
        pintarTablaOrdenes(ordenes);
    }); 
}

function pintarTablaOrdenes(ordenes){
    let $tabla = $('#tablaOrdenesTrabajo');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    if(!Array.isArray(ordenes) || ordenes.length === 0){
        // $tbody.append('<tr><td colspan="8">No hay resultados para los filtros seleccionados.</td></tr>');
    }else {
        ordenes.forEach(r => {
            $tbody.append(`<tr>
                <td>${r.id_orden ?? ""}</td>
                <td>$${r.costo !== undefined && r.costo !== null ? parseFloat(r.costo).toFixed(2) : ""}</td>
                <td>${r.fecha_inicial ?? ""}</td>
                <td>${r.fecha_final ?? ""}</td>
                <td>${r.lugar ?? ""}</td>
                <td>${r.ubicacion ?? ""}</td>
                <td>${r.observacion ?? ""}</td>
                <td>${r.estado ?? ""}</td>
            </tr>`);
        });
    }
    // Inicializa DataTable con footerCallback para totalizar Costo
    $tabla.DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']],
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    parseFloat(i.replace(/[\$,]/g, '')) || 0 :
                    typeof i === 'number' ?
                        i : 0;
            };
            // Columna Costo es la 1 (índice 1)
            var total = api.column(1, {search:'applied'}).data().reduce(function(a,b){ return intVal(b) + a; }, 0);
            $(api.column(1).footer()).html('$' + total.toFixed(2));
        }
    });
}

function getBase64Image(imgElement) {
    const canvas = document.createElement("canvas");
    canvas.width = imgElement.width;
    canvas.height = imgElement.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
    return canvas.toDataURL("image/png");
} 

//Exportar a pdf con total y footer azul
function exportarTablaAPDF(resultados, filtrosAplicados) {
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
    doc.text("Reporte de Ordenes de Trabajo", pageWidth / 2, y, { align: "center" });
    const x = 70;
    fecha_inicial = $('#fecha_inicial').val();
    fecha_final = $('#fecha_final').val();  
    doc.setFontSize(16);
    doc.setTextColor(40, 55, 71);
    doc.text("DESDE: " +  fecha_inicial + " HASTA: " + fecha_final , pageWidth / 2, x, { align: "center" });

    doc.setFontSize(12);
    doc.setTextColor(44, 62, 80);
    let filtroY = 100;
    doc.text('Filtros aplicados:', 40, filtroY);
    doc.line(40, 100 + 2, x + 60, 100 + 2);
    filtroY += 18;
    Object.entries(filtrosAplicados).forEach(([key, value]) => {
        doc.text(`${key}: ${value}`, 50, filtroY);
        filtroY += 16;
    });
    filtroY += 10;

    // --- Calcular total costo
    let totalCosto = 0;
    resultados.forEach(r => {
        let valor = r.campo2.replace('$','');
        totalCosto += parseFloat(valor) || 0;
    });

    // Define columnas
    const columns = [
        { header: 'ID Orden', dataKey: 'campo1' },
        { header: 'Costo', dataKey: 'campo2' },
        { header: 'Fecha Inicial', dataKey: 'campo3' },
        { header: 'Fecha Final', dataKey: 'campo4' },
        { header: 'Lugar', dataKey: 'campo5' },
        { header: 'Ubicación', dataKey: 'campo6' },
        { header: 'Observación', dataKey: 'campo7' },
        { header: 'Estado', dataKey: 'campo8' }
    ];

    // --- Footer azul con total
    let footRow = [
        "TOTAL:",
        '$' + totalCosto.toFixed(2),
        "", "", "", "", "", ""
    ];

    doc.autoTable({
        columns: columns,
        body: resultados,
        foot: [footRow],
        startY: filtroY,
        styles: {
            fontSize: 10,
            cellPadding: 5,
            fillColor: [230, 240, 255]
        },
        headStyles: {
            fillColor: [41, 134, 197],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        footStyles: {
            fillColor: [41, 134, 197],
            textColor: [255,255,255],
            fontStyle: 'bold'
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        margin: { left: 40, right: 40 }
    });

    const fecha = new Date().toLocaleString();
    doc.setFontSize(10);
    doc.setTextColor(127, 140, 141);
    doc.text(`Generado el: ${fecha} | Usuario:` +  '<?php echo $_SESSION['usuario']; ?>', 40, doc.internal.pageSize.getHeight() - 30);

    doc.save('Reporte de Ordenes Trabajo.pdf');
}

//Obtiene datos de grid para exportar a pdf 
function obtenerDatosDeTabla(tablaId) {
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
            campo7: row[6],
            campo8: row[7]
        });
    });
    return resultados;
} 

//Exportar a Excel con total y footer azul
function exportarExcelOrdenes(){
    var table = document.getElementById('tablaOrdenesTrabajo');
    var rows = Array.from(table.querySelectorAll('tbody tr'));
    var headerCells = Array.from(table.querySelectorAll('thead tr th'));
    var headers = headerCells.map(cell => cell.innerText);

    // Extrae body
    var bodyRows = [];
    rows.forEach(row => {
        var cols = Array.from(row.cells).map(cell => cell.innerText);
        bodyRows.push(cols);
    });

    // Calcula total Costo (columna 1)
    let totalCosto = 0;
    bodyRows.forEach(r => {
        let valor = r[1].replace('$','');
        totalCosto += parseFloat(valor) || 0;
    });

    let footRow = [
        "TOTAL:",
        '$' + totalCosto.toFixed(2),
        "", "", "", "", "", ""
    ];

    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('OrdenesTrabajo');

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

    bodyRows.forEach(row => {
        let excelRow = worksheet.addRow(row);
        // Formato decimal en columna "Costo"
        excelRow.getCell(2).numFmt = '0.00';
        excelRow.getCell(2).alignment = { horizontal: "right" };
    });

    let totalRow = worksheet.addRow(footRow);
    totalRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: "FF2986C5" }
        };
        cell.alignment = { horizontal: "right", vertical: "middle" };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });
    totalRow.getCell(1).alignment = { horizontal: "left", vertical: "middle" };

    worksheet.columns.forEach(function(column, i) {
        let maxLength = 10;
        column.eachCell({ includeEmpty: true }, function(cell) {
            let cellValue = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, cellValue.length + 2);
        });
        column.width = maxLength;
    });

    workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "ReporteOrdenesTrabajo.xlsx");
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