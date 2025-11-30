<?php 
session_start();
if(isset($_SESSION['usuario'])){ 
$id_horas_trabajo = $_GET['id_horas_trabajo'] ?? 0;

// Consulta el estado del registro de horas
require_once "../../clases/Conexion.php";
require_once "../../clases/HorasTrabajo.php";
$horas = new HorasTrabajo();
$datosHoras = $horas->obtener($id_horas_trabajo);
$estado = isset($datosHoras['estado']) ? $datosHoras['estado'] : 1; // 1 = activo, 0 = eliminado
?> 
<div>
    <h4>Empleados asignados al registro</h4>
    <button class="btn btn-default" id="btnAgregarEmpleadoInterno" <?php echo ($estado == 0) ? "disabled" : ""; ?>>
      <span class="glyphicon glyphicon-plus"></span> Agregar Empleado
    </button>
     <button id="btnExportExcelDetalle" class="btn btn-success">
       <i class="glyphicon glyphicon-download"></i> Excel
    </button>
    <button id="btnExportPDFDetalle" class="btn btn-danger">
         <i class="glyphicon glyphicon-download"></i> PDF
    </button>
    <?php if($estado == 0): ?>
        <span style="color:#a00;font-weight:bold;margin-left:15px;">Registro eliminado. No se pueden agregar ni eliminar empleados.</span>
    <?php endif; ?>  
    <hr> 
    <table id="gridEmpleadosInternos" class="table table-striped nowrap" style="width:100%">
    <thead>
        <tr>
            <th>Empleado</th>
            <th style="text-align: center;color: blue;"># Horas</th>
            <th style="text-align: center;color: blue;">Costo x Hora</th>
            <th style="text-align: center;color: blue;">Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <th style="text-align: right;">TOTAL:</th>
            <th style="text-align: center;color: blue;"  id="totalHoras"></th>
            <th style="text-align: center;color: blue;"  id="totalCosto"></th>
            <th style="text-align: center;color: blue;"  id="totalGeneral"></th>
            <th></th>
        </tr>
    </tfoot>
</table>
    <!-- Modal Empleado Interno -->
    <div id="modalEmpleadoInterno" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
        <form id="formEmpleadoInterno" class="modal-content">
            <div class="modal-header">
                <h4 id="modalEmpleadoTitle">Agregar Empleado</h4>
                <button type="button" id="closeModalInterno" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
               <input type="hidden" id="id_horas_trabajo_empleado" name="id_horas_trabajo_empleado">
               <input type="hidden" id="id_horas_trabajo" name="id_horas_trabajo" value="<?php echo $id_horas_trabajo;?>">
                <label class="label-custom" >Empleado:</label>
                <select class="form-control input-sm" id="id_empleado" name="id_empleado" style="width: 100%;" required></select>
               <div class="row">
                 <div class="col-sm-6">
                    <label  class="label-custom"># Horas:</label>
                    <input class="form-control input-sm" type="number" min="0" id="numero_horas" name="numero_horas" required>
                    <label  class="label-custom">Costo x Hora:</label>
                    <input class="form-control input-sm" type="number" min="0" step="0.01" id="costo_por_hora" name="costo_por_hora" required>
                 </div>    
                 <div class="col-sm-6">
                    <label  class="label-custom">Total:</label>
                    <input style="text-align: center;color: blue;font-size: x-large;" class="form-control input-sm" type="number" id="total" name="total" readonly>
                 </div> 
               </div> 
            </div>
            <div class="modal-footer form-acciones"> 
                <button type="button" id="cancelarInterno" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
                <button type="submit" class="btn btn-success" <?php echo ($estado == 0) ? "disabled" : ""; ?>>GUARDAR</button>
            </div>
        </form>
        </div>
    </div>
</div> 
<script>  
var empleadoOriginalEdit = null; // En vez de let
var empleadosActuales = [];      // En vez de let
$(document).ready(function() {
    const id_horas_trabajo = <?php echo json_encode($id_horas_trabajo); ?>;
    const estado = <?php echo json_encode($estado); ?>;

    $('#id_horas_trabajo').val(id_horas_trabajo);

    cargarEmpleadosInternos(id_horas_trabajo);
    cargarComboEmpleados();

    $(document).on('click', '#btnAgregarEmpleadoInterno', function() {
    if (estado == 0) return;
    $('#modalEmpleadoInterno').modal('show');
    $('#formEmpleadoInterno')[0].reset();
    $('#modalEmpleadoTitle').text('Agregar Empleado');
    $('#id_horas_trabajo_empleado').val('');
    $('#id_empleado').val(null).trigger('change');
    $('#numero_horas').val('');
    $('#costo_por_hora').val('');
    $('#total').val('');
    cargarComboEmpleados(); // Recarga select2 al abrir el modal
});

    // Igual para cerrar el modal:
$(document).on('click', '#cancelarInterno, #closeModalInterno', function() {
    $('#modalEmpleadoInterno').modal('hide');
});

  // Y para el submit:
$(document).on('submit', '#formEmpleadoInterno', function(e){
    e.preventDefault();
    if (estado == 0) return;

    const id_horas_trabajo_empleado = $('#id_horas_trabajo_empleado').val();
    const id_empleado = String($('#id_empleado').val());

    // AGREGAR: bloquea si ya existe
    if (!id_horas_trabajo_empleado) {
        if (empleadosActuales.includes(id_empleado)) {
            Swal.fire({
                title: "El empleado ya está asignado a esta hora de trabajo.",
                icon: "error",
                draggable: true,
            }); 
            return;
        }
    } else {
        // EDITAR: solo valida si cambió el empleado
        if (id_empleado !== empleadoOriginalEdit && empleadosActuales.includes(id_empleado)) {
            Swal.fire({
                title: "El empleado ya está asignado a esta hora de trabajo.",
                icon: "error",
                draggable: true,
            }); 
            return;
        }
    }
    guardarEmpleadoInterno();
});

// Delegación para el cálculo en campos dinámicos
$(document).on('input', '#numero_horas, #costo_por_hora', function() {
    let horas = parseFloat($('#numero_horas').val()) || 0;
    let costo = parseFloat($('#costo_por_hora').val()) || 0;
    $('#total').val((horas * costo).toFixed(2));
});
});

function cargarEmpleadosInternos(id_horas_trabajo) {
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_detalle_empleados', id_horas_trabajo})
    })
    .then(resp => resp.json())
    .then(data => {
        empleadosActuales = data.map(e => String(e.id_empleado));
        pintarGridEmpleadosInternos(data);
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function cargarComboEmpleados() {
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_empleados'})
    })
    .then(resp => resp.json())
    .then(empleados => {
        // DESTRUIR SELECT2 SI YA ESTÁ INICIALIZADO
        if ($('#id_empleado').hasClass("select2-hidden-accessible")) {
            $('#id_empleado').select2('destroy');
        }
        const sel = document.getElementById('id_empleado');
        sel.innerHTML = '';
        empleados.forEach(e => {
            sel.innerHTML += `<option value="${e.id_empleados}">${e.nombres_apellidos}</option>`;
        });
        // INICIALIZAR SELECT2 DESPUÉS DE AGREGAR OPCIONES
        $('#id_empleado').select2({
            width: 'resolve',
            dropdownParent: $('#modalEmpleadoInterno')
        });
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function pintarGridEmpleadosInternos(empleados) {
    const estado = <?php echo json_encode($estado); ?>;
    const tbody = document.querySelector('#gridEmpleadosInternos tbody');
    tbody.innerHTML = '';

    // Totales
    let sumHoras = 0, sumCosto = 0, sumTotal = 0;

    empleados.forEach(e => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${e.nombres_apellidos}</td>
            <td>${e.numero_horas}</td>
            <td>$${parseFloat(e.costo_por_hora).toFixed(2)}</td>
            <td>$${parseFloat(e.total).toFixed(2)}</td>
            <td>
                <button class="btn btn-warning" onclick="editarEmpleadoInterno(${e.id_horas_trabajo_empleado})" ${estado == 0 ? "disabled" : ""}><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick="eliminarEmpleadoInterno(${e.id_horas_trabajo_empleado})" ${estado == 0 ? "disabled" : ""}><i class="glyphicon glyphicon-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);

        // Sumar totales
        sumHoras += parseFloat(e.numero_horas) || 0;
        sumCosto += parseFloat(e.costo_por_hora) || 0;
        sumTotal += parseFloat(e.total) || 0;
    });

    // Mostrar totales en el tfoot
    document.getElementById('totalHoras').textContent    = sumHoras;
    document.getElementById('totalCosto').textContent    = `$${sumCosto.toFixed(2)}`;
    document.getElementById('totalGeneral').textContent  = `$${sumTotal.toFixed(2)}`;
}

function editarEmpleadoInterno(id_horas_trabajo_empleado) {
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'obtener_detalle_empleado', id_horas_trabajo_empleado})
    })
    .then(resp => resp.json())
    .then(data => {
        $('#modalEmpleadoInterno').modal('show');
        $('#modalEmpleadoTitle').text('Editar Empleado');
        $('#id_horas_trabajo_empleado').val(data.id_horas_trabajo_empleado);
        $('#id_horas_trabajo').val(data.id_horas_trabajo);
        $('#id_empleado').val(data.id_empleado).trigger('change');
        $('#numero_horas').val(data.numero_horas);
        $('#costo_por_hora').val(data.costo_por_hora);
        $('#total').val((parseFloat(data.numero_horas) * parseFloat(data.costo_por_hora)).toFixed(2));
        // Guardar el empleado original como string
        empleadoOriginalEdit = String(data.id_empleado);
        console.log("empleado original:" + empleadoOriginalEdit)
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function eliminarEmpleadoInterno(id) {
    const estado = <?php echo json_encode($estado); ?>;
    if(estado == 0) return;
    if(confirm("¿Eliminar empleado?")){
        fetch('../procesos/horas_trabajo/horas_trabajo.php', {
            method: 'POST',
            body: new URLSearchParams({action:'eliminar_detalle_empleado', id_horas_trabajo_empleado:id})
        })
        .then(resp => resp.json())
        .then(r => {
            cargarEmpleadosInternos(document.getElementById('id_horas_trabajo').value);
        })
        .catch(error => {
            console.error('Error AJAX:', error);
        });
    }
}

function guardarEmpleadoInterno() {
    const estado = <?php echo json_encode($estado); ?>;
    if (estado == 0) return;
    const datos = new FormData(document.getElementById('formEmpleadoInterno'));
    if ($('#id_horas_trabajo_empleado').val()) {
        datos.append('action', 'editar_detalle_empleado');
    } else {
        datos.append('action', 'agregar_detalle_empleados');
    }
    fetch('../procesos/horas_trabajo/horas_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => { 
        if(r.success === false && r.msg) {
             Swal.fire({
                        title: r.msg,
                        icon: "error",
                        draggable: true,
                  }); 
            return;
        }
        $('#modalEmpleadoInterno').modal('hide');
        cargarEmpleadosInternos(document.getElementById('id_horas_trabajo').value);
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}
$(document).on('click', '#btnExportExcelDetalle', function() {
    // Obtiene los datos del grid
    var rows = [];
    // Cabecera
    rows.push(["Empleado", "# Horas", "Costo x Hora", "Total"]);
    // Filas de datos
    $('#gridEmpleadosInternos tbody tr').each(function() {
        var cols = [];
        $(this).find('td').each(function(i) {
            // Ignora la columna de acciones
            if (i < 4) {
                cols.push($(this).text().trim());
            }
        });
        if(cols.length) rows.push(cols);
    });
    // Agrega la fila de totales si existe
    if ($('#gridEmpleadosInternos tfoot').length > 0) {
        var totalRow = [];
        totalRow.push("TOTAL:");
        totalRow.push($('#totalHoras').text().trim());
        totalRow.push($('#totalCosto').text().trim());
        totalRow.push($('#totalGeneral').text().trim());
        rows.push(totalRow);
    }
    // Genera el archivo Excel
    var wb = XLSX.utils.book_new();
    var ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Empleados");
    XLSX.writeFile(wb, "DetalleEmpleadosHoras.xlsx");
});
$(document).on('click', '#btnExportPDFDetalle', function() {
    // Genera los datos del grid
    var rows = [];
    // Cabecera
    rows.push(["Empleado", "# Horas", "Costo x Hora", "Total"]);
    // Filas de datos
    $('#gridEmpleadosInternos tbody tr').each(function() {
        var cols = [];
        $(this).find('td').each(function(i) {
            if (i < 4) cols.push($(this).text().trim());
        });
        if(cols.length) rows.push(cols);
    });
    // Agrega la fila de totales si existe
    if ($('#gridEmpleadosInternos tfoot').length > 0) {
        var totalRow = [];
        totalRow.push("TOTAL:");
        totalRow.push($('#totalHoras').text().trim());
        totalRow.push($('#totalCosto').text().trim());
        totalRow.push($('#totalGeneral').text().trim());
        rows.push(totalRow);
    }

    // Crea el PDF
    var doc = new jspdf.jsPDF();
    doc.text("Empleados asignados al registro", 14, 18);
    doc.autoTable({
        head: [rows[0]],
        body: rows.slice(1),
        startY: 22,
        styles: { fontSize: 10 },
    });
    doc.save("DetalleEmpleadosHoras.pdf");
});
</script>
<?php 
}else{
    header("location:../../index.php");
}
?>