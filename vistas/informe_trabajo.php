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
                            <label class="label-custom">UBICACIÒN:</label>
                            <select id="id_bodega_secundaria" name="id_bodega_secundaria"  style="width: 270px;" required></select>
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

    // --- Exportar a Excel ---
    $('#btnExportExcel').on('click', function() {
        var table = document.getElementById('gridInformes');
        var rows = Array.from(table.querySelectorAll('tbody tr'));
        var headerCells = Array.from(table.querySelectorAll('thead tr th'));
        // Exportar solo las 6 primeras columnas (sin acciones)
        var header = headerCells.slice(0,6).map(cell => cell.innerText);

        var workbook = new ExcelJS.Workbook();
        var worksheet = workbook.addWorksheet('InformesTrabajo');

        let headerRow = worksheet.addRow(header);
        headerRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: "FF046b8b" }
            };
            cell.alignment = { horizontal: "center", vertical: "middle" };
            cell.border = {
                top: {style:'thin'}, left: {style:'thin'},
                bottom: {style:'thin'}, right: {style:'thin'}
            };
        });

        rows.forEach(row => {
            let dataRow = Array.from(row.cells).slice(0,6).map(cell => cell.innerText);
            worksheet.addRow(dataRow);
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
            saveAs(new Blob([buffer], { type: "application/octet-stream" }), "InformesTrabajo.xlsx");
        });
    });

    // --- Exportar a PDF ---
    $('#btnExportPDF').on('click', function() {
        var table = document.getElementById('gridInformes');
        var headerCells = Array.from(table.querySelectorAll('thead tr th'));
        var header = headerCells.slice(0,6).map(cell => cell.innerText);
        var bodyRows = [];
        $('#gridInformes tbody tr').each(function() {
            var cols = [];
            $(this).find('td').each(function(i) {
                if (i < 6) cols.push($(this).text().trim());
            });
            if(cols.length) bodyRows.push(cols);
        });

        var doc = new window.jspdf.jsPDF();
        doc.text("Informes de Trabajo", 14, 18);
        doc.autoTable({
            head: [header],
            body: bodyRows,
            startY: 22,
            styles: { fontSize: 10 },
            headStyles: {
                fillColor: [4, 107, 139],
                textColor: [255, 255, 255],
                fontStyle: 'bold'
            }
        });
        doc.save("InformesTrabajo.pdf");
    });
});


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
    .then(data => pintarGridInformes(data));
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
        if ($('#id_bodega_secundaria').hasClass("select2-hidden-accessible")) {
            $('#id_bodega_secundaria').select2('destroy');
        }
        const principal = document.getElementById('id_bodega_principal');
        const secundaria = document.getElementById('id_bodega_secundaria');
        principal.innerHTML = '';

        // Solo principales
        bodegas.filter(b => !b.es_bodega_secundaria).forEach(b => {
            principal.innerHTML += `<option value="${b.id_bodega}">${b.nombreBodega}</option>`;
        });

        $('#id_bodega_principal').select2({
            width: 'resolve',
            dropdownParent: $('#formModalInforme')
        });

        // Limpiar secundaria inicialmente
        secundaria.innerHTML = '';
        $('#id_bodega_secundaria').select2({
            width: 'resolve',
            dropdownParent: $('#formModalInforme')
        });

        // Cuando cambie principal, cargar la principal + secundarias hijas
        principal.onchange = function() {
            let idPrincipal = principal.value;
            secundaria.innerHTML = '';

            // 1. Agrega la bodega principal seleccionada como opción
            const bodegaPrincipalObj = bodegas.find(b => b.id_bodega == idPrincipal);
            if (bodegaPrincipalObj) {
                secundaria.innerHTML += `<option value="${bodegaPrincipalObj.id_bodega}">${bodegaPrincipalObj.nombreBodega} (Principal)</option>`;
            }

            // 2. Agrega las bodegas secundarias hijas
            bodegas.filter(b =>
                b.es_bodega_secundaria === 1 &&
                b.id_bodega_principal == idPrincipal
            ).forEach(b => {
                secundaria.innerHTML += `<option value="${b.id_bodega}">${b.nombreBodega}</option>`;
            });

            // Selecciona la principal como default
            secundaria.value = idPrincipal;
            $('#id_bodega_secundaria').val(idPrincipal).trigger('change');
        };

        // Disparar el evento al cargar para sincronizar
        if (principal.options.length > 0) {
            principal.value = principal.options[0].value;
            principal.onchange();
        }
    });
}

function mostrarFormularioInforme(informe = null) {
    $('#formModalInforme').modal('show');
    let id_informe = "";
    let selectBodega = document.getElementById('id_bodega_principal');
    let selectSecundaria = document.getElementById('id_bodega_secundaria');

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
            if (selectBodega && informe.id_bodega_principal) {
                selectBodega.value = informe.id_bodega_principal;
                $('#id_bodega_principal').trigger('change');
            }
            setTimeout(function() {
                if (selectSecundaria && informe.id_bodega_secundaria) {
                    selectSecundaria.value = informe.id_bodega_secundaria;
                    $('#id_bodega_secundaria').val(informe.id_bodega_secundaria).trigger('change');
                }
            }, 200);
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
                selectBodega.selectedIndex = 0;
                $('#id_bodega_principal').trigger('change');
                let principalValue = selectBodega.value;
                if (selectSecundaria) {
                    selectSecundaria.value = principalValue;
                    $('#id_bodega_secundaria').val(principalValue).trigger('change');
                }
            }
        }, 200);
    }
}

function guardarInforme() {
    const formInforme = document.getElementById('formInforme');
    const datos = new FormData(formInforme);
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