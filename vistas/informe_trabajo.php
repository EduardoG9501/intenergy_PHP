<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "INFORMES DIARIOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
<!DOCTYPE html>
<html>
<head>
    <title>OPERACIONES -> INFORMES DIARIOS DE TRABAJO</title>
    <?php require_once "menu.php"; ?> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
   
     <style>
        .modal-header .close { margin-top: -50px !important; }
        .modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px;}
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center;}
        th { background: #f5f5f5;}
        /* Usar Bootstrap modal clásico para evitar conflictos con el menú */
        .modal.fade { display: none; }
        .modal.in { display: block; } 
        input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
        .label-custom { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
        /* Detalle desplegable */
        .detalle-row td { background: #fbfbfb; padding:0 !important; }
        .detalle-panel { padding:10px; border:1px solid #ccc; background:#fff; position:relative;}
        .close-detalle-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #c00;
            font-size: 22px;
            background: #eee;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            z-index: 2;
        }
        .close-detalle-btn:hover {
            background: #ffdada;
        }
        .nav-tabs { margin-bottom:0; }
    </style>
</head>
<body>
<div class="container">
    <?php
    $id_ejecucion_obra = isset($_GET['id_ejecucion_obra']) ? intval($_GET['id_ejecucion_obra']) : 0;
    $identificador = isset($_GET['identificador']) ? $_GET['identificador'] : '';
    if ($id_ejecucion_obra) {
        echo '<button class="btn btn-warning" onclick="window.location.href=\'ejecucionobra.php\'">&larr; Regresar</button>';
        echo '<span style="font-weight:bold; font-size:16px; margin-left:10px;">Ejecución: <span style="font-weight:bold;color: #880202ff;">' . htmlspecialchars($identificador) . ' - ' . $id_ejecucion_obra . '</span></span>';
    }
    ?>
    <button id="btnAgregarInforme" class="btn btn-primary">
        <i class="glyphicon glyphicon-plus"></i> NUEVO
    </button>
    <button id="btnExportExcel" class="btn btn-success">
        <i class="glyphicon glyphicon-download"></i> Excel
    </button>
    <button id="btnExportPDF" class="btn btn-danger">
        <i class="glyphicon glyphicon-download"></i> PDF
    </button>
    <hr>
    <!-- Grid de Informes -->
    <div id="gridContainer">
        <table id="gridInformes" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ejecución de Obra</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Ubicaciòn</th>
                    <th>Observación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se llena por JS -->
            </tbody>
        </table>
    </div>

    <!-- Modal Bootstrap para el formulario de informe -->
    <div id="formModalInforme" class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
            <form id="formInforme">
                <div class="modal-header">
                    <h3 id="formModalLabel">NUEVO INFORME DIARIO</h3>
                    <button id="closexInforme" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_informe" name="id_informe">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="label-custom">EJECUCIÓN DE OBRA:</label> 
                            <select name="id_ejecucion_obra" id="id_ejecucion_obra" style="width: 270px;" required></select>
                            <label class="label-custom">FECHA:</label>
                            <input class="form-control input-sm" type="date" id="fecha" name="fecha" required>
                            <label class="label-custom">LUGAR:</label>
                            <select id="id_bodega_principal" name="id_bodega_principal"  style="width: 270px;" required></select>
                            <input type="hidden" id="id_bodega_secundaria" name="id_bodega_secundaria">
                        </div>
                        <div class="col-sm-6">  
                            <label class="label-custom">OBSERVACIÓN:</label>
                            <textarea style="width: 100%;" id="observacion" name="observacion"></textarea>  
                        </div>
                    </div>
                </div>
                <div class="modal-footer form-acciones" style="text-align: center;"> 
                    <button type="button" id="btnCancelarInforme" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" id="btnGuardarInforme" class="btn btn-success">GUARDAR</button>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>
<!-- JS Bootstrap, jQuery, DataTables, Select2, SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> 
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
<script type="text/javascript">

let bodegasCache = [];
let informesTrabajoCache = [];
const bodegaActivaSesion = <?php echo isset($_SESSION['bodega_seleccionada']) ? intval($_SESSION['bodega_seleccionada']) : 'null'; ?>;

window.idEjecucionObraFiltro = <?php echo isset($_GET['id_ejecucion_obra']) && intval($_GET['id_ejecucion_obra']) > 0 ? intval($_GET['id_ejecucion_obra']) : 'null'; ?>;
$(document).ready(function() {
    cargarInformes();
    cargarEjecucionesObra();
    cargarBodegas();

    $('#btnAgregarInforme').on('click', function() {
        mostrarFormularioInforme();
    });

    $('#btnCancelarInforme, #closexInforme').on('click', function() {
        $('#formModalInforme').modal('hide');
        $('#formInforme')[0].reset();
    });

    $('#formInforme').on('submit', function(e) {
        e.preventDefault();
        guardarInforme();
    });

    // --- Exportar a Excel con detalle por informe ---
    $('#btnExportExcel').on('click', async function() {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('InformesTrabajo');
        const informes = Array.isArray(informesTrabajoCache) ? informesTrabajoCache : [];
        const detallesInformes = [];
        let maxImgs = 0;

        for (const informe of informes) {
            const idInforme = informe.id_informe;
            const [empleadosInternos, empleadosExternos, articulos, imagenes] = await Promise.all([
                postInformeTrabajo('listar_empleados_internos', { id_informe: idInforme }),
                postInformeTrabajo('listar_empleados_externos', { id_informe: idInforme }),
                postInformeTrabajo('listar_articulos', { id_informe: idInforme }),
                postInformeTrabajo('listar_imagenes', { id_informe: idInforme })
            ]);

            const empleados = [];
            (empleadosInternos || []).forEach(e => empleados.push(e.nombres_apellidos || ''));
            (empleadosExternos || []).forEach(e => empleados.push(e.nombres_apellidos || ''));

            const articulosStr = (articulos || []).length > 0
                ? (articulos || []).map(a => `${a.nombre || ''} (${a.cantidad || 0}) [Lote: ${a.lote || '-'}]`).join('\n')
                : '—';
            const empleadosStr = empleados.length > 0 ? empleados.join('\n') : '—';
            const descripcionStr = (informe.descripcion && String(informe.descripcion).trim() !== '') ? informe.descripcion : '—';

            let imagenesBase64 = [];
            for (const img of (imagenes || [])) {
                if (!img.ruta) continue;
                const b64 = await getBase64FromUrlInformeTrabajo('../' + String(img.ruta).replace(/^\.\.\//, ''));
                if (b64) imagenesBase64.push(b64);
            }
            maxImgs = Math.max(maxImgs, imagenesBase64.length);

            detallesInformes.push({
                informe,
                articulosStr,
                empleadosStr,
                descripcionStr,
                imagenesBase64
            });
        }

        if (maxImgs < 1) maxImgs = 1;

        let header = ['ID', 'Ejecución de Obra', 'Fecha'];
        for (let i = 1; i <= maxImgs; i++) {
            header.push('Imagen ' + i);
        }
        header = header.concat(['Lugar', 'Ubicación', 'Observación']);

        let headerRow = worksheet.addRow(header);
        headerRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FF046b8b' }
            };
            cell.alignment = { horizontal: 'center', vertical: 'middle' };
            cell.border = {
                top: {style:'thin'}, left: {style:'thin'},
                bottom: {style:'thin'}, right: {style:'thin'}
            };
        });

        for (const det of detallesInformes) {
            const informe = det.informe;
            const encabezado = worksheet.addRow([
                informe.id_informe ?? '',
                informe.identificador_ejecucion ?? (informe.id_ejecucion_obra ?? ''),
                informe.fecha ?? '',
                ...Array(maxImgs).fill(''),
                informe.bodega_principal ?? '',
                informe.bodega_secundaria ?? '',
                informe.observacion ?? ''
            ]);
            encabezado.eachCell(cell => {
                cell.font = { bold: true, color: { argb: 'FF000000' } };
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: { argb: 'FFDAECF7' }
                };
                cell.alignment = { horizontal: 'left', vertical: 'top', wrapText: true };
                cell.border = {
                    top: {style:'thin'}, left: {style:'thin'},
                    bottom: {style:'thin'}, right: {style:'thin'}
                };
            });

            const detalle = worksheet.addRow([
                'Artículos:\n' + det.articulosStr,
                'Empleados:\n' + det.empleadosStr,
                '',
                ...Array(maxImgs).fill(''),
                'Descripción:\n' + det.descripcionStr,
                '',
                ''
            ]);
            detalle.height = 62;
            detalle.eachCell(cell => {
                cell.font = { size: 10 };
                cell.alignment = { horizontal: 'left', vertical: 'top', wrapText: true };
                cell.border = {
                    top: {style:'thin'}, left: {style:'thin'},
                    bottom: {style:'thin'}, right: {style:'thin'}
                };
            });

            // Insertar imágenes reales en columnas dinámicas
            for (let i = 0; i < det.imagenesBase64.length; i++) {
                const imageId = workbook.addImage({
                    base64: det.imagenesBase64[i],
                    extension: 'png'
                });
                const columnaImagen = 4 + i; // D en adelante
                worksheet.addImage(imageId, {
                    tl: { col: columnaImagen - 1 + 0.08, row: detalle.number - 1 + 0.1 },
                    ext: { width: 58, height: 44 },
                    editAs: 'absolute'
                });
            }

            worksheet.addRow(Array(3 + maxImgs + 3).fill(''));
        }

        worksheet.getColumn(1).width = 12;
        worksheet.getColumn(2).width = 26;
        worksheet.getColumn(3).width = 16;
        for (let i = 0; i < maxImgs; i++) {
            worksheet.getColumn(4 + i).width = 12;
        }
        worksheet.getColumn(4 + maxImgs).width = 18;
        worksheet.getColumn(5 + maxImgs).width = 18;
        worksheet.getColumn(6 + maxImgs).width = 30;

        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'InformesTrabajo.xlsx');
    });

    // --- Exportar a PDF (estructurado igual al reporte avanzado) ---
    $('#btnExportPDF').on('click', async function() {
        const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });
        const pageWidth = doc.internal.pageSize.getWidth();
        doc.setFontSize(20);
        doc.setTextColor(40, 55, 71);
        doc.text("Informes de Trabajo", pageWidth / 2, 32, { align: 'center' });

        const informes = Array.isArray(informesTrabajoCache) ? informesTrabajoCache : [];
        let body = [];

        for (const informe of informes) {
            const idInforme = informe.id_informe;
            const [empleadosInternos, empleadosExternos, articulos, imagenes] = await Promise.all([
                postInformeTrabajo('listar_empleados_internos', { id_informe: idInforme }),
                postInformeTrabajo('listar_empleados_externos', { id_informe: idInforme }),
                postInformeTrabajo('listar_articulos', { id_informe: idInforme }),
                postInformeTrabajo('listar_imagenes', { id_informe: idInforme })
            ]);

            const empleados = [];
            (empleadosInternos || []).forEach(e => empleados.push(e.nombres_apellidos || ''));
            (empleadosExternos || []).forEach(e => empleados.push(e.nombres_apellidos || ''));

            const articulosStr = (articulos || []).length > 0
                ? (articulos || []).map(a => `${a.nombre || ''} (${a.cantidad || 0}) [Lote: ${a.lote || '-'}]`).join('\n')
                : '—';
            const empleadosStr = empleados.length > 0 ? empleados.join('\n') : '—';
            const descripcionStr = (informe.descripcion && String(informe.descripcion).trim() !== '') ? informe.descripcion : '—';

            let imgsBase64 = [];
            for (const img of (imagenes || [])) {
                if (!img.ruta) continue;
                const b64 = await getBase64FromUrlInformeTrabajo('../' + String(img.ruta).replace(/^\.\.\//, ''));
                if (b64) imgsBase64.push(b64);
                if (imgsBase64.length >= 6) break;
            }

            body.push({
                id_informe: informe.id_informe ?? '',
                ejecucion: informe.identificador_ejecucion ?? (informe.id_ejecucion_obra ?? ''),
                fecha: informe.fecha ?? '',
                lugar: informe.bodega_principal ?? '',
                ubicacion: informe.bodega_secundaria ?? '',
                observacion: informe.observacion ?? ''
            });

            body.push({
                id_informe: {
                    content: 'Artículos:\n' + articulosStr,
                    colSpan: 2,
                    styles: { halign: 'left', valign: 'top', cellWidth: 210 }
                },
                fecha: {
                    content: 'Empleados:\n' + empleadosStr,
                    colSpan: 1,
                    styles: { halign: 'left', valign: 'top', cellWidth: 140 }
                },
                lugar: {
                    content: 'Imágenes:',
                    colSpan: 1,
                    styles: { halign: 'left', valign: 'top', cellWidth: 180 }
                },
                ubicacion: {
                    content: 'Descripción:\n' + descripcionStr,
                    colSpan: 2,
                    styles: { halign: 'left', valign: 'top', cellWidth: 220 }
                },
                __imagenesMiniaturas: imgsBase64
            });
        }

        doc.autoTable({
            startY: 48,
            columns: [
                { header: 'ID', dataKey: 'id_informe' },
                { header: 'Ejecución de Obra', dataKey: 'ejecucion' },
                { header: 'Fecha', dataKey: 'fecha' },
                { header: 'Lugar', dataKey: 'lugar' },
                { header: 'Ubicación', dataKey: 'ubicacion' },
                { header: 'Observación', dataKey: 'observacion' }
            ],
            body: body,
            styles: {
                fontSize: 9,
                cellPadding: 4,
                fillColor: [245, 245, 245],
                overflow: 'linebreak',
                valign: 'middle'
            },
            headStyles: {
                fillColor: [4, 107, 139],
                textColor: [255, 255, 255],
                fontStyle: 'bold',
                halign: 'center',
                valign: 'middle',
                minCellHeight: 24
            },
            margin: { left: 32, right: 32 },
            columnStyles: {
                0: { cellWidth: 45, halign: 'center' },
                1: { cellWidth: 120, halign: 'left' },
                2: { cellWidth: 78, halign: 'center' },
                3: { cellWidth: 90, halign: 'left' },
                4: { cellWidth: 90, halign: 'left' },
                5: { cellWidth: 325, halign: 'left' }
            },
            didParseCell: function (data) {
                if (data.row.section === 'body' && data.cell.raw && data.cell.raw.colSpan) {
                    data.cell.colSpan = data.cell.raw.colSpan;
                    data.cell.styles = Object.assign(data.cell.styles, data.cell.raw.styles || {});
                }
                if (
                    data.section === 'body' &&
                    data.row.raw &&
                    Array.isArray(data.row.raw.__imagenesMiniaturas) &&
                    data.column.index === 3
                ) {
                    const imgs = data.row.raw.__imagenesMiniaturas;
                    const porFila = 4;
                    const filas = Math.max(1, Math.ceil(imgs.length / porFila));
                    const altoImg = 34;
                    const gapY = 6;
                    const altoNecesario = (filas * altoImg) + ((filas - 1) * gapY) + 22;
                    data.cell.styles.minCellHeight = Math.max(data.cell.styles.minCellHeight || 0, altoNecesario);
                }
            },
            didDrawCell: function (data) {
                if (
                    data.section === 'body' &&
                    data.row.raw &&
                    Array.isArray(data.row.raw.__imagenesMiniaturas) &&
                    data.column.index === 3
                ) {
                    const imgs = data.row.raw.__imagenesMiniaturas;
                    if (imgs.length > 0) {
                        const width = 44;
                        const height = 34;
                        const gapX = 4;
                        const gapY = 6;
                        const porFila = 4;
                        const baseX = data.cell.x + 6;
                        const baseY = data.cell.y + 18;
                        imgs.forEach((img, i) => {
                            const col = i % porFila;
                            const row = Math.floor(i / porFila);
                            const x = baseX + (col * (width + gapX));
                            const y = baseY + (row * (height + gapY));
                            try { doc.addImage(img, 'PNG', x, y, width, height); } catch(e) {}
                        });
                    }
                }
            }
        });

        doc.save("InformesTrabajo.pdf");
    });
});

