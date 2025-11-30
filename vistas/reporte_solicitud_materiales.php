<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "SOLICITUD DE MATERIALES");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES → SOLICITUD DE MATERIALES</title>
    <?php require_once "menu.php"; ?> 
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- ExcelJS y FileSaver para exportar Excel con formato -->
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
            #tablaSolicitudMateriales, #tablaSolicitudMateriales * { visibility: visible; }
            #tablaSolicitudMateriales { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .fa-file-pdf:before { color: red; }
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">REPORTE DE SOLICITUD DE MATERIALES</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosSolicitudes" class="form-inline" autocomplete="off">
            <div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
               <div class="col-sm-2">
                    <label class="label-custom" for="fecha_desde">Fecha desde:</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" class="form-control">
                    <label class="label-custom" for="fecha_hasta">Fecha hasta:</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control">
               </div>
               <div class="col-sm-3">
                    <label class="label-custom" for="id_bodega_principal">B. Lugar:</label>
                    <select id="id_bodega_principal" name="id_bodega_principal" class="form-control" style="width: 250px;"><option value="">Todas</option></select>
                    <label class="label-custom" for="id_bodega_secundaria">B. Ubicaciòn:</label>
                    <select id="id_bodega_secundaria" name="id_bodega_secundaria" class="form-control" style="width:250px"><option value="">Todas</option></select>
                    <label class="label-custom" for="id_estado">Estado solicitud:</label>
                    <select id="id_estado" name="id_estado" class="form-control" style="width:200px"><option value="">Todos</option></select>
                </div>
                <div class="col-sm-3"> 
                    <label class="label-custom" for="id_articulo">Artículo:</label>
                    <select id="id_articulo" name="id_articulo" class="form-control" style="width:200px"><option value="">Todos</option></select>
                    <label class="label-custom" for="id_estado_articulo">Estado solicitud artículo:</label>
                    <select id="id_estado_articulo" name="id_estado_articulo" class="form-control" style="width:200px"><option value="">Todos</option></select>
                    <button type="submit" style=" width: 200px;  margin-top: 15px" class="btn btn-primary">Buscar</button>
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
    <table id="tablaSolicitudMateriales" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID Solicitud</th>
                <th>Fecha</th>
                <th>Lugar</th>
                <th>Ubicaciòn</th>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Estado Solicitud</th>
                <th>Estado Solicitud Artículo</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se llena por JS -->
        </tbody>
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
    document.getElementById('fecha_desde').value = fechaHoy;
    document.getElementById('fecha_hasta').value = fechaHoy;

    // Inicializar combos
    $('#id_bodega_principal, #id_bodega_secundaria, #id_estado, #id_articulo, #id_estado_articulo').select2({width:'200px', placeholder:'Seleccione una opción', allowClear:true});
    cargarCombos();

    $('#formFiltrosSolicitudes').on('submit', function(e){
        e.preventDefault();
        buscarSolicitudesMateriales();
    });

    $('#btnExportarExcel').on('click', function(){
        exportarExcelSolicitudes();
    });

    $('#btnImprimir').on('click', function(){
        var filtrosAplicados = {
            "Bodega Principal": $('#id_bodega_principal option:selected').text() || 'Todas',
            "Bodega Secundaria": $('#id_bodega_secundaria option:selected').text() || 'Todas',
            "Estado Solicitud": $('#id_estado option:selected').text() || 'Todos',
            "Artículo": $('#id_articulo option:selected').text() || 'Todos',
            "Estado Solicitud Artículo": $('#id_estado_articulo option:selected').text() || 'Todos'
        };
        const resultados = obtenerDatosDeTabla('tablaSolicitudMateriales');
        exportarTablaAPDF(resultados, filtrosAplicados);
    });

    buscarSolicitudesMateriales();
});

function cargarCombos() {
    $.post('../procesos/reportes/solicitud_materiales_reporte.php', {action:'listar_bodegas'}, function(data){
        let bodegas = JSON.parse(data);
        let $principal = $('#id_bodega_principal');
        let $secundaria = $('#id_bodega_secundaria');
        $principal.empty().append('<option value="">Todas</option>');
        $secundaria.empty().append('<option value="">Todas</option>');
        bodegas.forEach(b => {
            $principal.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
            $secundaria.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
        });
        $principal.select2({width:'200px', placeholder:'Bodega principal', allowClear:true});
        $secundaria.select2({width:'200px', placeholder:'Bodega secundaria', allowClear:true});
    });
    $.post('../procesos/reportes/solicitud_materiales_reporte.php', {action:'listar_estados_solicitud'}, function(data){
        let estados = JSON.parse(data);
        let $combo = $('#id_estado');
        $combo.empty().append('<option value="">Todos</option>');
        estados.forEach(e => {
            $combo.append(`<option value="${e.id_estado_solicitud}">${e.estado}</option>`);
        });
        $combo.select2({width:'200px', placeholder:'Estado solicitud', allowClear:true});
    });
    $.post('../procesos/reportes/solicitud_materiales_reporte.php', {action:'listar_estados_articulo'}, function(data){
        let estados = JSON.parse(data);
        let $combo = $('#id_estado_articulo');
        $combo.empty().append('<option value="">Todos</option>');
        estados.forEach(e => {
            $combo.append(`<option value="${e.id_estado_solicitud_articulos}">${e.estado}</option>`);
        });
        $combo.select2({width:'200px', placeholder:'Estado Artículo', allowClear:true});
    });
    $.post('../procesos/reportes/solicitud_materiales_reporte.php', {action:'listar_articulos'}, function(data){
        let articulos = JSON.parse(data);
        let $combo = $('#id_articulo');
        $combo.empty().append('<option value="">Todos</option>');
        articulos.forEach(a => {
            $combo.append(`<option value="${a.id_producto}">${a.nombre}</option>`);
        });
        $combo.select2({width:'200px', placeholder:'Artículo', allowClear:true});
    });
}

