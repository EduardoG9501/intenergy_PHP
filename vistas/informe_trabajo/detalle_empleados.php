<?php 
session_start();
if(isset($_SESSION['usuario'])){ 
$id_informe = $_GET['id_informe'] ?? 0;
?> 
<div>
    <h4>Empleados del Informe</h4>
         <button class="btn btn-default" id="btnAgregarEmpleadoInterno">
            <span class="glyphicon glyphicon-plus"></span> Agregar Empleado
        </button>
    <hr>
    <table id="gridEmpleados"  class="table table-striped nowrap" style="width:100%"> 
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <!-- Modal Empleado Unificado -->
    <div id="modalEmpleado" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
        <form id="formEmpleado" class="modal-content">
            <div class="modal-header">
                <h4>Agregar/Editar Empleado</h4>
                <button type="button" id="closeModalEmpleado" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_informe_empleado" name="id_informe_empleado">
                <input type="hidden" id="id_informe" name="id_informe" value="<?php echo $id_informe;?>">
                <label>Empleado:</label>
                <select id="id_empleado" name="id_empleado" style="width: 100%;" required></select>
            </div>
            <div class="modal-footer form-acciones"> 
                <button type="button" id="cancelarEmpleado" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
                <button type="submit" class="btn btn-success">GUARDAR</button>
            </div>
        </form>
        </div>
    </div>
</div> 
<script>
function guardarEmpleado() {
    const datos = new FormData(document.getElementById('formEmpleado'));
    datos.append('action', $('#id_informe_empleado').val() ? 'editar_empleado_interno' : 'agregar_empleado_interno');
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => {
        $('#modalEmpleado').modal('hide');
        cargarEmpleados(document.getElementById('id_informe').value);
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

$(document).ready(function() {
    const id_informe = <?php echo json_encode($id_informe); ?>;
    $('#id_informe').val(id_informe);

    cargarEmpleados(id_informe);
    cargarComboEmpleados();

    $('#btnAgregarEmpleadoInterno').on('click', function() {
        $('#modalEmpleado').modal('show');
        $('#formEmpleado')[0].reset();
        $('#id_informe_empleado').val('');
    });

    $('#cancelarEmpleado, #closeModalEmpleado').on('click', function() {
        $('#modalEmpleado').modal('hide');
    });

    $('#formEmpleado').on('submit', function(e){
        e.preventDefault();
        guardarEmpleado();
    });
});

function cargarEmpleados(id_informe) {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_empleados_internos', id_informe})
    })
    .then(resp => resp.json())
    .then(data => pintarGridEmpleados(data))
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function cargarEmpleadosExternos(id_informe) {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_empleados_externos', id_informe})
    })
    .then(resp => resp.json())
    .then(data => pintarGridEmpleadosExternos(data))
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function cargarComboEmpleados() {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
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
        $('#id_empleado').select2({
            width: 'resolve',
            dropdownParent: $('#modalEmpleado')
        });
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function cargarComboEmpleadosExternos() {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_empleados'})
    })
    .then(resp => resp.json())
    .then(empleados => {
        // DESTRUIR SELECT2 SI YA ESTÁ INICIALIZADO
        if ($('#id_empleado_externo').hasClass("select2-hidden-accessible")) {
            $('#id_empleado_externo').select2('destroy');
        }
        const sel = document.getElementById('id_empleado_externo');
        sel.innerHTML = '';
        empleados.forEach(e => {
            sel.innerHTML += `<option value="${e.id_empleados}">${e.nombres_apellidos}</option>`;
        });
        $('#id_empleado_externo').select2({
            width: 'resolve',
            dropdownParent: $('#modalEmpleadoExterno')
        });
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}
function pintarGridEmpleados(empleados) {
    const tbody = document.querySelector('#gridEmpleados tbody');
    tbody.innerHTML = '';
    empleados.forEach(e => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${e.nombres_apellidos}</td>
            <td>
                <button class="btn btn-warning" onclick="editarEmpleado(${e.id_informe_empleado}, ${e.id_empleado})"><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick="eliminarEmpleado(${e.id_informe_empleado})"><i class="glyphicon glyphicon-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function pintarGridEmpleadosExternos(empleados) {
    const tbody = document.querySelector('#gridEmpleadosExternos tbody');
    tbody.innerHTML = '';
    empleados.forEach(e => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${e.nombres_apellidos}</td>
            <td>
                <button class="btn btn-warning" onclick="editarEmpleadoExterno(${e.id_informe_empleado_externo}, ${e.id_empleado_externo})"><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick="eliminarEmpleadoExterno(${e.id_informe_empleado_externo})"><i class="glyphicon glyphicon-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function editarEmpleado(id, id_empleado) {
    $('#modalEmpleado').modal('show');
    $('#id_informe_empleado').val(id);
    $('#id_empleado').val(id_empleado).trigger('change');
}
function eliminarEmpleado(id) {
    Swal.fire({
        title: '¿Estás seguro que deseas eliminar este registro?',
        text: 'Se eliminará este empleado del informe en caso que presiones Sí, eliminar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar!',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success ms-3',
            cancelButton: 'btn btn-danger me-3'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../procesos/informe_trabajo/informe_trabajo.php', {
                method: 'POST',
                body: new URLSearchParams({action:'eliminar_empleado_interno', id_informe_empleado:id})
            })
            .then(resp => resp.json())
            .then(r => {
                cargarEmpleados(document.getElementById('id_informe').value);
            })
            .catch(error => {
                console.error('Error AJAX:', error);
            });
        }
    });
}
function editarEmpleadoExterno(id, id_empleado_externo) {
    $('#modalEmpleadoExterno').modal('show');
    $('#id_informe_empleado_externo').val(id);
    $('#id_empleado_externo').val(id_empleado_externo).trigger('change');
}
function eliminarEmpleadoExterno(id) {
    if(confirm("¿Eliminar empleado externo?")){
        fetch('../procesos/informe_trabajo/informe_trabajo.php', {
            method: 'POST',
            body: new URLSearchParams({action:'eliminar_empleado_externo', id_informe_empleado_externo:id})
        })
        .then(resp => resp.json())
        .then(r => {
            cargarEmpleadosExternos(document.getElementById('id_informe').value);
        })
        .catch(error => {
            console.error('Error AJAX:', error);
        });
    }
}
function guardarEmpleadoInterno() {
    const datos = new FormData(document.getElementById('formEmpleadoInterno'));
    datos.append('action', $('#id_informe_empleado').val() ? 'editar_empleado_interno' : 'agregar_empleado_interno');
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => {
        $('#modalEmpleadoInterno').modal('hide');
        cargarEmpleadosInternos(document.getElementById('id_informe').value);
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}
function guardarEmpleadoExterno() {
    const datos = new FormData(document.getElementById('formEmpleadoExterno'));
    datos.append('action', $('#id_informe_empleado_externo').val() ? 'editar_empleado_externo' : 'agregar_empleado_externo');
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => {
        $('#modalEmpleadoExterno').modal('hide');
        cargarEmpleadosExternos(document.getElementById('id_informe').value);
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}
</script>
<?php 
}else{
    header("location:../../index.php");
}
?>
<style>
  /* Forzar separación entre botones de SweetAlert2 en todos los diálogos */
  .swal2-actions .btn + .btn {
    margin-left: 1rem !important;
  }
</style>