async function postInformeTrabajo(action, payload) {
    const params = new URLSearchParams(Object.assign({ action: action }, payload || {}));
    const resp = await fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: params
    });
    try {
        return await resp.json();
    } catch (e) {
        return [];
    }
}

async function getBase64FromUrlInformeTrabajo(url) {
    if (!url) return '';
    return await new Promise((resolve) => {
        const img = new Image();
        img.setAttribute('crossOrigin', 'anonymous');
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = function() { resolve(''); };
        img.src = url;
    });
}


function cargarInformes() {
    let params = {action: 'listar'};
    if (window.idEjecucionObraFiltro) {
        params.id_ejecucion_obra = window.idEjecucionObraFiltro;
    }
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams(params)
    })
    .then(resp => resp.json())
    .then(data => {
        informesTrabajoCache = Array.isArray(data) ? data : [];
        pintarGridInformes(informesTrabajoCache);
    });
}

function cargarEjecucionesObra() {
    return fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_ejecuciones'})
    })
    .then(resp => resp.json())
    .then(ejecuciones => {
        const sel = document.getElementById('id_ejecucion_obra');
        sel.innerHTML = '';
        ejecuciones.forEach(e => {
            sel.innerHTML += `<option value="${e.id_ejecucion_obra}">${e.identificador_ejecucion ? e.identificador_ejecucion : (e.id_ejecucion_obra + (e.observacion ? ' - ' + e.observacion : ''))}</option>`;
        });
        if (window.$ && typeof $('#id_ejecucion_obra').select2 === 'function') {
            $('#id_ejecucion_obra').select2({
                width: 'resolve',
                dropdownParent: $('#formModalInforme'),
                minimumResultsForSearch: 0 // Siempre muestra la caja de búsqueda
            });
        }
    });
}

