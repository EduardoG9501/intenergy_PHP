<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){  
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "HORAS DE TRABAJOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
}   
?> 
<!DOCTYPE html>
<html>
<head>
    <title>OPERACIONES -> HORAS DE TRABAJO</title>
    <?php require_once "menu.php"; ?> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        .modal-header .close { margin-top: -50px !important; }
        .modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px;}
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center;}
        thead tr {
            background: #2986c5 !important;
        }
        th {
            color: #fff !important;
            font-weight: bold;
        }
        tfoot tr {
            background: #2986c5 !important;
            font-weight: bold;
        }
        tfoot td {
            color: #fff !important;
            font-size: 17px;
        }
        .modal.fade { display: none; }
        .modal.in { display: block; } 
        input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
        .label-custom { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
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
    <button id="btnAgregarHoras" class="btn btn-primary">
        <i class="glyphicon glyphicon-plus"></i> NUEVO
    </button> 
    <button id="btnExportExcel" class="btn btn-success">
       <i class="glyphicon glyphicon-download"></i> Excel
    </button>
    <button id="btnExportPDF" class="btn btn-danger">
         <i class="glyphicon glyphicon-download"></i> PDF
    </button>
    <hr> 
    <div id="gridContainer">
        <table id="gridHoras" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Ubicaciòn</th>
                    <th>Responsable Obra</th>
                    <th style="text-align: center;"># Horas</th>
                    <th style="text-align: center;">Costo x Hora</th>
                    <th style="text-align: center;">Total</th>
                    <th>Estado</th>
                    <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se llena por JS -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right;">TOTAL:</td>
                    <td id="totalHoras">0</td>
                    <td id="totalCostoHora">$0.00</td>
                    <td id="totalTotal">$0.00</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- Modal Bootstrap para el formulario de horas -->
    <div id="formModalHoras" class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
            <form id="formHoras">
                <div class="modal-header">
                    <h3 id="formModalLabel">NUEVO REGISTRO DE HORAS</h3>
                    <button id="closexHoras" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_horas_trabajo" name="id_horas_trabajo">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="label-custom">FECHA:</label>
                            <input class="form-control input-sm" type="date" id="fecha" name="fecha" required>
                            <label class="label-custom">LUGAR:</label>
                            <select id="id_bodega_principal" name="id_bodega_principal" style="width: 270px;" required></select>
                            <label class="label-custom">UBICACIÒN:</label>
                            <select id="id_bodega_secundaria" name="id_bodega_secundaria" style="width: 270px;" required></select>
                        </div>
                        <div class="col-sm-6">  
                            <label class="label-custom">RESPONSABLE OBRA:</label>
                            <select id="id_responsable_obra" name="id_responsable_obra" style="width: 270px;" required></select>
                            <label class="label-custom">OBSERVACIÓN:</label>
                            <textarea style="width: 100%;" id="observacion" name="observacion"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer form-acciones" style="text-align: center;"> 
                    <button type="button" id="btnCancelarHoras" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
                    <button type="submit" id="btnGuardarHoras" class="btn btn-success">GUARDAR</button>
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

$(document).ready(function() {
    cargarHoras();
    cargarBodegas();
    cargarEmpleados();

    $('#btnAgregarHoras').on('click', function() {
        mostrarFormularioHoras();
    });

    $('#btnCancelarHoras, #closexHoras').on('click', function() {
        $('#formModalHoras').modal('hide');
        $('#formHoras')[0].reset();
    });
 
    $('#formHoras').on('submit', function(e) {
    e.preventDefault();
    let id = $('#id_horas_trabajo').val();
    let formData = new FormData(this);
    formData.append('action', 'guardar');
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(r => {
        if(r.success) {
            $('#formModalHoras').modal('hide');
            $('#formHoras')[0].reset();
            Swal.fire({
               title: "Registro actualizado con èxito!!",
                icon: "success",
                draggable: true,
             });
            cargarHoras();
        } else {
           Swal.fire({
                    title: "Error al guardar los datos!!",
                    icon: "error",
                    draggable: true,
              }); 
        }
    });
}); 
});

function cargarHoras() {
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action: 'listar'})
    })
    .then(resp => resp.json())
    .then(data => pintarGridHoras(data));
}

// --------- FILTRO BODEGA PRINCIPAL/SECUNDARIA ---------
function cargarBodegas() {
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_bodegas'})
    })
    .then(resp => resp.json())
    .then(bodegas => {
        bodegasCache = bodegas;
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
            dropdownParent: $('#formModalHoras')
        });

        // Limpiar secundaria inicialmente
        secundaria.innerHTML = '';
        $('#id_bodega_secundaria').select2({
            width: 'resolve',
            dropdownParent: $('#formModalHoras')
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
// --------- FIN FILTRO DEPENDIENTE ---------

function cargarEmpleados() {
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_empleados'})
    })
    .then(resp => resp.json())
    .then(empleados => {
        const responsable = document.getElementById('id_responsable_obra');
        responsable.innerHTML = '';
        empleados.forEach(e => {
            responsable.innerHTML += `<option value="${e.id_empleados}">${e.nombres_apellidos}</option>`;
        });
        $('#id_responsable_obra').select2({
            dropdownParent: $('#formModalHoras'),
            width: '100%'
        });
    });
}

