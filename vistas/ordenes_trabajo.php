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
                    <th>Id</th>
                    <th>Identificador</th>
                    <th>Costo</th>
                    <th>Fecha Inicial</th>
                    <th>Fecha Final</th>
                    <th>Lugar</th>
                    <th>Ubicaciòn</th>
                    <th>Estado</th>
                    <th>Acciones</th>
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
                            <div id="grupoIdentificador" style="display:none;">
                                 <label class="label-custom">IDENTIFICADOR:   </label> 
                                 <input class="form-control input-sm" type="text" id="identificador" name="identificador" readonly>
                             </div>
                            <label class="label-custom">COSTO:</label>
                            <input class="form-control input-sm" type="number" id="costo" name="costo" required>
                            <label class="label-custom">FECHA INICIO: </label>
                            <input class="form-control input-sm" type="date" id="fecha_inicial" name="fecha_inicial" required>
                            <label class="label-custom">FECHA FIN: </label>
                            <input class="form-control input-sm" type="date" id="fecha_final" name="fecha_final" required>
                        </div>
                        <div class="col-sm-6">  
                           <label class="label-custom">LUGAR: </label>
                            <select  style="width: 270px;" id="id_bodega_principal" name="id_bodega_principal" required></select>
                            <label class="label-custom">UBICACION: </label>
                            <select  style="width: 270px;"  id="id_bodega_secundaria" name="id_bodega_secundaria" required></select>
                            <label class="label-custom">OBSERVACION:</label>
                            <textarea style="width: 100%;" id="observacion" name="observacion"></textarea> 
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
        let selectSecundaria = document.getElementById('id_bodega_secundaria');

        if (orden) {
            formModalLabel.textContent = 'Editar Orden';
            grupoIdentificador.style.display = 'block'; // Mostrar solo al editar
            grupoEstado.style.display = 'block';        // Mostrar solo al editar
            Object.keys(orden).forEach(k => {
                if (document.getElementById(k)) {
                    document.getElementById(k).value = orden[k];
                }
            });

            // Principal
            if (selectBodega && orden.id_bodega_principal) {
                selectBodega.value = orden.id_bodega_principal;
                $('#id_bodega_principal').trigger('change');
            }

            // Secundaria (esperar a que el cambio de principal cargue las secundarias)
            setTimeout(function() {
                if (selectSecundaria && orden.id_bodega_secundaria) {
                    selectSecundaria.value = orden.id_bodega_secundaria;
                    $('#id_bodega_secundaria').val(orden.id_bodega_secundaria).trigger('change');
                }
            }, 200); // Pequeño retardo para asegurar que se llenó el select

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

            // Asignar la fecha por defecto
            document.getElementById('fecha_inicial').value = fechaHoy;
            document.getElementById('fecha_final').value = fechaHoy;

            // Seleccionar SIEMPRE la primera opción en el select de bodega principal
            if (selectBodega.options.length > 0) {
                selectBodega.selectedIndex = 0;
                $('#id_bodega_principal').trigger('change');
                // Selecciona también la secundaria igual a la principal
                let principalValue = selectBodega.value;
                if (selectSecundaria) {
                    selectSecundaria.value = principalValue;
                    $('#id_bodega_secundaria').val(principalValue).trigger('change');
                }
            }
        }
        actualizarCamposPorEstado();
    }

    function editarOrden(id_orden) {
        fetch(`../procesos/ordenestrabajo/orden_trabajo.php?action=obtener&id_orden=${id_orden}`)
            .then(resp => resp.json())
            .then(orden => mostrarFormulario(orden));
    }
    function guardarOrden() {
        const formOrden = document.getElementById('formOrden');
        const datos = new FormData(formOrden);
        datos.append('action', 'guardar');
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
                const secundaria = document.getElementById('id_bodega_secundaria');

                // Solo principales
                principal.innerHTML = '';
                bodegas.filter(b => !b.es_bodega_secundaria).forEach(b => {
                    principal.innerHTML += `<option value="${b.id_bodega}">${b.nombre}</option>`;
                });
                $('#id_bodega_principal').select2();

                // Limpiar secundaria inicialmente
                secundaria.innerHTML = '';
                $('#id_bodega_secundaria').select2();

                // Cuando cambie principal, cargar la principal + secundarias hijas
                principal.onchange = function() {
                    let idPrincipal = principal.value;
                    secundaria.innerHTML = '';

                    // 1. Agrega la bodega principal seleccionada como opción
                    const bodegaPrincipalObj = bodegas.find(b => b.id_bodega == idPrincipal);
                    if (bodegaPrincipalObj) {
                        secundaria.innerHTML += `<option value="${bodegaPrincipalObj.id_bodega}">${bodegaPrincipalObj.nombre} (Principal)</option>`;
                    }

                    // 2. Agrega las bodegas secundarias hijas
                    bodegas.filter(b =>
                        b.es_bodega_secundaria === 1 &&
                        b.id_bodega_principal == idPrincipal
                    ).forEach(b => {
                        secundaria.innerHTML += `<option value="${b.id_bodega}">${b.nombre}</option>`;
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
                <td>${orden.costo}</td>
                <td>${orden.fecha_inicial}</td>
                <td>${orden.fecha_final}</td>
                <td>${orden.bodega_principal}</td>
                <td>${orden.bodega_secundaria}</td>
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
        // Desactiva todos menos el botón Cancelar
        document.getElementById('identificador').disabled = desactivar;
        document.getElementById('costo').disabled = desactivar;
        document.getElementById('fecha_inicial').disabled = desactivar;
        document.getElementById('fecha_final').disabled = desactivar;
        document.getElementById('id_bodega_principal').disabled = desactivar;
        document.getElementById('id_bodega_secundaria').disabled = desactivar;
        document.getElementById('observacion').disabled = desactivar;
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