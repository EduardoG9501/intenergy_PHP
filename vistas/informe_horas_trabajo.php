<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){
/*HABILITAR ACCESO AL MENU*/     
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "HORAS DE TRABAJO");
if (!$acceso) {
    header("location:../index.php");
    exit();
}   
?>
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES → HORAS DE TRABAJO</title>
    <?php require_once "menu.php"; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- ExcelJS for Excel export with styles -->
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
            #tablaHorasTrabajo, #tablaHorasTrabajo * { visibility: visible; }
            #tablaHorasTrabajo { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .fa-file-pdf:before { color: red; }
        tr.eliminado { background: #efe0e0  !important; color: #050505ff  !important; }
        tfoot tr { background: #3c8dbc !important; font-weight: bold; }
        tfoot td { color: #f6f7f8ff !important; font-size: 17px; }
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">REPORTE DE HORAS DE TRABAJO</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosHorasTrabajo" class="form-inline" autocomplete="off">
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
               </div>
               <div class="col-sm-3">
                    <label class="label-custom" for="id_responsable_obra">Responsable Obra:</label>
                    <select id="id_responsable_obra" name="id_responsable_obra" class="form-control" style="width:220px"><option value="">Todos</option></select>
                    <label class="label-custom" for="id_empleado">Empleado:</label>
                    <select id="id_empleado" name="id_empleado" class="form-control" style="width:220px"><option value="">Todos</option></select>
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
    <table id="tablaHorasTrabajo" class="table table-bordered table-striped">
        <thead>
            <tr style="background:#3c8dbc; color:white;">
                <th>ID</th>
                <th>Fecha</th>
                <th>Lugar</th>
                <th>Ubicaciòn</th>
                <th>Responsable Obra</th>
                <th>Empleado</th>
                <th># Horas</th>
                <th>Costo x Hora</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se llena por JS -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right;">TOTAL:</td>
                <td id="totalHoras">0</td>
                <td id="totalCostoHora">$0.00</td>
                <td id="totalTotal">$0.00</td>
                <td></td>
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
    document.getElementById('fecha_desde').value = fechaHoy;
    document.getElementById('fecha_hasta').value = fechaHoy;

    // Inicializar combos
    $('#id_bodega_principal, #id_bodega_secundaria, #id_responsable_obra, #id_empleado').select2({width:'220px', placeholder:'Seleccione una opción', allowClear:true});
    cargarCombos();

    $('#formFiltrosHorasTrabajo').on('submit', function(e){
        e.preventDefault();
        buscarHorasTrabajo();
    });

    $('#btnExportarExcel').on('click', function(){
        exportarExcelHorasTrabajo();
    });

    $('#btnImprimir').on('click', function(){
        var filtrosAplicados = {
            "B. Lugar": $('#id_bodega_principal option:selected').text() || 'Todas',
            "B. Ubicaciòn": $('#id_bodega_secundaria option:selected').text() || 'Todas',
            "Responsable Obra": $('#id_responsable_obra option:selected').text() || 'Todos',
            "Empleado": $('#id_empleado option:selected').text() || 'Todos'
        };
        const resultados = obtenerDatosDeTabla('tablaHorasTrabajo');
        exportarTablaAPDF(resultados, filtrosAplicados);
    });

    buscarHorasTrabajo();
});

function cargarCombos() {
    $.post('../procesos/reportes/informes_hora_trabajo.php', {action:'bodegas'}, function(data){
        let bodegas = JSON.parse(data);
        let $principal = $('#id_bodega_principal');
        let $secundaria = $('#id_bodega_secundaria');
        $principal.empty().append('<option value="">Todas</option>');
        $secundaria.empty().append('<option value="">Todas</option>');
        bodegas.forEach(b => {
            $principal.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
            $secundaria.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
        });
        $principal.select2({width:'220px', placeholder:'Bodega principal', allowClear:true});
        $secundaria.select2({width:'220px', placeholder:'Bodega secundaria', allowClear:true});
    });
    $.post('../procesos/reportes/informes_hora_trabajo.php', {action:'responsables'}, function(data){
        let responsables = JSON.parse(data);
        let $combo = $('#id_responsable_obra');
        $combo.empty().append('<option value="">Todos</option>');
        responsables.forEach(e => {
            $combo.append(`<option value="${e.id_empleados}">${e.nombre}</option>`);
        });
        $combo.select2({width:'220px', placeholder:'Responsable', allowClear:true});
    });
    $.post('../procesos/reportes/informes_hora_trabajo.php', {action:'empleados'}, function(data){
        let empleados = JSON.parse(data);
        let $combo = $('#id_empleado');
        $combo.empty().append('<option value="">Todos</option>');
        empleados.forEach(e => {
            $combo.append(`<option value="${e.id_empleados}">${e.nombre}</option>`);
        });
        $combo.select2({width:'220px', placeholder:'Empleado', allowClear:true});
    });
}

function buscarHorasTrabajo() {
    let datos = {
        action: 'listar_reporte',
        fecha_desde: $('#fecha_desde').val(),
        fecha_hasta: $('#fecha_hasta').val(),
        id_bodega_principal: $('#id_bodega_principal').val(),
        id_bodega_secundaria: $('#id_bodega_secundaria').val(),
        id_responsable_obra: $('#id_responsable_obra').val(),
        id_empleado: $('#id_empleado').val()
    };
    $.post('../procesos/reportes/informes_hora_trabajo.php', datos, function(data){
        let horas = JSON.parse(data);
        pintarTablaHorasTrabajo(horas);
    });
}

function pintarTablaHorasTrabajo(rows){
    let $tabla = $('#tablaHorasTrabajo');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    if(!Array.isArray(rows) || rows.length === 0){
        //$tbody.append('<tr><td colspan="10">No hay resultados para los filtros seleccionados.</td></tr>');
    }else {
        rows.forEach(r => {
            let fila = `
                <tr${r.estado === 'Eliminado' ? ' class="eliminado"':''}>
                    <td>${r.id_horas_trabajo_empleado ?? ""}</td>
                    <td>${r.fecha ?? ""}</td>
                    <td>${r.bodega_principal ?? ""}</td>
                    <td>${r.bodega_secundaria ?? ""}</td>
                    <td>${r.responsable ?? ""}</td>
                    <td>${r.empleado ?? ""}</td>
                    <td>${r.numero_horas ?? ""}</td>
                    <td>${r.costo_por_hora ?? ""}</td>
                    <td>${r.total ?? ""}</td>
                    <td>${r.estado ?? ""}</td>
                </tr>`;
            $tbody.append(fila);
        });
    }
    // CREA EL DATATABLE Y AGREGA EL EVENTO QUE ACTUALIZA TOTALES AL FILTRAR/BUSCAR/PAGINAR
    let dt = $tabla.DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
    calcularTotalesHorasTrabajo(dt);
    $tabla.on('draw.dt', function() {
        calcularTotalesHorasTrabajo(dt);
    });
}

// SUMA Y MUESTRA LOS TOTALES SOLO DE FILAS VISIBLES/FILTRADAS
function calcularTotalesHorasTrabajo(dtTable) {
    let totalHoras = 0, totalCostoHora = 0, totalTotal = 0;
    if (!dtTable) return;
    dtTable.rows({ search: 'applied' }).every(function(){
        let data = this.data();
        totalHoras += parseFloat(data[6]) || 0;
        totalCostoHora += parseFloat(data[7]) || 0;
        totalTotal += parseFloat(data[8]) || 0;
    });
    $('#totalHoras').text(totalHoras.toFixed(2));
    $('#totalCostoHora').text('$' + totalCostoHora.toFixed(2));
    $('#totalTotal').text('$' + totalTotal.toFixed(2));
}

// EXPORTAR A EXCEL CON TOTALES Y SOMBREADO (USANDO ExcelJS)
function exportarExcelHorasTrabajo(){
    const table = document.getElementById('tablaHorasTrabajo');
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const headerCells = Array.from(table.querySelectorAll('thead tr th'));
    const header = headerCells.map(cell => cell.innerText);

    // Crea libro y hoja
    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('Horas Trabajo');

    // Header (azul claro)
    let headerRow = worksheet.addRow(header);
    headerRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FF000000" } };
        cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: "5fadda" }
        };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });

    // Datos
    rows.forEach(row => {
        let dataRow = Array.from(row.cells).map(cell => cell.innerText);
        worksheet.addRow(dataRow);
    });

    // Fila de totales
    let totalRowData = [
        'TOTAL:', '', '', '', '', '',
        document.getElementById('totalHoras').innerText,
        document.getElementById('totalCostoHora').innerText,
        document.getElementById('totalTotal').innerText,
        ''
    ];
    let totalRow = worksheet.addRow(totalRowData);

    // Aplica fondo celeste y negrita a TODAS las celdas de la fila total
    totalRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FF000000" } };
        cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: "5fadda" } // Celeste
        };
        cell.alignment = { horizontal: "right", vertical: "middle" };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });
    // Opcional: alineación izquierda SOLO para la celda "TOTAL:"
    totalRow.getCell(1).alignment = { horizontal: "left", vertical: "middle" };

    // Ajustar ancho de columnas automáticamente
    worksheet.columns.forEach(function(column, i) {
        let maxLength = 10;
        column.eachCell({ includeEmpty: true }, function(cell) {
            let cellValue = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, cellValue.length + 2);
        });
        column.width = maxLength;
    });

    // Exporta
    workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Reporte_Horas_Trabajo.xlsx");
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

    if (logoBase64) {
        doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
    }
    doc.setFontSize(24);
    doc.setTextColor(40, 55, 71);
    const y = 40;
    const pageWidth = doc.internal.pageSize.getWidth();
    doc.text("Reporte de Horas de Trabajo", pageWidth / 2, y, { align: "center" });
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

    // --- Añadir totales a PDF ---
    // Calcular los totales igual que en la tabla
    let totalHoras = 0, totalCostoHora = 0, totalTotal = 0;
    resultados.forEach(row => {
        totalHoras += parseFloat(row.campo7) || 0;
        totalCostoHora += parseFloat(row.campo8) || 0;
        totalTotal += parseFloat(row.campo9) || 0;
    });

    const columns = [
        { header: 'ID', dataKey: 'campo1' },
        { header: 'Fecha', dataKey: 'campo2' },
        { header: 'Lugar', dataKey: 'campo3' },
        { header: 'Ubicacion', dataKey: 'campo4' },
        { header: 'Responsable Obra', dataKey: 'campo5' },
        { header: 'Empleado', dataKey: 'campo6' },
        { header: '# Horas', dataKey: 'campo7' },
        { header: 'Costo x Hora', dataKey: 'campo8' },
        { header: 'Total', dataKey: 'campo9' },
        { header: 'Estado', dataKey: 'campo10' }
    ];

    // Agrega la fila de totales al final del PDF
    doc.autoTable({
        columns: columns,
        body: resultados.concat([{
            campo1: 'TOTAL:',
            campo2: '', campo3: '', campo4: '', campo5: '', campo6: '',
            campo7: totalHoras.toFixed(2),
            campo8: '$' + totalCostoHora.toFixed(2),
            campo9: '$' + totalTotal.toFixed(2),
            campo10: ''
        }]),
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
        footStyles: {
            fillColor: [244, 176, 132],
            fontStyle: 'bold'
        },
        margin: { left: 40, right: 40 }
    });

    const fecha = new Date().toLocaleString();
    doc.setFontSize(10);
    doc.setTextColor(127, 140, 141);
    doc.text(`Generado el: ${fecha} | Usuario:` +  '<?php echo $_SESSION['usuario']; ?>', 40, doc.internal.pageSize.getHeight() - 30);

    doc.save('Reporte_Horas_Trabajo.pdf');
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
            campo8: row[7],
            campo9: row[8],
            campo10: row[9]
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