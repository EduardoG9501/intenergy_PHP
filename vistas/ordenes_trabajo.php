<script>
    // Función para editar una orden
    function editarOrden(id_orden) {
        fetch(`../procesos/ordenestrabajo/orden_trabajo.php?action=obtener&id=${id_orden}`)
            .then(resp => resp.json())
            .then(orden => {
                mostrarFormulario(orden);
            });
    }
</script>
<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
    /*HABILITAR ACCESO AL MENU*/     
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "ORDEN DE TRABAJO");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
<!DOCTYPE html>
    <html>
    <head>
                <script>
                // Inyectar la bodega activa de la sesión PHP a JS
                var bodegaActivaSesion = null;
                <?php if (isset($_SESSION['bodega_seleccionada'])): ?>
                    bodegaActivaSesion = '<?php echo $_SESSION['bodega_seleccionada']; ?>';
                <?php endif; ?>
                </script>
        <title>OPERACIONES -> ORDEN DE TRABAJO</title>
       <?php 
          require_once "menu.php";   
        ?>
       <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
       <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    </head>
<body>
    <div class="container">   
        <button id="btnAgregar" class="btn btn-primary">  <i class="glyphicon glyphicon-plus"></i> NUEVO </button>  
        <button id="btnExportExcel" class="btn btn-success">
            <i class="glyphicon glyphicon-download"></i> Excel
        </button>
        <button id="btnExportPDF" class="btn btn-danger">
            <i class="glyphicon glyphicon-download"></i> PDF
        </button>
        <hr> 
        <table id="gridOrdenes" class="table table-striped nowrap" style="width:100%">
         <caption><hash class="hash-custom">Ordenes de Trabajos</hash></caption>  
         <thead>
                <tr>
                    <th>ID</th>
                    <th>IDENTIFICADOR</th>
                    <th>PROYECTO</th>
                    <th>MANO DE LA OBRA</th> 
                    <th>FECHA</th>
                    <th>LUGAR</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <!-- Las órdenes se cargan aquí por JS -->
            </tbody>
        </table>
        
        <!-- Formulario Modal -->
        <div id="formModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <form id="formOrden" class="modal-content">
                <!-- HEADER -->
                <div class="modal-header">
                    <h3 id="formModalLabel">NUEVA ORDEN DE TRABAJO</h3>
                    <button id="closex" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <!-- BODY -->
                <div class="modal-body">
                    <input type="hidden" id="id_orden" name="id_orden">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="label-custom">PROYECTO:</label><br>
                            <select class="form-control input-sm" id="id_proyecto" name="id_proyecto" required style="width:100%"></select>
                        </div>
                        <div class="col-sm-6">
                            <label class="label-custom">NOMBRE DE OBRA:</label><br>
                            <select class="form-control input-sm" id="id_obra" name="id_obra" required style="width:100%"></select>
                        </div>
                    </div>
                    <div class="row" style="margin-top:10px;">
                        <div class="col-sm-6">
                            <div id="grupoIdentificador" style="display:none;">
                                 <label class="label-custom">IDENTIFICADOR:   </label> 
                                 <input class="form-control input-sm" type="text" id="identificador" name="identificador" readonly>
                             </div>
                            <label class="label-custom">FECHA:</label>
                            <input class="form-control input-sm" type="date" id="fecha" name="fecha" required>
                        </div>
                        <div class="col-sm-6">  
                           <label class="label-custom">LUGAR: </label>
                            <select  style="width: 270px;" id="id_bodega_principal" name="id_bodega_principal" required></select>
                               <!-- Observación eliminada -->
                             <!-- Estado -->                
                            <div id="grupoEstado" style="display:none;">
                              <label class="label-custom">ESTADO:</label>
                              <select style="width: 100%;" id="id_estado_orden" name="id_estado_orden" required></select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- FOOTER -->
                <div class="modal-footer form-acciones" style="text-align: center;"> 
                    <button type="button" id="btnCancelar" class="btn btn-danger">CANCELAR</button>
                    <button type="submit" id="btnGuardar" class="btn btn-success">GUARDAR</button>
                </div>
            </form>
        </div>
</div>
</div>
</body>
</html>

