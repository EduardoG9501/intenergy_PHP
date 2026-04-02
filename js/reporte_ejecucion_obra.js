// reporte_ejecucion_obra.js
// Lógica JS para cargar datos, filtros y exportar


// Variables globales para cada sección
var materialesOriginal = [], materialesFiltrados = [];
var horasOriginal = [], horasFiltradas = [];
var informesOriginal = [], informesFiltrados = [];
var reporteEndpoint = window.REPORTE_EJECUCION_ENDPOINT || '../procesos/reportes/reporte_ejecucion_obra.php';
var reporteTitulo = window.REPORTE_EJECUCION_TITULO || 'REPORTE DE EJECUCIÓN DE OBRA';
var reporteArchivoBase = window.REPORTE_EJECUCION_ARCHIVO || 'reporte_ejecucion_obra';
var esLiquidacionTrabajo = (reporteArchivoBase === 'liquidacion_trabajo');

function normalizarRutaImagenInforme(ruta) {
    if (!ruta) return '';
    if (/^https?:\/\//i.test(ruta) || ruta.startsWith('data:')) return ruta;
    if (ruta.startsWith('../')) return ruta;
    return '../' + ruta.replace(/^\/+/, '');
}

function textoArticulosInforme(articulos) {
    if (!Array.isArray(articulos) || articulos.length === 0) return '—';
    return articulos
        .map(function(a){ return (a.nombre || '') + ' (' + (a.cantidad || 0) + ') [Lote: ' + (a.lote || '-') + ']'; })
        .join('\n');
}

function textoEmpleadosInforme(empleados) {
    if (!Array.isArray(empleados) || empleados.length === 0) return '—';
    return empleados.join('\n');
}

function textoDescripcionInforme(descripcion) {
    return (descripcion && String(descripcion).trim() !== '') ? descripcion : '—';
}

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
    async function exportarPDFEjecucionObra() {
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
        const manoObra = esLiquidacionTrabajo ? ($('#txtManoObra').val() || '').trim() : '';
        const importante = esLiquidacionTrabajo ? ($('#txtImportante').val() || '').trim() : '';
        const imagenesLiquidacion = esLiquidacionTrabajo ? await obtenerImagenesLiquidacionBase64() : [];
        doc.text(`Proyecto: ${proyecto}`, 50, filtroY); filtroY += 16;
        doc.text(`Nombre de la Obra: ${obra}`, 50, filtroY); filtroY += 16;
        doc.text(`Lugar: ${lugar}`, 50, filtroY); filtroY += 16;
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
        const encabezadoInformes = informesOriginal.map(function(r) {
            return [
                r.id_informe ?? '',
                r.nombre_obra ?? r.obra ?? '',
                r.fecha ?? '',
                r.lugar ?? '',
                r.ubicacion ?? '',
                r.observacion ?? ''
            ];
        });

        doc.autoTable({
            startY: filtroY,
            head: [['ID INFORME','NOMBRE DE LA OBRA','FECHA','LUGAR','UBICACIÓN','OBSERVACIÓN']],
            body: encabezadoInformes,
            styles: { fontSize: 8, cellPadding: 3, halign: 'center', valign: 'middle' },
            headStyles: headerStyles,
            alternateRowStyles: altRowStyles,
            margin: { left: 40, right: 40 },
            columnStyles: {
                0: { cellWidth: 58 },
                1: { cellWidth: 170, halign: 'left' },
                2: { cellWidth: 78 },
                3: { cellWidth: 78 },
                4: { cellWidth: 90 },
                5: { cellWidth: 286, halign: 'left' }
            }
        });

        filtroY = doc.lastAutoTable.finalY + 10;

        for (const r of informesOriginal) {
            if (filtroY > doc.internal.pageSize.getHeight() - 120) {
                doc.addPage();
                filtroY = 50;
            }

            doc.setFontSize(9);
            doc.setTextColor(44, 62, 80);
            doc.text('Detalle Informe #' + (r.id_informe ?? ''), 50, filtroY);
            filtroY += 6;

            let imagenesMiniatura = [];
            if (Array.isArray(r.imagenes)) {
                for (let i = 0; i < r.imagenes.length; i++) {
                    const rutaImagen = normalizarRutaImagenInforme(r.imagenes[i]);
                    if (!rutaImagen) continue;
                    const base64 = await imageUrlToDataUrl(rutaImagen);
                    if (base64) imagenesMiniatura.push(base64);
                }
            }

            const detalleRow = [{
                articulos: textoArticulosInforme(r.articulos),
                empleados: textoEmpleadosInforme(r.empleados),
                imagenes: imagenesMiniatura.length > 0 ? '' : '—',
                descripcion: textoDescripcionInforme(r.descripcion),
                __imagenesMiniaturas: imagenesMiniatura
            }];

            doc.autoTable({
                startY: filtroY,
                columns: [
                    { header: 'ARTÍCULOS', dataKey: 'articulos' },
                    { header: 'EMPLEADOS', dataKey: 'empleados' },
                    { header: 'IMÁGENES', dataKey: 'imagenes' },
                    { header: 'DESCRIPCIÓN', dataKey: 'descripcion' }
                ],
                body: detalleRow,
                styles: { fontSize: 8, cellPadding: 4, valign: 'top', halign: 'left' },
                headStyles: {
                    fillColor: [200, 226, 245],
                    textColor: [31, 72, 106],
                    fontStyle: 'bold',
                    halign: 'center'
                },
                margin: { left: 40, right: 40 },
                columnStyles: {
                    0: { cellWidth: 220 },
                    1: { cellWidth: 170 },
                    2: { cellWidth: 160, halign: 'center' },
                    3: { cellWidth: 210 }
                },
                didParseCell: function(data) {
                    if (
                        data.section === 'body' &&
                        data.row.raw &&
                        Array.isArray(data.row.raw.__imagenesMiniaturas) &&
                        data.column.index === 2
                    ) {
                        const imgs = data.row.raw.__imagenesMiniaturas;
                        const porFila = 4;
                        const filas = Math.max(1, Math.ceil(imgs.length / porFila));
                        const altoImg = 28;
                        const gapY = 4;
                        const altoNecesario = (filas * altoImg) + ((filas - 1) * gapY) + 12;
                        data.cell.styles.minCellHeight = Math.max(data.cell.styles.minCellHeight || 0, altoNecesario);
                    }
                },
                didDrawCell: function(data) {
                    if (
                        data.section === 'body' &&
                        data.row.raw &&
                        Array.isArray(data.row.raw.__imagenesMiniaturas) &&
                        data.column.index === 2
                    ) {
                        const imgs = data.row.raw.__imagenesMiniaturas;
                        if (imgs.length > 0) {
                            const width = 34;
                            const height = 28;
                            const gapX = 4;
                            const gapY = 4;
                            const porFila = 4;
                            const baseX = data.cell.x + 6;
                            const baseY = data.cell.y + 6;
                            for (let i = 0; i < imgs.length; i++) {
                                const col = i % porFila;
                                const row = Math.floor(i / porFila);
                                const posX = baseX + (col * (width + gapX));
                                const posY = baseY + (row * (height + gapY));
                                try {
                                    doc.addImage(imgs[i], 'PNG', posX, posY, width, height);
                                } catch (e) {}
                            }
                        }
                    }
                }
            });

            filtroY = doc.lastAutoTable.finalY + 8;
        }

        // LIQUIDACION AL FINAL (solo para liquidación de trabajo)
        if (esLiquidacionTrabajo) {
            filtroY = doc.lastAutoTable.finalY + 16;

            if (filtroY > doc.internal.pageSize.getHeight() - 120) {
                doc.addPage();
                filtroY = 50;
            }

            // Encabezado destacado de sección
            doc.setFillColor(217, 237, 247);
            doc.setDrawColor(154, 207, 234);
            doc.roundedRect(45, filtroY - 12, pageWidth - 90, 22, 2, 2, 'FD');
            doc.setFontSize(12);
            doc.setTextColor(23, 82, 130);
            doc.text('SECCION DE LIQUIDACION DE TRABAJO', 50, filtroY + 2);
            const anchoTituloLiquidacion = doc.getTextWidth('SECCION DE LIQUIDACION DE TRABAJO');
            doc.setLineWidth(1.2);
            doc.line(50, filtroY + 5, 50 + anchoTituloLiquidacion, filtroY + 5);
            doc.setTextColor(44, 62, 80);
            filtroY += 24;

            if (manoObra) {
                const manoObraLineas = doc.splitTextToSize(manoObra, pageWidth - 120);
                doc.setFontSize(11);
                doc.text('MANO DE OBRA:', 50, filtroY);
                const anchoManoObra = doc.getTextWidth('MANO DE OBRA:');
                doc.setLineWidth(1);
                doc.line(50, filtroY + 3, 50 + anchoManoObra, filtroY + 3);
                filtroY += 14;
                doc.text(manoObraLineas, 50, filtroY);
                filtroY += (manoObraLineas.length * 12) + 8;
            }

            if (importante) {
                const importanteLineas = doc.splitTextToSize(importante, pageWidth - 120);
                doc.setFontSize(11);
                doc.text('IMPORTANTE:', 50, filtroY);
                const anchoImportante = doc.getTextWidth('IMPORTANTE:');
                doc.setLineWidth(1);
                doc.line(50, filtroY + 3, 50 + anchoImportante, filtroY + 3);
                filtroY += 14;
                doc.text(importanteLineas, 50, filtroY);
                filtroY += (importanteLineas.length * 12) + 8;
            }

            if (imagenesLiquidacion.length > 0) {
                const imgAncho = 120;
                const imgAlto = 90;
                const margenX = 20;

                if (filtroY + imgAlto + 30 > doc.internal.pageSize.getHeight() - 60) {
                    doc.addPage();
                    filtroY = 50;
                }

                doc.setFontSize(11);
                doc.text('SECCION DE IMAGENES ADJUNTADAS:', 50, filtroY);
                const anchoImagenes = doc.getTextWidth('SECCION DE IMAGENES ADJUNTADAS:');
                doc.setLineWidth(1);
                doc.line(50, filtroY + 3, 50 + anchoImagenes, filtroY + 3);
                filtroY += 12;

                let col = 0;
                for (let i = 0; i < imagenesLiquidacion.length; i++) {
                    const posX = 50 + ((imgAncho + margenX) * col);
                    if (filtroY + imgAlto > doc.internal.pageSize.getHeight() - 60) {
                        doc.addPage();
                        filtroY = 50;
                        doc.setFontSize(11);
                        doc.text('SECCION DE IMAGENES ADJUNTADAS (CONT.):', 50, filtroY);
                        const anchoImagenesCont = doc.getTextWidth('SECCION DE IMAGENES ADJUNTADAS (CONT.):');
                        doc.line(50, filtroY + 3, 50 + anchoImagenesCont, filtroY + 3);
                        filtroY += 12;
                        col = 0;
                    }
                    doc.addImage(imagenesLiquidacion[i], 'JPEG', posX, filtroY, imgAncho, imgAlto);
                    col++;
                    if (col === 5) {
                        col = 0;
                        filtroY += imgAlto + 10;
                    }
                }
                if (col !== 0) {
                    filtroY += imgAlto + 10;
                }
            }
        }
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

    async function imageUrlToDataUrl(url) {
        try {
            const response = await fetch(url);
            const blob = await response.blob();
            return await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        } catch (e) {
            return null;
        }
    }

    async function obtenerImagenesLiquidacionBase64() {
        const imagenes = [];
        const nodos = document.querySelectorAll('#previewImagenesLiquidacion img');
        for (let i = 0; i < nodos.length; i++) {
            const src = nodos[i].getAttribute('src');
            if (!src) continue;
            const dataUrl = await imageUrlToDataUrl(src);
            if (dataUrl) imagenes.push(dataUrl);
        }
        return imagenes;
    }

    // Exportar Excel completo
    async function exportarExcelEjecucionObra() {
        // Requiere ExcelJS
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('Ejecución de Obra');
        // Cabecera
        sheet.addRow([reporteTitulo]);
        sheet.addRow([`Desde: ${$('#fecha_inicial').val()} Hasta: ${$('#fecha_final').val()}`]);
        sheet.addRow([`Proyecto: ${$('#proyecto option:selected').text()}`]);
        sheet.addRow([`Nombre de la Obra: ${$('#obra option:selected').text()}`]);
        sheet.addRow([`Lugar: ${$('#bodega option:selected').text()}`]);
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
        let headerRowInf = sheet.addRow(['ID INFORME','NOMBRE DE LA OBRA','FECHA','LUGAR','UBICACION','OBSERVACIÓN']);
        headerRowInf.eachCell((cell) => {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '2986C5' }
            };
            cell.font = { bold: true, color: { argb: 'FFFFFF' }, size: 8 };
            cell.alignment = { horizontal: 'center' };
        });
        let maxImagenesInforme = 0;
        informesOriginal.forEach(r => {
            if (Array.isArray(r.imagenes)) {
                maxImagenesInforme = Math.max(maxImagenesInforme, r.imagenes.length);
            }
        });

        for (const r of informesOriginal) {
            let filaEncabezadoInforme = sheet.addRow([
                r.id_informe ?? '',
                r.nombre_obra ?? r.obra ?? '',
                r.fecha ?? '',
                r.lugar ?? '',
                r.ubicacion ?? '',
                r.observacion ?? ''
            ]);
            filaEncabezadoInforme.eachCell((cell) => {
                cell.font = { bold: true, color: { argb: '000000' }, size: 8 };
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'DAECF7' }
                };
                cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                cell.border = {
                    top: { style: 'thin' }, left: { style: 'thin' },
                    bottom: { style: 'thin' }, right: { style: 'thin' }
                };
            });

            let detalleInforme = sheet.addRow([
                'Artículos:\n' + textoArticulosInforme(r.articulos),
                'Empleados:\n' + textoEmpleadosInforme(r.empleados),
                ...Array(maxImagenesInforme).fill(''),
                'Descripción:\n' + textoDescripcionInforme(r.descripcion)
            ]);
            detalleInforme.height = 52;
            detalleInforme.eachCell((cell) => {
                cell.font = { size: 8 };
                cell.alignment = { horizontal: 'left', vertical: 'top', wrapText: true };
                cell.border = {
                    top: { style: 'thin' }, left: { style: 'thin' },
                    bottom: { style: 'thin' }, right: { style: 'thin' }
                };
            });

            if (Array.isArray(r.imagenes)) {
                for (let i = 0; i < r.imagenes.length; i++) {
                    const rutaImagen = normalizarRutaImagenInforme(r.imagenes[i]);
                    if (!rutaImagen) continue;
                    const base64 = await imageUrlToDataUrl(rutaImagen);
                    if (!base64) continue;
                    const imageId = workbook.addImage({
                        base64: base64,
                        extension: 'png'
                    });
                    const columnaImagen = 3 + i;
                    sheet.addImage(imageId, {
                        tl: { col: columnaImagen - 1 + 0.08, row: detalleInforme.number - 1 + 0.1 },
                        ext: { width: 58, height: 44 },
                        editAs: 'absolute'
                    });
                }
            }
        }

        if (maxImagenesInforme > 0) {
            for (let i = 0; i < maxImagenesInforme; i++) {
                sheet.getColumn(3 + i).width = 12;
            }
        }
        sheet.getColumn(1).width = 38;
        sheet.getColumn(2).width = 28;
        sheet.getColumn(3 + maxImagenesInforme).width = 40;

        // LIQUIDACION AL FINAL (solo para liquidación de trabajo)
        if (esLiquidacionTrabajo && $('#txtManoObra').length && ($('#txtManoObra').val() || '').trim() !== '') {
            sheet.addRow([]);
            let tituloManoObra = sheet.addRow(['MANO DE OBRA']);
            tituloManoObra.font = { bold: true, size: 12 };
            sheet.addRow([$('#txtManoObra').val()]);
        }

        if (esLiquidacionTrabajo && $('#txtImportante').length && ($('#txtImportante').val() || '').trim() !== '') {
            sheet.addRow([]);
            let tituloImportante = sheet.addRow(['IMPORTANTE']);
            tituloImportante.font = { bold: true, size: 12 };
            sheet.addRow([$('#txtImportante').val()]);
        }

        // IMAGENES ADJUNTADAS AL FINAL (solo liquidación)
        const imagenesLiquidacion = esLiquidacionTrabajo ? await obtenerImagenesLiquidacionBase64() : [];
        if (esLiquidacionTrabajo && imagenesLiquidacion.length > 0) {
            sheet.addRow([]);
            let tituloImagenes = sheet.addRow(['IMAGENES ADJUNTADAS']);
            tituloImagenes.font = { bold: true, size: 12 };
            sheet.addRow([]);

            let filaInicio = sheet.rowCount + 1;
            let colInicio = 1;
            for (let i = 0; i < imagenesLiquidacion.length; i++) {
                const imageId = workbook.addImage({
                    base64: imagenesLiquidacion[i],
                    extension: 'jpeg'
                });

                sheet.addImage(imageId, {
                    tl: { col: colInicio - 1, row: filaInicio - 1 },
                    ext: { width: 180, height: 120 }
                });

                colInicio += 3;
                if (colInicio > 10) {
                    colInicio = 1;
                    filaInicio += 7;
                }
            }
        }

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
    let detallesFilas = [];
    $tbody.html('');
    if ( $.fn.DataTable.isDataTable($tabla) ) {
        $tabla.DataTable().clear().destroy();
    }
    if(!Array.isArray(informes) || informes.length === 0){
        let vacio = '<tr>';
        for(let i=0;i<6;i++) vacio += '<td></td>';
        vacio += '</tr>';
        $tbody.append(vacio);
    } else {
        informes.forEach(r => {
            let rowHtml = `<tr>
                <td>${r.id_informe ?? ''}</td>
                <td>${r.nombre_obra ?? r.obra ?? ''}</td>
                <td>${r.fecha ?? ''}</td>
                <td>${r.lugar ?? ''}</td>
                <td>${r.ubicacion ?? ''}</td>
                <td>${r.observacion ?? ''}</td>
            </tr>`;

            let listaArticulos = (r.articulos || []).map(function(a){
                return `<li>${a.nombre || ''} (${a.cantidad || 0}) [Lote: ${a.lote || '-'}]</li>`;
            }).join('');
            if (!listaArticulos) listaArticulos = '<li>—</li>';

            let listaEmpleados = (r.empleados || []).map(function(e){
                return `<li>${e}</li>`;
            }).join('');
            if (!listaEmpleados) listaEmpleados = '<li>—</li>';

            let imagenesHtml = (r.imagenes || []).map(function(img){
                const ruta = normalizarRutaImagenInforme(img);
                if (!ruta) return '';
                return `<img src="${ruta}" class="img-zoom-thumb-reporte" style="max-width:110px;max-height:90px;margin:4px 4px 2px 0;border-radius:4px;box-shadow:0 1px 4px #aaa;">`;
            }).join('');
            if (!imagenesHtml) imagenesHtml = '<span>—</span>';

            let detalleHtml = `<div class="detalle-informe-row" style="padding:8px 10px;">
                <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start;">
                    <div style="min-width:220px;flex:1;">
                        <b>Artículos:</b>
                        <ul style="margin:4px 0 0 18px;padding:0;">${listaArticulos}</ul>
                    </div>
                    <div style="min-width:220px;flex:1;">
                        <b>Empleados:</b>
                        <ul style="margin:4px 0 0 18px;padding:0;">${listaEmpleados}</ul>
                    </div>
                    <div style="min-width:220px;flex:1;">
                        <b>Imágenes:</b><br>${imagenesHtml}
                    </div>
                    <div style="min-width:220px;flex:1;">
                        <b>Descripción:</b>
                        <div style="margin-top:4px;white-space:pre-line;">${textoDescripcionInforme(r.descripcion)}</div>
                    </div>
                </div>
            </div>`;
            $tbody.append(rowHtml);
            detallesFilas.push(detalleHtml);
        });
    }
    let dt = $tabla.DataTable({responsive:true, destroy:true, order:[], searching:true});

    if (detallesFilas.length > 0) {
        dt.rows().every(function(rowIdx) {
            const detalle = detallesFilas[rowIdx] || '';
            if (detalle) {
                this.child(detalle).show();
                $(this.node()).addClass('shown');
            }
        });
    }
}

$(document).on('mouseenter', '.img-zoom-thumb-reporte', function(){
    let src = $(this).attr('src');
    let modal = $('#imgZoomModalReporte');
    $('#imgZoomModalReporteImg').attr('src', src || '');
    modal.addClass('visible');
});

$(document).on('mouseleave', '.img-zoom-thumb-reporte', function(){
    setTimeout(function(){
        $('#imgZoomModalReporte').removeClass('visible');
    }, 220);
});

$('#imgZoomModalReporte').on('click', function(e){
    if (!$(e.target).is('#imgZoomModalReporteImg')) {
        $('#imgZoomModalReporte').removeClass('visible');
    }
});

$(document).on('keydown', function(e){
    if (e.key === 'Escape') {
        $('#imgZoomModalReporte').removeClass('visible');
    }
});
