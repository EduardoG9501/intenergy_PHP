<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "INFORMES DIARIO");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES → INFORME DIARIO</title>
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
            #tablaInformeDiario, #tablaInformeDiario * { visibility: visible; }
            #tablaInformeDiario { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .fa-file-pdf:before { color: red; }
        /* Modal zoom para imágenes en informes generales */
#imgZoomModalGeneral {
    display: none;
    position: fixed;
    z-index: 99999;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(44, 62, 80, 0.35);
    cursor: pointer;
    align-items: center;
    justify-content: center;
}
#imgZoomModalGeneral.visible {
    display: flex;
}
#imgZoomModalGeneral img {
    max-width: 90vw;
    max-height: 90vh;
    box-shadow: 0 2px 28px #3393cb;
    border-radius: 14px;
    border: 4px solid #cde6fe;
    background: #fff;
}
.img-zoom-thumb {
    cursor: zoom-in;
    transition: box-shadow 0.2s, border 0.2s;
}
.img-zoom-thumb:hover {
    box-shadow: 0 2px 12px #3393cb;
    border: 2px solid #cde6fe;
}
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">REPORTE DE INFORME DIARIO</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosInformeDiario" class="form-inline" autocomplete="off">
            <div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
                <div class="col-sm-2">
                    <label class="label-custom" for="fecha_desde">Fecha desde:</label>
                    <input type="date" id="fecha_desde" name="fecha_desde" class="form-control">
                    <label class="label-custom" for="fecha_hasta">Fecha hasta:</label>
                    <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control">
                </div> 
                <div class="col-sm-3">
                    <label class="label-custom" for="tipo_informe">Tipo de informe:</label>
                    <select id="tipo_informe" name="tipo_informe" class="form-control" style="width:200px">
                        <option value="">Seleccione</option>
                        <option value="general">General</option>
                        <option value="interno">Empleado Interno</option>
                        <option value="externo">Empleado Externo</option>
                        <option value="articulo">Artículo</option>
                    </select>
                    <label class="label-custom" id="label_busqueda" style="display:none;" for="busqueda_combo"></label>
                    <select id="busqueda_combo" name="busqueda_combo" class="form-control" style="width:200px;display:none;"><option value="">Todos</option></select>
                </div>
                 <div class="col-sm-3">
                    <label class="label-custom" for="id_bodega_principal">B. Lugar:</label>
                    <select id="id_bodega_principal" name="id_bodega_principal" class="form-control" style="width: 250px;"><option value="">Todas</option></select>
                    <label class="label-custom" for="id_bodega_secundaria">B. Ubicaciòn:</label>
                    <select id="id_bodega_secundaria" name="id_bodega_secundaria" class="form-control" style="width:250px"><option value="">Todas</option></select>
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
    <table id="tablaInformeDiario" class="table table-bordered table-striped">
        <thead>
            <tr id="cabecera_tabla">
                <!-- Se llena dinámicamente según tipo de informe -->
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
    $('#id_bodega_principal, #id_bodega_secundaria, #tipo_informe, #busqueda_combo').select2({
        width:'200px', placeholder:'Seleccione una opción', allowClear:true
    });

    cargarCombos();

    $('#tipo_informe').on('change', function(){
        mostrarBusquedaCombo();
    });

    $('#formFiltrosInformeDiario').on('submit', function(e){
        e.preventDefault();
        buscarInformeDiario();
    });

    $('#btnExportarExcel').on('click', function(){
        exportarExcelInformeDiario();
    });

   $('#btnImprimir').on('click', async function(){
    var filtrosAplicados = {
        "Lugar": $('#id_bodega_principal option:selected').text() || 'Todas',
        "Ubicaciòn": $('#id_bodega_secundaria option:selected').text() || 'Todas',
        "Tipo Informe": $('#tipo_informe option:selected').text() || 'Todos',
        "Búsqueda": $('#busqueda_combo option:selected').text() || 'Todos'
    };
    let tipo = $('#tipo_informe').val();
    if (tipo == 'general') {
        await exportarTablaAPDF(window.__ULTIMO_JSON_GENERAL || [], filtrosAplicados);
    } else {
        const resultados = obtenerDatosDeTabla('tablaInformeDiario');
        await exportarTablaAPDF(resultados, filtrosAplicados);
    }
});

    buscarInformeDiario();
});