function cargarBodegas() {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_bodegas'})
    })
    .then(resp => resp.json())
    .then(bodegas => {
        bodegasCache = bodegas;
        // Destruye select2 si está inicializado previamente
        if ($('#id_bodega_principal').hasClass("select2-hidden-accessible")) {
            $('#id_bodega_principal').select2('destroy');
        }
        const principal = document.getElementById('id_bodega_principal');
        principal.innerHTML = '';

        // Mostrar bodegas activas para seleccionar la bodega activa de sesión.
        bodegas.forEach(b => {
            principal.innerHTML += `<option value="${b.id_bodega}">${b.nombreBodega}</option>`;
        });

        $('#id_bodega_principal').select2({
            width: 'resolve',
            dropdownParent: $('#formModalInforme')
        });

        // Secundaria siempre debe igualar principal para no afectar backend.
        principal.onchange = function() {
            let idPrincipal = principal.value;
            $('#id_bodega_secundaria').val(idPrincipal);
        };

        if (principal.options.length > 0) {
            let valorBodega = null;
            if (bodegaActivaSesion !== null && principal.querySelector('option[value="' + bodegaActivaSesion + '"]')) {
                valorBodega = String(bodegaActivaSesion);
            } else {
                valorBodega = principal.options[0].value;
            }
            principal.value = valorBodega;
            $('#id_bodega_principal').val(valorBodega).trigger('change');
            $('#id_bodega_secundaria').val(valorBodega);
        }

        // Bloquear para que siempre use la bodega activa.
        $('#id_bodega_principal').prop('disabled', true).trigger('change.select2');
    });
}

