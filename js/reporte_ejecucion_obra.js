// reporte_ejecucion_obra.js
// Lógica JS para cargar datos, filtros y exportar


// Variables globales para cada sección
var materialesOriginal = [], materialesFiltrados = [];
var horasOriginal = [], horasFiltradas = [];
var informesOriginal = [], informesFiltrados = [];
var reporteEndpoint = window.REPORTE_EJECUCION_ENDPOINT || '../procesos/reportes/reporte_ejecucion_obra.php';
var reporteTitulo = window.REPORTE_EJECUCION_TITULO || 'REPORTE DE EJECUCIÓN DE OBRA';
var reporteArchivoBase = window.REPORTE_EJECUCION_ARCHIVO || 'reporte_ejecucion_obra';

$(document).ready(function(){
    cargarProyectos();
    cargarObras('');
    cargarBodegas();
    // Fechas por defecto
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const fechaHoy = `${yyyy}-${mm}-${dd}`;
    $('#fecha_inicial').val(fechaHoy);
    $('#fecha_final').val(fechaHoy);

    $('#formFiltrosEjecucionObra').on('submit', function(e){
        e.preventDefault();
        cargarMateriales();
        cargarHoras();
        cargarInformes();
        $('#buscadorPersonalizado').val('');
    });
    $('#btnExportarExcel').on('click', function(){
        exportarExcelEjecucionObra();
    });
    $('#btnImprimir').on('click', function(){
        exportarPDFEjecucionObra();
    });
    // Exportar PDF completo
    function exportarPDFEjecucionObra() {
        // Requiere jsPDF y jsPDF-autotable
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape', 'pt', 'A4');
        // Logo y header estilo igual a pedidos de materiales
        const imgElement = document.getElementById("logoEmpresa");
        let logoBase64 = null;
        if (imgElement) {
            logoBase64 = getBase64Image(imgElement);
        }
        if (logoBase64) {
            doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
        }
        doc.setFontSize(24);
        doc.setTextColor(40, 55, 71);
        const y = 40;
        const pageWidth = doc.internal.pageSize.getWidth();
        doc.text(reporteTitulo, pageWidth / 2, y, { align: "center" });
        const x = 70;
        let fecha_inicial = $('#fecha_inicial').val();
        let fecha_final = $('#fecha_final').val();
        doc.setFontSize(16);
        doc.setTextColor(40, 55, 71);
        doc.text("DESDE: " +  fecha_inicial + " HASTA: " + fecha_final , pageWidth / 2, x, { align: "center" });
        doc.setFontSize(12);
        doc.setTextColor(44, 62, 80);
        let filtroY = 100;
        const proyecto = $('#proyecto option:selected').text() || 'Todos';
        const obra = $('#obra option:selected').text() || 'Todos';
        const lugar = $('#bodega option:selected').text() || 'Todos';
        const manoObra = ($('#txtManoObra').val() || '').trim();
        const importante = ($('#txtImportante').val() || '').trim();
        doc.text(`Proyecto: ${proyecto}`, 50, filtroY); filtroY += 16;
        doc.text(`Nombre de la Obra: ${obra}`, 50, filtroY); filtroY += 16;
        doc.text(`Lugar: ${lugar}`, 50, filtroY); filtroY += 16;
        if (manoObra) {
            doc.text(`Mano de Obra: ${manoObra}`, 50, filtroY); filtroY += 16;
        }
        if (importante) {
            doc.text(`Importante: ${importante}`, 50, filtroY); filtroY += 16;
        }
        doc.text('Artículos: Todos', 50, filtroY); filtroY += 10;

        // Materiales
        filtroY += 18; // Espacio extra para separar el título
        doc.setFontSize(12);
        doc.text('MATERIALES A UTILIZAR', 50, filtroY);
        // Línea horizontal solo bajo el texto
        doc.setLineWidth(2);
        doc.setDrawColor(41, 134, 197);
        const matWidth = doc.getTextWidth('MATERIALES A UTILIZAR');
        doc.line(50, filtroY + 3, 50 + matWidth, filtroY + 3);
        filtroY += 16;
        const headerStyles = {
            fillColor: [41, 134, 197],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'center',
            fontSize: 8 // Tamaño original
        };
        const rowStyles = {
            fontSize: 8,
            cellPadding: 3,
            halign: 'center',
            valign: 'middle',
        };
        const altRowStyles = {
            fillColor: [230, 240, 255]
        };
        // Usar los filtrados si hay búsqueda, si no los originales
        // Usar los datos agrupados del grid (como se ve en pantalla)
        let datosMateriales = materialesFiltrados && materialesFiltrados.length > 0 ? materialesFiltrados : materialesOriginal;
        let bodyMateriales = [];
        datosMateriales.forEach(r => {
            if (r.subtotal) {
                bodyMateriales.push([
                    '', '', '', '', '', `TOTALES ${r.articulo}:`, parseFloat(r.cantidad).toFixed(2), ''
                ]);
            } else if (r.total_general) {
                bodyMateriales.push(['', '', '', '', '', 'TOTALES GENERALES:', parseFloat(r.cantidad).toFixed(2), '']);
            } else {
                bodyMateriales.push([
                    r.id_orden ?? r.identificador ?? '',
                    r.proyecto ?? '',
                    r.obra ?? '',
                    r.fecha ?? '',
                    r.lugar ?? '',
                    r.articulo ?? '',
                    parseFloat(r.cantidad).toFixed(2),
                    r.contabilizado ?? ''
                ]);
            }
        });
        doc.autoTable({
            startY: filtroY,
            head: [['ID ORDEN','PROYECTO','NOMBRE DE LA OBRA','FECHA','LUGAR','ARTICULO','CANTIDAD','CONTABILIZADO']],
            body: bodyMateriales,
            styles: rowStyles,
            headStyles: headerStyles,
            alternateRowStyles: altRowStyles,
            didParseCell: function(data) {
                if (data.row.index !== undefined && data.row.raw && data.row.raw[5] && data.row.raw[5].startsWith('Subtotal')) {
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
        });
        filtroY = doc.lastAutoTable.finalY + 16;
        doc.setFontSize(12);
        doc.text('HORAS DE TRABAJO', 50, filtroY);
        doc.setLineWidth(2);
        doc.setDrawColor(41, 134, 197);
        const horasWidth = doc.getTextWidth('HORAS DE TRABAJO');
        doc.line(50, filtroY + 3, 50 + horasWidth, filtroY + 3);
        filtroY += 16;
        // Construir body de horas con total
        let datosHoras = horasFiltradas && horasFiltradas.length > 0 ? horasFiltradas : horasOriginal;
        let bodyHoras = datosHoras.map(r => [
            r.id_orden ?? '',
            r.proyecto ?? '',
            r.obra ?? '',
            r.fecha ?? '',
            r.lugar ?? '',
            r.empleado ?? '',
            r.hora_entrada ?? '',
            r.hora_salida ?? '',
            r.h_normales ?? '',
            r.h_extras ?? '',
            r.h_extraordinarias ?? ''
        ]);
        // Calcular totales
        let totalNormales = 0, totalExtras = 0, totalExtraordinarias = 0;
        datosHoras.forEach(r => {
            totalNormales += parseFloat(r.h_normales || 0);
            totalExtras += parseFloat(r.h_extras || 0);
            totalExtraordinarias += parseFloat(r.h_extraordinarias || 0);
        });
        bodyHoras.push([
            '', '', '', '', '', '', '', 'TOTAL:',
            totalNormales.toFixed(2),
            totalExtras.toFixed(2),
            totalExtraordinarias.toFixed(2)
        ]);
        doc.autoTable({
            startY: filtroY,
            head: [['ID ORDEN','PROYECTO','NOMBRE DE LA OBRA','FECHA','LUGAR','EMPLEADO','HORA ENTRADA','HORA SALIDA','H. NORMAL','H. EXTRAS','H. EXTRAORDINARIAS']],
            body: bodyHoras,
            styles: rowStyles,
            headStyles: headerStyles,
            alternateRowStyles: altRowStyles,
            didParseCell: function(data) {
                if (data.row.index === bodyHoras.length - 1) {
                    data.cell.styles.fontStyle = 'bold';
                    data.cell.styles.fillColor = [41, 134, 197];
                    data.cell.styles.textColor = [255,255,255];
                }
            },
        });
        filtroY = doc.lastAutoTable.finalY + 16;
        doc.setFontSize(12);
        doc.text('INFORMES DIARIOS', 50, filtroY);
        doc.setLineWidth(2);
        doc.setDrawColor(41, 134, 197);
        const informesWidth = doc.getTextWidth('INFORMES DIARIOS');
        doc.line(50, filtroY + 3, 50 + informesWidth, filtroY + 3);
        filtroY += 16;
        doc.autoTable({
            startY: filtroY,
            head: [['ID INFORME','NOMBRE DE LA OBRA','ORDEN TRABAJO','FECHA','LUGAR','UBICACION','OBSERVACIÓN']],
            body: informesOriginal.map(r => [r.id_informe ?? '', r.obra ?? '', r.orden_trabajo ?? '', r.fecha ?? '', r.lugar ?? '', r.ubicacion ?? '', r.observacion ?? '']),
            styles: rowStyles,
            headStyles: headerStyles,
            alternateRowStyles: altRowStyles,
        });
        // ...el resto del código permanece igual...
        const fecha = new Date().toLocaleString();
        doc.setFontSize(10);
        doc.setTextColor(127, 140, 141);
        doc.text(`Generado el: ${fecha} | Usuario:` +  (window.usuarioActual || ''), 50, doc.internal.pageSize.getHeight() - 30);
        doc.save(reporteArchivoBase + '.pdf');
    }

    // Utilidad para convertir imagen a base64
    function getBase64Image(img) {
        if (!img) return null;
        var canvas = document.createElement("canvas");
        canvas.width = img.width;
        canvas.height = img.height;
        var ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, img.width, img.height);
        return canvas.toDataURL("image/png");
    }

    // Exportar Excel completo
    function exportarExcelEjecucionObra() {
        // Requiere ExcelJS
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('Ejecución de Obra');
        // Cabecera
        sheet.addRow([reporteTitulo]);
        sheet.addRow([`Desde: ${$('#fecha_inicial').val()} Hasta: ${$('#fecha_final').val()}`]);
        sheet.addRow([`Proyecto: ${$('#proyecto option:selected').text()}`]);
        sheet.addRow([`Nombre de la Obra: ${$('#obra option:selected').text()}`]);
        sheet.addRow([`Lugar: ${$('#bodega option:selected').text()}`]);
        if ($('#txtManoObra').length && ($('#txtManoObra').val() || '').trim() !== '') {
            sheet.addRow([`Mano de Obra: ${$('#txtManoObra').val()}`]);
        }
        if ($('#txtImportante').length && ($('#txtImportante').val() || '').trim() !== '') {
            sheet.addRow([`Importante: ${$('#txtImportante').val()}`]);
        }
        sheet.addRow([]);
        // Título y línea solo una vez
        let matTitle = sheet.addRow(['MATERIALES A UTILIZAR']);
        matTitle.font = { bold: true, size: 12 };
        sheet.addRow([]);
        let matLine = sheet.addRow(['']);
        matLine.getCell(1).border = {
            bottom: { style: 'thick', color: { argb: '2986C5' } }
        };
        let headerRowMat = sheet.addRow(['ID ORDEN','PROYECTO','NOMBRE DE LA OBRA','FECHA','LUGAR','ARTICULO','CANTIDAD','CONTABILIZADO']);
        headerRowMat.eachCell((cell) => {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '2986C5' }
            };
            cell.font = { bold: true, color: { argb: 'FFFFFF' }, size: 8 };
            cell.alignment = { horizontal: 'center' };
        });
        // Materiales body
        let datosMateriales = materialesFiltrados && materialesFiltrados.length > 0 ? materialesFiltrados : materialesOriginal;
        datosMateriales.forEach(r => {
            if (r.subtotal) {
                sheet.addRow(['', '', '', '', '', `TOTALES ${r.articulo}:`, parseFloat(r.cantidad).toFixed(2), '']);
            } else if (r.total_general) {
                sheet.addRow(['', '', '', '', '', 'TOTALES GENERALES:', parseFloat(r.cantidad).toFixed(2), '']);
            } else {
                sheet.addRow([r.id_orden ?? r.identificador ?? '', r.proyecto ?? '', r.obra ?? '', r.fecha ?? '', r.lugar ?? '', r.articulo ?? '', parseFloat(r.cantidad).toFixed(2), r.contabilizado ?? '']);
            }
        });
        sheet.addRow([]);
        // HORAS DE TRABAJO
        let horasTitle = sheet.addRow(['HORAS DE TRABAJO']);
        horasTitle.font = { bold: true, size: 12 };
        sheet.addRow([]);
        let horasLine = sheet.addRow(['']);
        horasLine.getCell(1).border = {
            bottom: { style: 'thick', color: { argb: '2986C5' } }
        };
        let headerRowHoras = sheet.addRow(['ID ORDEN','PROYECTO','NOMBRE DE LA OBRA','FECHA','LUGAR','EMPLEADO','HORA ENTRADA','HORA SALIDA','H. NORMAL','H. EXTRAS','H. EXTRAORDINARIAS']);
        headerRowHoras.eachCell((cell) => {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '2986C5' }
            };
            cell.font = { bold: true, color: { argb: 'FFFFFF' }, size: 8 };
            cell.alignment = { horizontal: 'center' };
        });
        let datosHoras = horasFiltradas && horasFiltradas.length > 0 ? horasFiltradas : horasOriginal;
        datosHoras.forEach(r => {
            sheet.addRow([r.id_orden ?? '', r.proyecto ?? '', r.obra ?? '', r.fecha ?? '', r.lugar ?? '', r.empleado ?? '', r.hora_entrada ?? '', r.hora_salida ?? '', r.h_normales ?? '', r.h_extras ?? '', r.h_extraordinarias ?? '']);
        });
        // Calcular totales
        let totalNormales = 0, totalExtras = 0, totalExtraordinarias = 0;
        datosHoras.forEach(r => {
            totalNormales += parseFloat(r.h_normales || 0);
            totalExtras += parseFloat(r.h_extras || 0);
            totalExtraordinarias += parseFloat(r.h_extraordinarias || 0);
        });
        let totalRow = sheet.addRow(['', '', '', '', '', '', '', 'TOTAL:', totalNormales.toFixed(2), totalExtras.toFixed(2), totalExtraordinarias.toFixed(2)]);
        totalRow.eachCell((cell) => {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '2986C5' }
            };
            cell.font = { bold: true, color: { argb: 'FFFFFF' }, size: 8 };
            cell.alignment = { horizontal: 'center' };
        });
        sheet.addRow([]);
        // INFORMES DIARIOS
        let informesTitle = sheet.addRow(['INFORMES DIARIOS']);
        informesTitle.font = { bold: true, size: 12 };
        sheet.addRow([]);
        let informesLine = sheet.addRow(['']);
        informesLine.getCell(1).border = {
            bottom: { style: 'thick', color: { argb: '2986C5' } }
        };
        let headerRowInf = sheet.addRow(['ID INFORME','NOMBRE DE LA OBRA','ORDEN TRABAJO','FECHA','LUGAR','UBICACION','OBSERVACIÓN']);
        headerRowInf.eachCell((cell) => {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '2986C5' }
            };
            cell.font = { bold: true, color: { argb: 'FFFFFF' }, size: 8 };
            cell.alignment = { horizontal: 'center' };
        });
        informesOriginal.forEach(r => {
            sheet.addRow([r.id_informe ?? '', r.obra ?? '', r.orden_trabajo ?? '', r.fecha ?? '', r.lugar ?? '', r.ubicacion ?? '', r.observacion ?? '']);
        });
        // Descargar
        workbook.xlsx.writeBuffer().then(buffer => {
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = reporteArchivoBase + '.xlsx';
            a.click();
            window.URL.revokeObjectURL(url);
        });
    }
    $('#proyecto').on('change', function(){
        cargarObras($(this).val());
    });
    cargarMateriales();
    cargarHoras();
    cargarInformes();

    // Buscador personalizado para materiales (igual que en pedidos)
    $('#buscadorMateriales').on('keyup', function() {
        let search = $(this).val().toLowerCase();
        materialesFiltrados = materialesOriginal.filter(function(r) {
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
        materialesFiltrados.forEach(r => {
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
        pintarTablaMateriales(resultado);
        materialesFiltrados = resultado;
    });
});

function cargarProyectos() {
    $.post(reporteEndpoint, {accion:'listar_proyectos'}, function(data){
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
    $.post(reporteEndpoint, {accion:'listar_obras', proyecto: proyecto}, function(data){
        let obras = JSON.parse(data);
        let $obra = $('#obra');
        $obra.empty().append('<option value="">Todos</option>');
        obras.forEach(o => {
            $obra.append(`<option value="${o.id}">${o.nombre}</option>`);
        });
        $obra.select2({width:'200px', placeholder:'Obra', allowClear:true});
    });
}
function cargarBodegas() {
    $.post(reporteEndpoint, {accion:'listar_bodegas'}, function(data){
        let bodegas = JSON.parse(data);
        let $bodega = $('#bodega');
        $bodega.empty().append('<option value="">Todos</option>');
        bodegas.forEach(b => {
            $bodega.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
        });
        $bodega.select2({width:'200px', placeholder:'Bodega', allowClear:true});
    });
}

function obtenerFiltros() {
    return {
        proyecto: $('#proyecto').val(),
        obra: $('#obra').val(),
        bodega: $('#bodega').val(),
        fecha_inicial: $('#fecha_inicial').val(),
        fecha_final: $('#fecha_final').val()
    };
}

function cargarMateriales() {
    let datos = {accion:'materiales', filtros:obtenerFiltros()};
    $.post(reporteEndpoint, datos, function(data){
        let materiales = JSON.parse(data);
        materialesOriginal = materiales;
        materialesFiltrados = materialesOriginal;
        pintarTablaMateriales(materialesOriginal);
    });
}
function pintarTablaMateriales(materiales) {
    let $tabla = $('#tablaMaterialesEjecucionObra');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    let totalGeneral = 0;
    window.__totalGeneralMateriales = 0; // global para recalculo
    if(!Array.isArray(materiales) || materiales.length === 0){
        let vacio = '<tr>';
        for(let i=0;i<8;i++) vacio += '<td></td>';
        vacio += '</tr>';
        $tbody.append(vacio);
    } else {
        materiales.forEach(r => {
            if (r.subtotal) {
                // Fila de subtotal por artículo (8 columnas, sin colspan)
                let rowHtml = `<tr style="background:#b0daf5;font-weight:bold;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;">Subtotal ${r.articulo}</td>
                    <td>${parseFloat(r.cantidad).toFixed(2)}</td>
                    <td></td>
                </tr>`;
                $tbody.append(rowHtml);
            } else if (r.total_general) {
                // Solo guardar el total general, no agregar fila al tbody
                totalGeneral = parseFloat(r.cantidad);
                window.__totalGeneralMateriales = totalGeneral;
            } else {
                // Fila normal de material
                let rowHtml = `<tr>
                    <td>${r.id_orden ?? r.identificador ?? ''}</td>
                    <td>${r.proyecto ?? ''}</td>
                    <td>${r.obra ?? ''}</td>
                    <td>${r.fecha ?? ''}</td>
                    <td>${r.lugar ?? ''}</td>
                    <td>${r.articulo ?? ''}</td>
                    <td>${parseFloat(r.cantidad).toFixed(2)}</td>
                    <td>${r.contabilizado ?? ''}</td>
                </tr>`;
                $tbody.append(rowHtml);
            }
        });
        $('#ftTotalCantidadMateriales').text(totalGeneral.toFixed(2));
    }
    // Solo inicializar DataTable para responsive y paginado, sin buscador ni lógica de filtrado
    $tabla.DataTable({responsive:true, destroy:true, order:[], searching:false});
}

function cargarHoras() {
    let datos = {accion:'horas', filtros:obtenerFiltros()};
    $.post(reporteEndpoint, datos, function(data){
        let horas = JSON.parse(data);
        horasOriginal = horas;
        horasFiltradas = horasOriginal;
        pintarTablaHoras(horasOriginal);
    });
}
function pintarTablaHoras(horas) {
    let $tabla = $('#tablaHorasTrabajoEjecucionObra');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    let totalNormales = 0, totalExtras = 0, totalExtraordinarias = 0;
    if(!Array.isArray(horas) || horas.length === 0){
        let vacio = '<tr>';
        for(let i=0;i<11;i++) vacio += '<td></td>';
        vacio += '</tr>';
        $tbody.append(vacio);
    } else {
        horas.forEach(r => {
            let rowHtml = `<tr>
                <td>${r.id_orden ?? ''}</td>
                <td>${r.proyecto ?? ''}</td>
                <td>${r.obra ?? ''}</td>
                <td>${r.fecha ?? ''}</td>
                <td>${r.lugar ?? ''}</td>
                <td>${r.empleado ?? ''}</td>
                <td>${r.hora_entrada ?? ''}</td>
                <td>${r.hora_salida ?? ''}</td>
                <td>${parseFloat(r.h_normales).toFixed(2)}</td>
                <td>${parseFloat(r.h_extras).toFixed(2)}</td>
                <td>${parseFloat(r.h_extraordinarias).toFixed(2)}</td>
            </tr>`;
            $tbody.append(rowHtml);
            totalNormales += parseFloat(r.h_normales);
            totalExtras += parseFloat(r.h_extras);
            totalExtraordinarias += parseFloat(r.h_extraordinarias);
        });
        $('#ftTotalHorasNormales').text(totalNormales.toFixed(2));
        $('#ftTotalHorasExtras').text(totalExtras.toFixed(2));
        $('#ftTotalHorasExtraordinarias').text(totalExtraordinarias.toFixed(2));
    }
    let dataTable = $tabla.DataTable({responsive:true, destroy:true, order:[], searching:true});

    // Recalcular totales al filtrar/buscar con el buscador nativo
    dataTable.on('draw', function() {
        let totalNormales = 0, totalExtras = 0, totalExtraordinarias = 0;
        dataTable.rows({search:'applied'}).every(function() {
            let data = this.data();
            // Índices correctos: H. NORMAL (8), H. EXTRAS (9), H. EXTRAORDINARIAS (10)
            let hNormales = parseFloat(data[8]) || 0;
            let hExtras = parseFloat(data[9]) || 0;
            let hExtraordinarias = parseFloat(data[10]) || 0;
            totalNormales += hNormales;
            totalExtras += hExtras;
            totalExtraordinarias += hExtraordinarias;
        });
        $('#ftTotalHorasNormales').text(totalNormales.toFixed(2));
        $('#ftTotalHorasExtras').text(totalExtras.toFixed(2));
        $('#ftTotalHorasExtraordinarias').text(totalExtraordinarias.toFixed(2));
    });
    // Lanzar el evento draw para actualizar el total al cargar
    dataTable.draw();
}

function cargarInformes() {
    let datos = {accion:'informes', filtros:obtenerFiltros()};
    $.post(reporteEndpoint, datos, function(data){
        let informes = JSON.parse(data);
        informesOriginal = informes;
        informesFiltrados = informesOriginal;
        pintarTablaInformes(informesOriginal);
    });
}
function pintarTablaInformes(informes) {
    let $tabla = $('#tablaInformesDiariosEjecucionObra');
    let $tbody = $tabla.find('tbody');
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    if(!Array.isArray(informes) || informes.length === 0){
        let vacio = '<tr>';
        for(let i=0;i<7;i++) vacio += '<td></td>';
        vacio += '</tr>';
        $tbody.append(vacio);
    } else {
        informes.forEach(r => {
            let rowHtml = `<tr>
                <td>${r.id_informe ?? ''}</td>
                <td>${r.obra ?? ''}</td>
                <td>${r.orden_trabajo ?? ''}</td>
                <td>${r.fecha ?? ''}</td>
                <td>${r.lugar ?? ''}</td>
                <td>${r.ubicacion ?? ''}</td>
                <td>${r.observacion ?? ''}</td>
            </tr>`;
            $tbody.append(rowHtml);
        });
    }
    $tabla.DataTable({responsive:true, destroy:true, order:[], searching:true});
}