function buscarSolicitudesMateriales() {
    let datos = {
        action: 'listar_reporte_solicitudes',
        fecha_desde: $('#fecha_desde').val(),
        fecha_hasta: $('#fecha_hasta').val(),
        id_bodega_principal: $('#id_bodega_principal').val(),
        id_bodega_secundaria: $('#id_bodega_secundaria').val(),
        id_estado: $('#id_estado').val(),
        id_articulo: $('#id_articulo').val(),
        id_estado_articulo: $('#id_estado_articulo').val()
    };  
    $.post('../procesos/reportes/solicitud_materiales_reporte.php', datos, function(data){
        let solicitudes = JSON.parse(data);
        pintarTablaSolicitudes(solicitudes);
    });
}

function pintarTablaSolicitudes(solicitudes){
    let $tabla = $('#tablaSolicitudMateriales');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    if(!Array.isArray(solicitudes) || solicitudes.length === 0){
        //$tbody.append('<tr><td colspan="8">No hay resultados para los filtros seleccionados.</td></tr>');
    }else {
        solicitudes.forEach(r => {
            $tbody.append(`<tr>
                <td>${r.id_solicitud_material ?? ""}</td>
                <td>${r.fecha_solicitud ?? ""}</td>
                <td>${r.bodega_principal ?? ""}</td>
                <td>${r.bodega_secundaria ?? ""}</td>
                <td>${r.articulo ?? ""}</td>
                <td>${r.cantidad ?? ""}</td>
                <td>${r.estado_solicitud ?? ""}</td>
                <td>${r.estado_solicitud_articulo ?? ""}</td>
            </tr>`);
        });
    }
    $tabla.DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
}

// --- AJUSTE EXPORTAR A EXCEL: header azul y negrita ---
function exportarExcelSolicitudes(){
    var table = document.getElementById('tablaSolicitudMateriales');
    var rows = Array.from(table.querySelectorAll('tbody tr'));
    var headerCells = Array.from(table.querySelectorAll('thead tr th'));
    var headers = headerCells.map(cell => cell.innerText);

    var bodyRows = [];
    rows.forEach(row => {
        var cols = Array.from(row.cells).map(cell => cell.innerText);
        bodyRows.push(cols);
    });

    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('SolicitudMateriales');

    // Header azul/negrita
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

    // Body
    bodyRows.forEach(row => {
        let excelRow = worksheet.addRow(row);
        // Formato decimal en columna "Cantidad" si quieres (col 6), opcional
        excelRow.getCell(6).alignment = { horizontal: "right" };
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
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "ReporteSolicitudMateriales.xlsx");
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
//Exportar a PDF
function exportarTablaAPDF(resultados, filtrosAplicados) {
    const imgElement = document.getElementById("logoEmpresa");
    const logoBase64 = getBase64Image(imgElement); 
    const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });

   // Logo (si tienes uno en base64, por ejemplo PNG)
   if (logoBase64) {
     doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60); // x, y, width, height
    }

    doc.setFontSize(24);
    doc.setTextColor(40, 55, 71);
    const y = 40;
    const pageWidth = doc.internal.pageSize.getWidth();
    doc.text("Reporte de Solicitud de Materiales", pageWidth / 2, y, { align: "center" });

    const x = 70;
    doc.setFontSize(16);
    doc.setTextColor(40, 55, 71);
    doc.text("DESDE: " +  $('#fecha_desde').val() + " HASTA: " + $('#fecha_hasta').val(), pageWidth / 2, x, { align: "center" });

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

    const columns = [
        { header: 'ID Solicitud', dataKey: 'campo1' },
        { header: 'Fecha', dataKey: 'campo2' },
        { header: 'Lugar', dataKey: 'campo3' },
        { header: 'Ubicacion', dataKey: 'campo4' },
        { header: 'Artículo', dataKey: 'campo5' },
        { header: 'Cantidad', dataKey: 'campo6' },
        { header: 'Estado Solicitud', dataKey: 'campo7' },
        { header: 'Estado Solicitud Artículo', dataKey: 'campo8' }
    ];

    doc.autoTable({
        columns: columns,
        body: resultados,
        startY: filtroY,
        styles: {
            fontSize: 10,
            cellPadding: 5,
            fillColor: [230, 240, 255]
        },
        headStyles: {
            fillColor: [52, 73, 94],
            textColor: [255, 255, 255],
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

    doc.save('Reporte_Solicitud_Materiales.pdf');
}

function obtenerDatosDeTabla(tablaId) {
    const resultados = [];
    const table = $('#' + tablaId).DataTable();
    const allData = table.rows().data().toArray();
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
</script>
</body>
</html>
<?php
}else{
    header("location:index.php");
}
?>