function mostrarFormularioHoras(horas = null) {
    $('#formModalHoras').modal('show');
    $('#formHoras')[0].reset();
    let selectBodega = document.getElementById('id_bodega_principal');
    let selectSecundaria = document.getElementById('id_bodega_secundaria');
    if (horas && horas.id_horas_trabajo) {
        $('#formModalLabel').text('Editar Registro de Horas');
        Object.keys(horas).forEach(k => {
            if ($('#' + k).length) {
                $('#' + k).val(horas[k]);
            }
        });
        $('#id_horas_trabajo').val(horas.id_horas_trabajo);

        // Esperar a que se carguen bodegas para setear los combos correctamente
        setTimeout(function() {
            if (selectBodega && horas.id_bodega_principal) {
                selectBodega.value = horas.id_bodega_principal;
                $('#id_bodega_principal').trigger('change');
            }
            setTimeout(function() {
                if (selectSecundaria && horas.id_bodega_secundaria) {
                    selectSecundaria.value = horas.id_bodega_secundaria;
                    $('#id_bodega_secundaria').val(horas.id_bodega_secundaria).trigger('change');
                }
            }, 200);
        }, 200);

    } else {
        $('#formModalLabel').text('Nuevo Registro de Horas');
        $('#formHoras')[0].reset();
        $('#id_horas_trabajo').val('');
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        $('#fecha').val(`${yyyy}-${mm}-${dd}`);

        // Después de cargar bodegas (si ya cargó), sincroniza los selects
        setTimeout(function() {
            if (selectBodega && selectBodega.options.length > 0) {
                selectBodega.selectedIndex = 0;
                $('#id_bodega_principal').trigger('change');
                // Selecciona también la secundaria igual a la principal
                let principalValue = selectBodega.value;
                if (selectSecundaria) {
                    selectSecundaria.value = principalValue;
                    $('#id_bodega_secundaria').val(principalValue).trigger('change');
                }
            }
        }, 200);
    }
}

function guardarHoras() {
    const formHoras = document.getElementById('formHoras');
    const datos = new FormData(formHoras);
    datos.append('action', 'guardar');
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
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
            title: r.msg || 'Registro guardado',
            icon: "success",
            draggable: true,
        });
        $('#formModalHoras').modal('hide');
        $('#formHoras')[0].reset();
        cargarHoras();
    });
}