function mostrarFormularioInforme(informe = null) {
    $('#formModalInforme').modal('show');
    let id_informe = "";
    let selectBodega = document.getElementById('id_bodega_principal');

    if (informe && informe.id_informe) {
        $('#formModalLabel').text('Editar Informe Diario');
        Object.keys(informe).forEach(k => {
            if ($('#' + k).length && k !== 'id_ejecucion_obra') {
                $('#' + k).val(informe[k]);
            }
        });
        $('#id_ejecucion_obra').val(informe.id_ejecucion_obra).trigger('change');
        id_informe = informe.id_informe;
        $('#id_informe').val(id_informe);

        // Bloqueo de ejecución de obra si viene de filtro
        setTimeout(function() {
            if (window.idEjecucionObraFiltro) {
                $('#id_ejecucion_obra').prop('disabled', true);
                // Agregar input hidden si no existe
                let hiddenEjecucion = document.getElementById('hidden_id_ejecucion_obra');
                if (!hiddenEjecucion) {
                    hiddenEjecucion = document.createElement('input');
                    hiddenEjecucion.type = 'hidden';
                    hiddenEjecucion.id = 'hidden_id_ejecucion_obra';
                    hiddenEjecucion.name = 'id_ejecucion_obra';
                    document.getElementById('id_ejecucion_obra').parentNode.appendChild(hiddenEjecucion);
                }
                hiddenEjecucion.value = window.idEjecucionObraFiltro;
            } else {
                $('#id_ejecucion_obra').prop('disabled', false);
                let hiddenEjecucion = document.getElementById('hidden_id_ejecucion_obra');
                if (hiddenEjecucion) hiddenEjecucion.remove();
            }
            if (selectBodega && selectBodega.options.length > 0) {
                if (bodegaActivaSesion !== null && selectBodega.querySelector('option[value="' + bodegaActivaSesion + '"]')) {
                    selectBodega.value = String(bodegaActivaSesion);
                }
                $('#id_bodega_principal').val(selectBodega.value).trigger('change');
                $('#id_bodega_secundaria').val(selectBodega.value);
                $('#id_bodega_principal').prop('disabled', true).trigger('change.select2');
            }
        }, 200);
    } else {
        $('#formModalLabel').text('Nuevo Informe Diario');
        $('#formInforme')[0].reset();
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        $('#fecha').val(`${yyyy}-${mm}-${dd}`);
        $('#id_informe').val('');

        setTimeout(function() {
            if (window.idEjecucionObraFiltro) {
                $('#id_ejecucion_obra').val(window.idEjecucionObraFiltro).trigger('change');
                $('#id_ejecucion_obra').prop('disabled', true);
                // Agregar input hidden si no existe
                let hiddenEjecucion = document.getElementById('hidden_id_ejecucion_obra');
                if (!hiddenEjecucion) {
                    hiddenEjecucion = document.createElement('input');
                    hiddenEjecucion.type = 'hidden';
                    hiddenEjecucion.id = 'hidden_id_ejecucion_obra';
                    hiddenEjecucion.name = 'id_ejecucion_obra';
                    document.getElementById('id_ejecucion_obra').parentNode.appendChild(hiddenEjecucion);
                }
                hiddenEjecucion.value = window.idEjecucionObraFiltro;
            } else {
                $('#id_ejecucion_obra').prop('disabled', false);
                let hiddenEjecucion = document.getElementById('hidden_id_ejecucion_obra');
                if (hiddenEjecucion) hiddenEjecucion.remove();
            }
            if (selectBodega && selectBodega.options.length > 0) {
                if (bodegaActivaSesion !== null && selectBodega.querySelector('option[value="' + bodegaActivaSesion + '"]')) {
                    selectBodega.value = String(bodegaActivaSesion);
                } else {
                    selectBodega.selectedIndex = 0;
                }
                $('#id_bodega_principal').trigger('change');
                $('#id_bodega_secundaria').val(selectBodega.value);
                $('#id_bodega_principal').prop('disabled', true).trigger('change.select2');
            }
        }, 200);
    }
}