function cargarCombos() {
    $.post('../procesos/reportes/informes_diarios_avanzado.php', {action:'listar_bodegas'}, function(data){
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
}

function mostrarBusquedaCombo() {
    let tipo = $('#tipo_informe').val();
    let $label = $('#label_busqueda');
    let $combo = $('#busqueda_combo');
    $combo.hide();
    $label.hide();
    
    $combo.select2({ width:'200px', placeholder:'escribir el : ' + tipo, allowClear:true });

    if (tipo == 'interno') {
        $label.text('Empleado interno:').show();
        $combo.show();
        $.post('../procesos/reportes/informes_diarios_avanzado.php', {action:'listar_empleados_internos'}, function(data){
            let empleados = JSON.parse(data);
            $combo.empty().append('<option value="">Todos</option>');
            empleados.forEach(e => {
                $combo.append(`<option value="${e.id_empleados}">${e.nombre}</option>`);
            });
        });
    } else if (tipo == 'externo') {
        $label.text('Empleado externo:').show();
        $combo.show();
        $.post('../procesos/reportes/informes_diarios_avanzado.php', {action:'listar_empleados_externos'}, function(data){
            let empleados = JSON.parse(data);
            $combo.empty().append('<option value="">Todos</option>');
            empleados.forEach(e => {
                $combo.append(`<option value="${e.id_empleado_externo}">${e.nombre}</option>`);
            });
        });
    } else if (tipo == 'articulo') {
        $label.text('Artículo:').show();
        $combo.show();
        $.post('../procesos/reportes/informes_diarios_avanzado.php', {action:'listar_articulos'}, function(data){
            let articulos = JSON.parse(data);
            $combo.empty().append('<option value="">Todos</option>');
            articulos.forEach(a => {
                $combo.append(`<option value="${a.id_producto}">${a.nombre}</option>`);
            });
        });
    }
}

function buscarInformeDiario() {
    let tipo = $('#tipo_informe').val();
    let datos = {
        action: 'listar_reporte_informes_diarios_avanzado',
        fecha_desde: $('#fecha_desde').val(),
        fecha_hasta: $('#fecha_hasta').val(),
        id_bodega_principal: $('#id_bodega_principal').val(),
        id_bodega_secundaria: $('#id_bodega_secundaria').val(),
        tipo_informe: tipo
    };
    if (tipo == 'interno') datos.id_empleado = $('#busqueda_combo').val();
    if (tipo == 'externo') datos.id_empleado_externo = $('#busqueda_combo').val();
    if (tipo == 'articulo') datos.id_articulo = $('#busqueda_combo').val();
    $.post('../procesos/reportes/informes_diarios_avanzado.php', datos, function(data){
        let informes = JSON.parse(data);
        pintarTablaInformeDiario(informes, tipo);
    });
}

// --- AJUSTE ROBUSTO: "Resetea" la tabla correctamente ---
function pintarTablaInformeDiario(informes, tipo){
     window.__ULTIMO_JSON_GENERAL = informes; // <-- Guarda el JSON para exportar
    // En tu función pintarTablaInformeDiario, agrega: 
    if (tipo == 'general') {
        pintarTablaInformeGeneral(informes); 
        return;
    }
    let $tabla = $('#tablaInformeDiario');
    let $thead = $tabla.find('thead');
    let $tbody = $tabla.find('tbody');

    // Destruye DataTable ANTES de modificar DOM
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }

    // Define cabecera y cantidad de columnas según tipo
    let cabecera = '';
    if (tipo == 'interno') {
        cabecera = `<tr>
            <th>ID Informe</th>
            <th>Orden Trabajo</th>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Ubicaciòn</th>
            <th>Empleado Interno</th>
            <th>Observación</th>
        </tr>`;
    } else if (tipo == 'externo') {
        cabecera = `<tr>
            <th>ID Informe</th>
            <th>Orden Trabajo</th>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Ubicaciòn</th>
            <th>Empleado Externo</th>
            <th>Observación</th>
        </tr>`;
    } else if (tipo == 'articulo') {
        cabecera = `<tr>
            <th>ID Informe</th>
            <th>Orden Trabajo</th>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Ubicaciòn</th>
            <th>Artículo</th>
            <th>Cantidad</th>
            <th>Observación</th>
        </tr>`;
    } else {
        cabecera = `<tr>
            <th>ID Informe</th>
            <th>Orden Trabajo</th>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Ubicaciòn</th>
        </tr>`;
    }

    // Limpia y repinta
    $thead.html(cabecera);
    $tbody.html('');

    // Rellena filas
    if(Array.isArray(informes) && informes.length > 0){
        informes.forEach(r => {
            let fila = '';
            if (tipo == 'interno') {
                fila = `<td>${r.id_informe ?? ""}</td>
                        <td>${r.identificador ?? ""}</td>
                        <td>${r.fecha ?? ""}</td>
                        <td>${r.bodega_principal ?? ""}</td>
                        <td>${r.bodega_secundaria ?? ""}</td>
                        <td>${r.empleado ?? ""}</td>
                        <td>${r.observacion ?? ""}</td>`;
            } else if (tipo == 'externo') {
                fila = `<td>${r.id_informe ?? ""}</td>
                        <td>${r.identificador ?? ""}</td>
                        <td>${r.fecha ?? ""}</td>
                        <td>${r.bodega_principal ?? ""}</td>
                        <td>${r.bodega_secundaria ?? ""}</td>
                        <td>${r.empleado_externo ?? ""}</td>
                        <td>${r.observacion ?? ""}</td>`;
            } else if (tipo == 'articulo') {
                fila = `<td>${r.id_informe ?? ""}</td>
                        <td>${r.identificador ?? ""}</td>
                        <td>${r.fecha ?? ""}</td>
                        <td>${r.bodega_principal ?? ""}</td>
                        <td>${r.bodega_secundaria ?? ""}</td>
                        <td>${r.articulo ?? ""}</td>
                        <td>${r.cantidad ?? ""}</td>
                        <td>${r.observacion ?? ""}</td>`;
            } else {
                fila = `<td>${r.id_informe ?? ""}</td>
                        <td>${r.identificador ?? ""}</td>
                        <td>${r.fecha ?? ""}</td>
                        <td>${r.bodega_principal ?? ""}</td>
                        <td>${r.bodega_secundaria ?? ""}</td>`;
            }
            $tbody.append(`<tr>${fila}</tr>`);
        });
    }

    // Inicializa DataTable SOLO después de repintar
    $tabla.DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
}

// --- NUEVA FUNCION EXPORTAR EXCEL CON HEADER AZUL/LETRAS BLANCAS/NEGRITA ---
 async function exportarExcelInformeDiario(){
    let workbook = new ExcelJS.Workbook(); // Declarada solo UNA VEZ
    let worksheet; // Declarada solo UNA VEZ
    let headers;
    let tipo = $('#tipo_informe').val();
    if (tipo !== 'general') {
        // Modo clásico para los otros tipos
        var table = document.getElementById('tablaInformeDiario');
        var headerCells = Array.from(table.querySelectorAll('thead tr th'));
        headers = headerCells.map(cell => cell.innerText);

        var bodyRows = [];
        Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
            var cols = Array.from(row.cells).map(cell => cell.innerText);
            bodyRows.push(cols);
        });
 
        worksheet = workbook.addWorksheet('InformeDiario');

        // Header azul/negrita
        let headerRow = worksheet.addRow(headers);
        headerRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: "FF3393CB" }
            };
            cell.alignment = { horizontal: "center", vertical: "middle" };
            cell.border = {
                top: {style:'thin'}, left: {style:'thin'},
                bottom: {style:'thin'}, right: {style:'thin'}
            };
        });

        // Body rows
        bodyRows.forEach(row => {
            let excelRow = worksheet.addRow(row);
            row.forEach((col, idx) => {
                excelRow.getCell(idx + 1).alignment = { horizontal: "center", vertical: "middle" };
            });
        });

        // Auto width
        worksheet.columns.forEach(function(column, i) {
            let maxLength = 10;
            column.eachCell({ includeEmpty: true }, function(cell) {
                let cellValue = cell.value ? cell.value.toString() : "";
                maxLength = Math.max(maxLength, cellValue.length + 2);
            });
            column.width = maxLength;
        });

        workbook.xlsx.writeBuffer().then(function(buffer) {
            saveAs(new Blob([buffer], { type: "application/octet-stream" }), "ReporteInformeDiario.xlsx");
        });
        return;
    }
   else {
        let informes = window.__ULTIMO_JSON_GENERAL || []; 
        worksheet = workbook.addWorksheet('InformeDiario');

        // 1. Obtener la máxima cantidad de imágenes de todos los informes
        let maxImgs = 0;
        for (const r of informes) {
            if (Array.isArray(r.imagenes)) maxImgs = Math.max(maxImgs, r.imagenes.length || 0);
        }

        // 2. Cabecera expandida (tantas columnas de imágenes como maxImgs)
        headers = ['ID Informe', 'Orden Trabajo', 'Fecha'];
        for (let i = 1; i <= maxImgs; i++) headers.push(`Imagen ${i}`);
        headers = [...headers, 'Lugar', 'Ubicación', 'Observación'];

        let headerRow = worksheet.addRow(headers);
        headerRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: "FF3393CB" }
            };
            cell.alignment = { horizontal: "center", vertical: "middle" };
            cell.border = {
                top: {style:'thin'}, left: {style:'thin'},
                bottom: {style:'thin'}, right: {style:'thin'}
            };
        });

        for (const r of informes) {
            // ---- Fila encabezado de informe (resaltada, solo datos principales, SIN detalles ni imágenes) ----
         let encabezadoRow = worksheet.addRow([
           r.id_informe ?? "",
        r.identificador ?? "",
        r.fecha ?? "",
        ...Array(maxImgs).fill(""), // columnas imágenes VACÍAS
        r.bodega_principal ?? "",
        r.bodega_secundaria ?? "",
        r.observacion ?? ""
    ]);
    // Aplica color y negrita SOLO en esta fila de encabezado
    encabezadoRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FF000000" } };
        cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: "FFDAECF7" } // Azul claro
        };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });

    // ---- Fila detalle (sin color especial) ----
    let articulosStr = ((r.articulos||[]).length > 0) ? (r.articulos||[]).map(a=>`${a.nombre} (${a.cantidad}) [Lote: ${a.lote||'-'}]`).join('\n') : "";
    let internosStr = (r.empleados_internos||[]).join('\n');
    let externosStr = (r.empleados_externos||[]).join('\n');
    let detalleRow = worksheet.addRow([
        articulosStr, internosStr, externosStr, ...Array(maxImgs).fill(""), "", "", ""
    ]);
    detalleRow.eachCell((cell, idx) => {
        cell.alignment = { horizontal: "left", vertical: "top", wrapText: true };
        cell.font = { size: 10 };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });

            // Inserta miniaturas: cada una en su propia columna
            if (Array.isArray(r.imagenes)) {
                for (let i = 0; i < r.imagenes.length; i++) {
                    let imgUrl = r.imagenes[i];
                    if (imgUrl) {
                        let base64 = await getBase64FromUrl("../"+imgUrl);
                        let imageId = workbook.addImage({
                            base64: base64,
                            extension: 'png'
                        });
                        // ColIndex para imágenes arranca en 4 (D)
                        let colIndex = 4 + i;
                        worksheet.addImage(imageId, {
                            tl: { col: colIndex - 1 + 0.15, row: detalleRow.number - 1 + 0.11 },
                            ext: { width: 60, height: 48 },
                            editAs: "absolute"
                        });
                    }
                }
            }
        }

        // Ajusta el ancho de las columnas de imágenes
        worksheet.columns.forEach(function(column, i) {
            if (i >= 3 && i < 3 + maxImgs) column.width = 13;
            else column.width = 20;
        });

        worksheet.views = [{ state: 'frozen', ySplit: 1 }];

        workbook.xlsx.writeBuffer().then(function(buffer) {
            saveAs(new Blob([buffer], { type: "application/octet-stream" }), "ReporteInformeDiario.xlsx");
        });
        return;
    }
}