function pintarGridHoras(horas) { 
    if ($.fn.dataTable.isDataTable('#gridHoras')) {
        $('#gridHoras').DataTable().destroy();
    }
    const tbody = document.querySelector('#gridHoras tbody');
    tbody.innerHTML = '';
    horas.forEach(registro => {
        let estadoTxt = registro.estado == 1 ? 'Activo' : 'Eliminado';
        let trClass = registro.estado == 1 ? '' : 'style="background:#f9d6d6;color:#a00"';
        let disabled = registro.estado == 1 ? '' : 'disabled';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td ${trClass}>${registro.id_horas_trabajo}</td>
            <td ${trClass}>${registro.fecha}</td>
            <td ${trClass}>${registro.bodega_principal}</td>
            <td ${trClass}>${registro.bodega_secundaria}</td>
            <td ${trClass}>${registro.responsable}</td>
            <td ${trClass}>${registro.numero_horas}</td>
            <td ${trClass}>${registro.costo_por_hora}</td>
            <td ${trClass}>${registro.total}</td>
            <td ${trClass}>${estadoTxt}</td>
            <td ${trClass}>
                <button class="btn btn-warning" onclick="editarHoras(${registro.id_horas_trabajo})" ${disabled}><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick="eliminarHorasTrabajo(${registro.id_horas_trabajo})" ${disabled}><i class="glyphicon glyphicon-trash"></i></button>
                <button class="btn btn-info" onclick="desplegarDetalleHoras(${registro.id_horas_trabajo}, this)"><i class="glyphicon glyphicon-plus"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    let dt = $('#gridHoras').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
    calcularTotalesGridHoras(dt);
    $('#gridHoras').on('draw.dt', function() {
        calcularTotalesGridHoras(dt);
    });
}

function desplegarDetalleHoras(id_horas_trabajo, btn) {
    $(".detalle-row").remove();
    const tr = btn.closest('tr');
    const detalleTr = document.createElement('tr');
    detalleTr.className = "detalle-row";
    detalleTr.innerHTML = `<td colspan="10">
        <div class="detalle-panel">
            <button class="close-detalle-btn" title="Cerrar detalle" onclick="cerrarDetalleHoras(this)"><i class="glyphicon glyphicon-minus"></i></button>
            <ul class="nav nav-tabs">
                <li class="active"><a style="background: #deeff9;" href="#" onclick="cargarDetalleEmpleadosHorasInline(${id_horas_trabajo}, this);return false;">Empleados</a></li>
            </ul>
            <div id="detalleContenidoHorasInline${id_horas_trabajo}" style="padding:10px;"></div>
        </div>
    </td>`;
    tr.parentNode.insertBefore(detalleTr, tr.nextSibling);
    cargarDetalleEmpleadosHorasInline(id_horas_trabajo, detalleTr.querySelector("li a"));
}

function cerrarDetalleHoras(btn) {
    $(btn).closest('.detalle-row').remove();
}

function cargarDetalleEmpleadosHorasInline(id_horas_trabajo, tab) {
    activarTabInlineHoras(tab);
    $('#detalleContenidoHorasInline'+id_horas_trabajo).load('horas_trabajo/detalle_empleados.php?id_horas_trabajo=' + id_horas_trabajo);
}
function activarTabInlineHoras(tab) {
    var $ul = $(tab).closest('ul');
    $ul.find('li').removeClass('active');
    $(tab).parent().addClass('active');
}

function editarHoras(id_horas_trabajo) {
    fetch(`../procesos/horas_trabajo/horas_trabajo.php`, {
        method: 'POST',
        body: new URLSearchParams({action: 'obtener', id_horas_trabajo})
    })
    .then(resp => resp.json())
    .then(horas => mostrarFormularioHoras(horas));
}
function eliminarHorasTrabajo(id) {
    if (confirm('¿Seguro que quieres marcar este registro como eliminado?')) {
        $.post('../procesos/horas_trabajo/horas_trabajo.php', {
            action: 'eliminar',
            id_horas_trabajo: id
        }, function(resp){
            let r = JSON.parse(resp);
            if (r.success) {
                alert('Registro marcado como eliminado.');
                cargarHoras();
            } else {
                alert('Error al marcar como eliminado.');
            }
        });
    }
}

function calcularTotalesGridHoras(dtTable) {
    let totalHoras = 0, totalCostoHora = 0, totalTotal = 0;
    if (!dtTable) return;
    dtTable.rows({ search: 'applied' }).every(function(){
        let data = this.data();
        totalHoras += parseFloat(data[5]) || 0;
        totalCostoHora += parseFloat(data[6]) || 0;
        totalTotal += parseFloat(data[7]) || 0;
    });
    $('#totalHoras').text(totalHoras.toFixed(2));
    $('#totalCostoHora').text('$' + totalCostoHora.toFixed(2));
    $('#totalTotal').text('$' + totalTotal.toFixed(2));
}

$(document).on('click', '#btnExportExcel', function() {
    var table = document.getElementById('gridHoras');
    var rows = Array.from(table.querySelectorAll('tbody tr'));
    var headerCells = Array.from(table.querySelectorAll('thead tr th'));
    var header = headerCells.slice(0,9).map(cell => cell.innerText);

    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('RegistrosHoras');

    let headerRow = worksheet.addRow(header);
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

    rows.forEach(row => {
        let dataRow = Array.from(row.cells).slice(0,9).map(cell => cell.innerText);
        worksheet.addRow(dataRow);
    });

    let totalRowData = [
        'TOTAL:', '', '', '', '',
        document.getElementById('totalHoras').innerText,
        document.getElementById('totalCostoHora').innerText,
        document.getElementById('totalTotal').innerText,
        ''
    ];
    let totalRow = worksheet.addRow(totalRowData);

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
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "RegistrosHoras.xlsx");
    });
});

$(document).on('click', '#btnExportPDF', function() {
    var table = document.getElementById('gridHoras');
    var headerCells = Array.from(table.querySelectorAll('thead tr th'));
    var header = headerCells.slice(0,9).map(cell => cell.innerText);
    var bodyRows = [];
    $('#gridHoras tbody tr').each(function() {
        var cols = [];
        $(this).find('td').each(function(i) {
            if (i < 9) cols.push($(this).text().trim());
        });
        if(cols.length) bodyRows.push(cols);
    });

    let totalRow = [
        'TOTAL:', '', '', '', '',
        document.getElementById('totalHoras').innerText,
        document.getElementById('totalCostoHora').innerText,
        document.getElementById('totalTotal').innerText,
        ''
    ];

    var doc = new window.jspdf.jsPDF();
    doc.text("Registros de Horas de Trabajo", 14, 18);
    doc.autoTable({
        head: [header],
        body: bodyRows,
        foot: [totalRow], // <-- footer separado!
        startY: 22,
        styles: { fontSize: 10 },
        headStyles: {
            fillColor: [41, 134, 197],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        footStyles: {
            fillColor: [41, 134, 197],
            textColor: [255,255,255],
            fontStyle: 'bold'
        }
    });
    doc.save("RegistrosHoras.pdf");
});
</script>
</body>
</html>
<?php 
}else{
    header("location:index.php");
}
?>