<style>
 .modal-header .close
   {
        margin-top: -50px !important;
   } 
 .modal-header {
    color: #fff !important;
    border-bottom: 1px solid #4e8307 !important;
    background: #046b8b;
    padding: 10px  !important;
}   
.modal {
    display: none;
    position: fixed;
    z-index: 10;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
}
#gridOrdenes tbody tr:hover {
    background-color: #e0f7fa;
    cursor: pointer;
    transition: background 0.2s;
} 
label {
    display: flex;
    flex-direction: column;
    font-size: 14px;
}
input, select, textarea {
    padding: 6px;
    font-size: 15px;
    border: 1px solid #bbb;
    border-radius: 5px;
}
label {
    display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: bold;
}
</style>
<script type="text/javascript"> 
    let bodegasCache = [];

    document.addEventListener('DOMContentLoaded', () => {
        cargarOrdenes();
        cargarBodegas();
        cargarEstados();
        cargarProyectos();
        cargarObras();
    function cargarProyectos() {
        fetch('../procesos/ordenestrabajo/orden_trabajo.php?action=proyectos')
            .then(resp => resp.json())
            .then(proyectos => {
                const select = document.getElementById('id_proyecto');
                select.innerHTML = '';
                proyectos.forEach(p => {
                    select.innerHTML += `<option value="${p.id}">${p.id} - ${p.nombre}</option>`;
                });
                $('#id_proyecto').select2({
                    placeholder: 'Seleccione un proyecto',
                    allowClear: true
                });
            });
    }

    function cargarObras() {
        fetch('../procesos/ordenestrabajo/orden_trabajo.php?action=obras')
            .then(resp => resp.json())
            .then(obras => {
                const select = document.getElementById('id_obra');
                select.innerHTML = '';
                obras.forEach(o => {
                    select.innerHTML += `<option value="${o.id}">${o.id} - ${o.nombre}</option>`;
                });
                $('#id_obra').select2({
                    placeholder: 'Seleccione una obra',
                    allowClear: true
                });
            });
    }

        const btnAgregar = document.getElementById('btnAgregar');
        const formModal = document.getElementById('formModal');
        const formOrden = document.getElementById('formOrden');
        const btnCancelar = document.getElementById('btnCancelar');
        const btnclosex = document.getElementById('closex');

        btnAgregar.onclick = () => mostrarFormulario();

        btnCancelar.onclick = () => {
            formModal.style.display = 'none';
            formOrden.reset();
        };
        btnclosex.onclick = () => {
            formModal.style.display = 'none';
            formOrden.reset();
        };
        formOrden.onsubmit = function(e) {
            e.preventDefault();
            guardarOrden();
        };

        // Exportar a Excel
        document.getElementById('btnExportExcel').onclick = exportarExcel;
        // Exportar a PDF
        document.getElementById('btnExportPDF').onclick = exportarPDF;
    });

    function cargarOrdenes() {
        fetch('../procesos/ordenestrabajo/orden_trabajo.php?action=listar')
            .then(resp => resp.json())
            .then(data => {
                pintarGrid(data);
            });
    } 

    function mostrarFormulario(orden = null) {
        const formModal = document.getElementById('formModal');
        const formModalLabel = document.getElementById('formModalLabel');
        const grupoIdentificador = document.getElementById('grupoIdentificador');
        const grupoEstado = document.getElementById('grupoEstado');
        formModal.style.display = 'flex';
        let selectBodega = document.getElementById('id_bodega_principal');
        // Bandera global para saber si es edición o nueva orden
        window._editandoOrden = !!orden;
        // Si es nueva orden, recargar bodegas para seleccionar la activa
        if (!window._editandoOrden) cargarBodegas();

        if (orden) {
            formModalLabel.textContent = 'Editar Orden';
            grupoIdentificador.style.display = 'block'; // Mostrar solo al editar
            grupoEstado.style.display = 'block';        // Mostrar solo al editar
            if (document.getElementById('id_orden')) document.getElementById('id_orden').value = orden.id_orden;
            if (document.getElementById('identificador')) document.getElementById('identificador').value = orden.identificador;
            if (document.getElementById('fecha')) document.getElementById('fecha').value = orden.fecha_inicial;
            if (selectBodega && orden.id_bodega_principal) {
                selectBodega.value = orden.id_bodega_principal;
                $('#id_bodega_principal').trigger('change');
                // Deshabilitar el combo Lugar y el select2 correctamente
                $('#id_bodega_principal').select2('destroy');
                selectBodega.disabled = true;
                $('#id_bodega_principal').select2({disabled: true});
                // Agregar input hidden para enviar el valor aunque esté deshabilitado
                let hiddenBodega = document.getElementById('hidden_bodega_principal');
                if (!hiddenBodega) {
                    hiddenBodega = document.createElement('input');
                    hiddenBodega.type = 'hidden';
                    hiddenBodega.id = 'hidden_bodega_principal';
                    hiddenBodega.name = 'id_bodega_principal';
                    selectBodega.parentNode.appendChild(hiddenBodega);
                }
                hiddenBodega.value = orden.id_bodega_principal;
            }
            if (orden.id_proyecto) $('#id_proyecto').val(orden.id_proyecto).trigger('change');
            if (orden.id_obra) $('#id_obra').val(orden.id_obra).trigger('change');
            // Preseleccionar el estado correcto
            if (orden.id_estado_orden) {
                document.getElementById('id_estado_orden').value = orden.id_estado_orden;
                const estadoSel = document.getElementById('id_estado_orden');
                if (estadoSel) {
                    // Esperar a que las opciones estén cargadas
                    setTimeout(() => {
                        estadoSel.value = orden.id_estado_orden;
                        $('#id_estado_orden').trigger('change');
                    }, 200);
                }
            }
        } else {
            formModalLabel.textContent = 'Agregar Nueva Orden';
            document.getElementById('formOrden').reset();
            document.getElementById('id_orden').value = '';
            grupoIdentificador.style.display = 'none'; // Ocultar al crear nuevo
            grupoEstado.style.display = 'none';        // Ocultar al crear nuevo
            // Obtener fecha actual en formato YYYY-MM-DD
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            const fechaHoy = `${yyyy}-${mm}-${dd}`;
            document.getElementById('fecha').value = fechaHoy;
            // Seleccionar la bodega activa de la sesión si existe
            if (selectBodega.options.length > 0) {
                let found = false;
                if (typeof bodegaActivaSesion !== 'undefined' && bodegaActivaSesion) {
                    for (let i = 0; i < selectBodega.options.length; i++) {
                        if (selectBodega.options[i].value == bodegaActivaSesion) {
                            selectBodega.selectedIndex = i;
                            found = true;
                            break;
                        }
                    }
                }
                if (!found) {
                    selectBodega.selectedIndex = 0;
                }
                $('#id_bodega_principal').trigger('change');
            }
            // Habilitar combo Lugar y select2
            selectBodega.disabled = false;
            if ($ && $.fn.select2) {
                $('#id_bodega_principal').select2('destroy');
                $('#id_bodega_principal').select2({disabled: false});
            }
            // Seleccionar estado APROBADO (id=2) por defecto si existe
            const estadoSel = document.getElementById('id_estado_orden');
            if (estadoSel) {
                let aprobadoOption = Array.from(estadoSel.options).find(opt => opt.value == '2');
                if (aprobadoOption) {
                    estadoSel.value = '2';
                    $('#id_estado_orden').trigger('change');
                } else {
                    estadoSel.selectedIndex = 0;
                }
            }
        }
        actualizarCamposPorEstado();
    }
    function guardarOrden() {
        const formOrden = document.getElementById('formOrden');
        // Validar que el combo Lugar tenga una opción seleccionada válida
        const selectBodega = document.getElementById('id_bodega_principal');
        if (!selectBodega.value) {
            // Si no hay valor, seleccionar la primera opción disponible
            if (selectBodega.options.length > 0) {
                selectBodega.selectedIndex = 0;
            }
        }
        const datos = new FormData(formOrden);
        datos.append('action', 'guardar');
        // El estado se toma del formulario, no se fuerza a APROBADO
        fetch('../procesos/ordenestrabajo/orden_trabajo.php', {
            method: 'POST',
            body: datos
        })
        .then(resp => resp.json())
        .then(r => {
            Swal.fire({
                    title: r.mensaje,
                    icon: "success",
                    draggable: true,
                }); 
            document.getElementById('formModal').style.display = 'none';
            cargarOrdenes();
        });
    }

    function cargarBodegas() {
        fetch('../procesos/ordenestrabajo/orden_trabajo.php?action=bodegas')
            .then(resp => resp.json())
            .then(bodegas => {
                bodegasCache = bodegas; // Guardamos para reutilizar
                const principal = document.getElementById('id_bodega_principal');

                // Solo principales
                principal.innerHTML = '';
                bodegas.filter(b => !b.es_bodega_secundaria).forEach(b => {
                    principal.innerHTML += `<option value="${b.id_bodega}">${b.nombre}</option>`;
                });
                $('#id_bodega_principal').select2();

                // Seleccionar la bodega activa de la sesión si existe
                let found = false;
                if (typeof bodegaActivaSesion !== 'undefined' && bodegaActivaSesion) {
                    for (let i = 0; i < principal.options.length; i++) {
                        if (principal.options[i].value == bodegaActivaSesion) {
                            principal.selectedIndex = i;
                            found = true;
                            break;
                        }
                    }
                }
                if (!found && principal.options.length > 0) {
                    principal.selectedIndex = 0;
                }
                $('#id_bodega_principal').trigger('change');
            });
    }

    function cargarEstados() {
        fetch('../procesos/ordenestrabajo/orden_trabajo.php?action=estados')
            .then(resp => resp.json())
            .then(estados => {
                const estadoSel = document.getElementById('id_estado_orden');
                estadoSel.innerHTML = '';
                estados.forEach(e => {
                    estadoSel.innerHTML += `<option value="${e.id_estado_orden}">${e.estado}</option>`;
                });
            });
    }

    window.onclick = function(event) {
        const formModal = document.getElementById('formModal');
        if (event.target == formModal) {
            formModal.style.display = "none";
            document.getElementById('formOrden').reset();
        }
    }
    function pintarGrid(ordenes) {
        const $grid = $('#gridOrdenes');
        // Destruye el DataTable ANTES de modificar el contenido
        if ($.fn.DataTable.isDataTable($grid)) {
            $grid.DataTable().destroy();
        }

        const tbody = document.querySelector('#gridOrdenes tbody');
        tbody.innerHTML = ''; // Limpia
        ordenes.forEach(orden => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${orden.id_orden}</td>
                <td>${orden.identificador}</td>
                <td>${orden.proyecto_nombre || ''}</td>
                <td>${orden.obra_nombre || ''}</td>
                <td>${orden.fecha_inicial || orden.fecha || ''}</td>
                <td>${orden.bodega_principal}</td>
                <td>${orden.estado_nombre}</td>
                <td>
                    <button class="btn btn-warning"  onclick="editarOrden(${orden.id_orden})"> <i class="glyphicon glyphicon-edit"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        // DataTable responsive
        if ($.fn.DataTable.isDataTable('#gridOrdenes')) {
            $('#gridOrdenes').DataTable().destroy();
        }
        $('#gridOrdenes').DataTable({
            responsive: true,
            destroy: true,
            order: [[0, 'desc']]
        }); 
    } 

    function actualizarCamposPorEstado() {
        const estado = document.getElementById('id_estado_orden').value;
        const desactivar = (estado === '5' || estado === '3');
        //alert(estado);
        //alert(desactivar);
        // Desactiva todos menos el botón Cancelar
        document.getElementById('identificador').disabled = desactivar;
        // document.getElementById('costo').disabled = desactivar;
        // document.getElementById('fecha_inicial').disabled = desactivar;
        // document.getElementById('fecha_final').disabled = desactivar;
        // ...no modificar combo Lugar aquí...
        // document.getElementById('observacion').disabled = desactivar;
        document.getElementById('id_estado_orden').disabled = desactivar;
        document.getElementById('btnGuardar').disabled = desactivar;
        // El botón Cancelar queda activo
    } 

    // ---------- EXPORTAR A EXCEL Y PDF ----------
    function exportarExcel() {
        var table = document.getElementById('gridOrdenes');
        var rows = Array.from(table.querySelectorAll('tbody tr'));
        var headerCells = Array.from(table.querySelectorAll('thead tr th'));
        var header = headerCells.slice(0,8).map(cell => cell.innerText);

        var workbook = new ExcelJS.Workbook();
        var worksheet = workbook.addWorksheet('OrdenesTrabajo');

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
            let dataRow = Array.from(row.cells).slice(0,8).map(cell => cell.innerText);
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
            saveAs(new Blob([buffer], { type: "application/octet-stream" }), "OrdenesTrabajo.xlsx");
        });
    }

    function exportarPDF() {
        var table = document.getElementById('gridOrdenes');
        var headerCells = Array.from(table.querySelectorAll('thead tr th'));
        var header = headerCells.slice(0,8).map(cell => cell.innerText);
        var bodyRows = [];
        $('#gridOrdenes tbody tr').each(function() {
            var cols = [];
            $(this).find('td').each(function(i) {
                if (i < 8) cols.push($(this).text().trim());
            });
            if(cols.length) bodyRows.push(cols);
        });

        var doc = new window.jspdf.jsPDF();
        doc.text("Órdenes de Trabajo", 14, 18);
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
        doc.save("OrdenesTrabajo.pdf");
    }
    // ---------------------------------------------
</script>
<?php 
}else{
    header("location:../index.php");
}
?>