function getBase64Image(imgElement) {
    const canvas = document.createElement("canvas");
    canvas.width = imgElement.width;
    canvas.height = imgElement.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
    return canvas.toDataURL("image/png");
}

async function exportarTablaAPDF(resultados, filtrosAplicados) {
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
    doc.text("Reporte de Informe Diario", pageWidth / 2, y, { align: "center" });

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

    let tipo = $('#tipo_informe').val();

    if (tipo == 'general') {
        let columns = [
            { header: 'ID Informe', dataKey: 'id_informe' },
            { header: 'Orden Trabajo', dataKey: 'identificador' },
            { header: 'Fecha', dataKey: 'fecha' },
            { header: 'Lugar', dataKey: 'bodega_principal' },
            { header: 'Ubicación', dataKey: 'bodega_secundaria' },
            { header: 'Observación', dataKey: 'observacion' }
        ];

        let body = [];

        for (const r of resultados) {
            // Fila principal
            body.push({
                id_informe: r.id_informe ?? "",
                identificador: r.identificador ?? "",
                fecha: r.fecha ?? "",
                bodega_principal: r.bodega_principal ?? "",
                bodega_secundaria: r.bodega_secundaria ?? "",
                observacion: r.observacion ?? ""
            });

            // Prepara las imágenes (máx 5 miniaturas por fila)
            let imgsBase64 = [];
            if (Array.isArray(r.imagenes)) {
                for (let i = 0; i < r.imagenes.length; i++) {
                    if (i >= 8) break; // no más de 5 imágenes por fila
                    let imgUrl = r.imagenes[i];
                    if (imgUrl) {
                        let base64 = await getBase64FromUrl("../"+imgUrl);
                        imgsBase64.push(base64);
                    }
                }
            }

            // Fila detalle en columnas igual al grid
            body.push({
                id_informe: {
                    content: [
                        { text: "Artículos:", styles: { fontStyle: 'bold' } },
                        { text: ((r.articulos||[]).length > 0) ? (r.articulos||[]).map(a=>`${a.nombre} (${a.cantidad}) [Lote: ${a.lote||'-'}]`).join('\n') : "—" }
                    ].map(obj => obj.text).join("\n"),
                    colSpan: 1,
                    styles: { halign: 'left', fontStyle:'italic', cellWidth: 120 }
                },
                identificador: {
                    content: [
                        { text: "Empleado Interno:", styles: { fontStyle: 'bold' } },
                        { text: (r.empleados_internos||[]).length > 0 ? (r.empleados_internos||[]).join('\n') : "—" }
                    ].map(obj => obj.text).join("\n"),
                    colSpan: 1,
                    styles: { halign: 'left', fontStyle:'italic', cellWidth: 110 }
                },
                fecha: {
                    content: [
                        { text: "Empleado Externo:", styles: { fontStyle: 'bold' } },
                        { text: (r.empleados_externos||[]).length > 0 ? (r.empleados_externos||[]).join('\n') : "—" }
                    ].map(obj => obj.text).join("\n"),
                    colSpan: 1,
                    styles: { halign: 'left', fontStyle:'italic', cellWidth: 110 }
                },
                bodega_principal: {
                    content: [
                        { text: "Imágenes:", styles: { fontStyle: 'bold' } },
                        { text: (imgsBase64.length > 0) ? "[miniaturas debajo]" : "—" }
                    ].map(obj => obj.text).join("\n"),
                    colSpan: 3,
                    styles: { halign: 'left', fontStyle:'italic', cellWidth: 250 }
                },
                __imagenesMiniaturas: imgsBase64 // clave especial para luego dibujar las miniaturas
            });
        }

        // Genera la tabla
        doc.autoTable({
            columns: columns,
            body: body,
            startY: filtroY,
            styles: {
                fontSize: 10,
                cellPadding: 5,
                fillColor: [230, 240, 255]
            },
            headStyles: {
              fillColor: [51, 147, 203], // Azul del ejemplo
               textColor: [255, 255, 255], // Blanco
              fontStyle: 'bold'
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            },
            margin: { left: 40, right: 40 },
            didParseCell: function (data) {
                if (data.row.section === 'body' && data.cell.raw && data.cell.raw.colSpan) {
                    data.cell.colSpan = data.cell.raw.colSpan;
                    data.cell.styles = Object.assign(data.cell.styles, data.cell.raw.styles || {});
                }
            },
            didDrawCell: function (data) {
                // Dibuja miniaturas si hay imágenes (solo en columna de imágenes)
                if (
                    data.section === 'body' &&
                    data.row.raw &&
                    Array.isArray(data.row.raw.__imagenesMiniaturas) &&
                    data.column.index === 3 // la columna "bodega_principal" es la de imágenes (por colSpan:3)
                ) {
                    const imgs = data.row.raw.__imagenesMiniaturas;
                    if (imgs && imgs.length > 0) {
                        let x = data.cell.x + 5;
                        let y = data.cell.y + 20;
                        let width = 50;
                        let height = 40;
                        let gap = 5;
                        for (let i = 0; i < imgs.length; i++) {
                            if (imgs[i]) {
                                try {
                                    doc.addImage(imgs[i], 'PNG', x, y, width, height);
                                } catch(e) {}
                                x += width + gap;
                            }
                        }
                    }
                }
            }
        });

        const fecha = new Date().toLocaleString();
        doc.setFontSize(10);
        doc.setTextColor(127, 140, 141);
        doc.text(`Generado el: ${fecha} | Usuario:` +  '<?php echo $_SESSION['usuario']; ?>', 40, doc.internal.pageSize.getHeight() - 30);

        doc.save('Reporte_Informe_Diario.pdf');
        return;
    } 
     // BLOQUE PARA LOS OTROS TIPOS DE INFORME
    let columns = [];
    let body = [];
    if (tipo == 'interno') {
        columns = [
            { header: 'ID Informe', dataKey: 'campo1' },
            { header: 'Orden Trabajo', dataKey: 'campo2' },
            { header: 'Fecha', dataKey: 'campo3' },
            { header: 'Lugar', dataKey: 'campo4' },
            { header: 'Ubicación', dataKey: 'campo5' },
            { header: 'Empleado Interno', dataKey: 'campo6' },
            { header: 'Observación', dataKey: 'campo7' }
        ];
        body = resultados.map(r => ({
            campo1: r.campo1,
            campo2: r.campo2,
            campo3: r.campo3,
            campo4: r.campo4,
            campo5: r.campo5,
            campo6: r.campo6,
            campo7: r.campo7
        }));
    } else if (tipo == 'externo') {
        columns = [
            { header: 'ID Informe', dataKey: 'campo1' },
            { header: 'Orden Trabajo', dataKey: 'campo2' },
            { header: 'Fecha', dataKey: 'campo3' },
            { header: 'Lugar', dataKey: 'campo4' },
            { header: 'Ubicación', dataKey: 'campo5' },
            { header: 'Empleado Externo', dataKey: 'campo6' },
            { header: 'Observación', dataKey: 'campo7' }
        ];
        body = resultados.map(r => ({
            campo1: r.campo1,
            campo2: r.campo2,
            campo3: r.campo3,
            campo4: r.campo4,
            campo5: r.campo5,
            campo6: r.campo6,
            campo7: r.campo7
        }));
    } else if (tipo == 'articulo') {
        columns = [
            { header: 'ID Informe', dataKey: 'campo1' },
            { header: 'Orden Trabajo', dataKey: 'campo2' },
            { header: 'Fecha', dataKey: 'campo3' },
            { header: 'Lugar', dataKey: 'campo4' },
            { header: 'Ubicación', dataKey: 'campo5' },
            { header: 'Artículo', dataKey: 'campo6' },
            { header: 'Cantidad', dataKey: 'campo7' },
            { header: 'Observación', dataKey: 'campo8' }
        ];
        body = resultados.map(r => ({
            campo1: r.campo1,
            campo2: r.campo2,
            campo3: r.campo3,
            campo4: r.campo4,
            campo5: r.campo5,
            campo6: r.campo6,
            campo7: r.campo7,
            campo8: r.campo8
        }));
    }
    // Si hay columnas y datos, genera el PDF
    if (columns.length > 0 && body.length > 0) {
        doc.autoTable({
            columns: columns,
            body: body,
            startY: filtroY,
            styles: {
                fontSize: 10,
                cellPadding: 5,
                fillColor: [230, 240, 255]
            },
            headStyles: {
               fillColor: [51, 147, 203], // Azul del ejemplo
               textColor: [255, 255, 255], // Blanco
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

        doc.save('Reporte_Informe_Diario.pdf');
        return;
    }
}

function obtenerDatosDeTabla(tablaId) {
    const resultados = [];
    const table = $('#' + tablaId).DataTable();
    const allData = table.rows().data().toArray();
    let tipo = $('#tipo_informe').val();
    allData.forEach(row => {
        if (tipo == 'interno') {
            resultados.push({
                campo1: row[0],
                campo2: row[1],
                campo3: row[2],
                campo4: row[3],
                campo5: row[4],
                campo6: row[5],
                campo7: row[6]
            });
        } else if (tipo == 'externo') {
            resultados.push({
                campo1: row[0],
                campo2: row[1],
                campo3: row[2],
                campo4: row[3],
                campo5: row[4],
                campo6: row[5],
                campo7: row[6]
            });
        } else if (tipo == 'articulo') {
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
        } else {
            resultados.push({
                campo1: row[0],
                campo2: row[1],
                campo3: row[2],
                campo4: row[3],
                campo5: row[4]
            });
        }
    });
    return resultados;
}
// Agrega esta función JS al final del archivo, después de pintarTablaInformeDiario:
function pintarTablaInformeGeneral(informes){
    let $tabla = $('#tablaInformeDiario');
    let $thead = $tabla.find('thead');
    let $tbody = $tabla.find('tbody');

    let cabecera = `<tr>
        <th>ID Informe</th>
        <th>Orden Trabajo</th>
        <th>Fecha</th>
        <th>Lugar</th>
        <th>Ubicación</th>
        <th>Observación</th>
    </tr>`;
    $thead.html(cabecera);
    $tbody.html('');

    if(Array.isArray(informes) && informes.length > 0){
        informes.forEach(r => {
            let fila = `<tr>
                <td>${r.id_informe ?? ""}</td>
                <td>${r.identificador ?? ""}</td>
                <td>${r.fecha ?? ""}</td>
                <td>${r.bodega_principal ?? ""}</td>
                <td>${r.bodega_secundaria ?? ""}</td>
                <td>${r.observacion ?? ""}</td>
            </tr>`;
            // Detalle expandido
            let detalle = `<tr class="detalle-informe-row">
                <td colspan="6">
                    <div style="display:flex;gap:32px;flex-wrap:wrap">
                        <div>
                            <b>Artículos:</b>
                            <ul>${(r.articulos||[]).map(a=>`<li>${a.nombre} (${a.cantidad}) [Lote: ${a.lote||'-'}]</li>`).join('')}</ul>
                        </div>
                        <div>
                            <b>Empleado Interno:</b>
                            <ul>${(r.empleados_internos||[]).map(e=>`<li>${e}</li>`).join('')}</ul>
                        </div>
                        <div>
                            <b>Empleado Externo:</b>
                            <ul>${(r.empleados_externos||[]).map(e=>`<li>${e}</li>`).join('')}</ul>
                        </div>
                        <div>
                            <b>Imágenes:</b><br>
                          ${(r.imagenes||[]).map(img=>`<img src="../${img}" class="img-zoom-thumb" style="max-width:200px;max-height:200px;margin:6px 4px 2px 0;border-radius:5px;box-shadow:0 1px 5px #aaa;">`).join('')}
                        </div>
                    </div>
                </td>
            </tr>`;
            $tbody.append(fila + detalle);
        });
    }
    // Opcional: no usar DataTable para este reporte, o re-inicializa si gustas
} 
// Agrega esta función utilitaria para cargar imágenes a base64 desde una ruta
async function getBase64FromUrl(url) {
    // Si la url no existe, retorna vacío
    if (!url) return "";
    // Si la url no es absoluta, agrégale el prefijo "../"
    if (!/^https?:\/\//.test(url) && !url.startsWith('data:')) {
        url = "../" + url.replace(/^\.\.\//, "");
    }
    return await new Promise((resolve, reject) => {
        const img = new window.Image();
        img.setAttribute('crossOrigin', 'anonymous');
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, img.width, img.height);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = function (err) {
            resolve(""); // Si falla, retorna vacío
        };
        img.src = url;
    });
}
// ----------- Imagen Zoom Modal para "general" -----------
$(document).on('mouseenter', '.img-zoom-thumb', function(e){
    let src = $(this).attr('src');
    let modal = $('#imgZoomModalGeneral');
    $('#imgZoomModalGeneralImg').attr('src', src);
    modal.addClass('visible');
});
$(document).on('mouseleave', '.img-zoom-thumb', function(e){
    setTimeout(function(){
        $('#imgZoomModalGeneral').removeClass('visible');
    }, 220); // Pequeño delay para que no desaparezca si va al modal
});
$('#imgZoomModalGeneral').on('click', function(e){
    if (!$(e.target).is('#imgZoomModalGeneralImg')) {
        $('#imgZoomModalGeneral').removeClass('visible');
    }
});
$(document).on('keydown', function(e){
    if (e.key === "Escape") $('#imgZoomModalGeneral').removeClass('visible');
});
</script>
<!-- Modal para zoom de imagen en informes generales -->
<div id="imgZoomModalGeneral">
    <img src="" id="imgZoomModalGeneralImg" alt="Zoom Imagen" />
</div>
</body>
</html>
<?php
}else{
    header("location:index.php");
}
?>