function guardarInforme() {
    const formInforme = document.getElementById('formInforme');
    const idPrincipal = $('#id_bodega_principal').val() || '';
    $('#id_bodega_secundaria').val(idPrincipal);
    const datos = new FormData(formInforme);
    datos.set('id_bodega_secundaria', idPrincipal);
    datos.append('action', 'guardar');
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => {
        if(r.success === false) {
            Swal.fire({
                title: r.mensaje,
                icon: "warning",
                draggable: true,
            });
            return;
        }
        Swal.fire({
            title: r.msg || 'Informe guardado',
            icon: "success",
            draggable: true,
        });
        $('#formModalInforme').modal('hide');
        $('#formInforme')[0].reset();
        cargarInformes();
    });
}

// --- Grid principal con botón "+" y panel desplegable ---
function pintarGridInformes(informes) { 
    if ($.fn.dataTable.isDataTable('#gridInformes')) {
        $('#gridInformes').DataTable().destroy();
    }
    const tbody = document.querySelector('#gridInformes tbody');
    tbody.innerHTML = '';
    informes.forEach(informe => {
        const tr = document.createElement('tr');
        let ejecucionLabel = informe.identificador_ejecucion ? informe.identificador_ejecucion : (informe.id_ejecucion_obra || '');
        tr.innerHTML = `
            <td>${informe.id_informe}</td>
            <td>${ejecucionLabel}</td>
            <td>${informe.fecha}</td>
            <td>${informe.bodega_principal}</td>
            <td>${informe.bodega_secundaria}</td>
            <td>${informe.observacion}</td>
            <td>
                <button class="btn btn-warning" onclick="editarInforme(${informe.id_informe})"><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-info" onclick="desplegarDetalle(${informe.id_informe}, this)"><i class="glyphicon glyphicon-plus"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    $('#gridInformes').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
}

// Panel desplegable con tabs y botón menos
function desplegarDetalle(id_informe, btn) {
    // Cierra otros detalles abiertos
    $(".detalle-row").remove();

    // Encuentra la fila
    const tr = btn.closest('tr');
    const detalleTr = document.createElement('tr');
    detalleTr.className = "detalle-row";
    detalleTr.innerHTML = `<td colspan="7">
        <div class="detalle-panel">
            <button class="close-detalle-btn" title="Cerrar detalle" onclick="cerrarDetalle(this)"><i class="glyphicon glyphicon-minus"></i></button>
            <ul class="nav nav-tabs">
                <li class="active"><a style="background: #deeff9;" href="#" onclick="cargarDetalleEmpleadosInline(${id_informe}, this);return false;">Empleados</a></li>
                <li><a style="background: #deeff9;" href="#" onclick="cargarDetalleArticulosInline(${id_informe}, this);return false;">Artículos</a></li>
                <li><a style="background: #deeff9;" href="#" onclick="cargarDetalleImagenesInline(${id_informe}, this);return false;">Imágenes</a></li>
                <li><a style="background: #deeff9;" href="#" onclick="cargarDetalleDescripcionInline(${id_informe}, this);return false;">Descripción</a></li>
            </ul>
            <div id="detalleContenidoInline${id_informe}" style="padding:10px;"></div>
        </div>
    </td>`;
    // Inserta después de la fila seleccionada
    tr.parentNode.insertBefore(detalleTr, tr.nextSibling);

    cargarDetalleEmpleadosInline(id_informe, detalleTr.querySelector("li a"));
}

function cargarDetalleDescripcionInline(id_informe, tab) {
    activarTabInline(tab);
    $('#detalleContenidoInline'+id_informe).load('informe_trabajo/descripcion.php?id_informe=' + id_informe);
}

function cerrarDetalle(btn) {
    $(btn).closest('.detalle-row').remove();
}

function cargarDetalleEmpleadosInline(id_informe, tab) {
    activarTabInline(tab);
    $('#detalleContenidoInline'+id_informe).load('informe_trabajo/detalle_empleados.php?id_informe=' + id_informe);
}
function cargarDetalleArticulosInline(id_informe, tab) {
    activarTabInline(tab);
    $('#detalleContenidoInline'+id_informe).load('informe_trabajo/detalle_articulos.php?id_informe=' + id_informe);
}
function cargarDetalleImagenesInline(id_informe, tab) {
    activarTabInline(tab);
    $('#detalleContenidoInline'+id_informe).load('informe_trabajo/detalle_imagenes.php?id_informe=' + id_informe);
}
function activarTabInline(tab) {
    var $ul = $(tab).closest('ul');
    $ul.find('li').removeClass('active');
    $(tab).parent().addClass('active');
}

// Editar informe desde grid principal
function editarInforme(id_informe) {
    fetch(`../procesos/informe_trabajo/informe_trabajo.php`, {
        method: 'POST',
        body: new URLSearchParams({action: 'obtener', id_informe})
    })
    .then(resp => resp.json())
    .then(informe => mostrarFormularioInforme(informe));
}
</script>
</body>
</html>
<?php 
}else{
    header("location:../index.php");
}
?>