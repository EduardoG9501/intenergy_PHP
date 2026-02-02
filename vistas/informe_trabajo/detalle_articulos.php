<?php 
session_start();
if(isset($_SESSION['usuario'])){ 
$id_informe = $_GET['id_informe'] ?? 0;
?> 
<div>
    <h4>Artículos Utilizados en el Informe</h4>
    <button class="btn btn-default" id="btnAgregarArticulo">
        <span class="glyphicon glyphicon-plus"></span> Agregar Artículo
    </button>
    <hr>
    <table id="gridArticulos" class="table table-striped nowrap" style="width:100%"> 
        <thead>
            <tr>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Lote</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <!-- Modal Artículo (Bootstrap) -->
    <div id="modalArticulo" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
        <form id="formArticulo" class="modal-content">
            <div class="modal-header">
                <h4>Agregar/Editar Artículo</h4>
                <button type="button" id="closeModalArticulo" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_informe_articulo" name="id_informe_articulo">
                <input type="hidden" id="id_informe" name="id_informe" value="<?php echo $id_informe;?>">
                <div>
                   <label class="label-custom">ARTICULO:</label>
                   <select id="id_producto" name="id_producto" style="width: 100%;" required></select>
                </div>
                <hr>
                <label class="label-custom" >CANTIDAD:</label>
                <input type="number" id="cantidad" name="cantidad" min="1" required>
                <label class="label-custom">LOTE:</label>
                <input type="text" id="lote" name="lote">
            </div>
            <div class="modal-footer form-acciones"> 
                <button type="button" id="cancelarArticulo" class="btn btn-danger" data-dismiss="modal">CANCELAR</button>
                <button type="submit" class="btn btn-success">GUARDAR</button>
            </div>
        </form>
        </div>
    </div>
</div> 
<style>
  /* Forzar separación entre botones de SweetAlert2 en todos los diálogos */
  .swal2-actions .btn + .btn {
    margin-left: 1rem !important;
  }
</style>
<script>
$(document).ready(function() {
    const id_informe = <?php echo json_encode($id_informe); ?>;
    $('#id_informe').val(id_informe);

    cargarArticulos(id_informe);
    cargarComboArticulos();

    $('#btnAgregarArticulo').on('click', function() {
        $('#modalArticulo').modal('show');
        $('#formArticulo')[0].reset();
        $('#id_informe_articulo').val('');
    });

    $('#cancelarArticulo, #closeModalArticulo').on('click', function() {
        $('#modalArticulo').modal('hide');
    });

    $('#formArticulo').on('submit', function(e){
        e.preventDefault();
        guardarArticulo();
    });
});

function cargarArticulos(id_informe) {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_articulos', id_informe})
    })
    .then(resp => resp.json())
    .then(data => pintarGridArticulos(data))
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function cargarComboArticulos() {
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: new URLSearchParams({action:'listar_articulos_combo'})
    })
    .then(resp => resp.json())
    .then(articulos => {
        // DESTRUIR SELECT2 SI YA ESTÁ INICIALIZADO
        if ($('#id_producto').hasClass("select2-hidden-accessible")) {
            $('#id_producto').select2('destroy');
        }
        const sel = document.getElementById('id_producto');
        sel.innerHTML = '';
        articulos.forEach(a => {
            sel.innerHTML += `<option value="${a.id_producto}">${a.nombre}</option>`;
        });
        // INICIALIZAR SELECT2 DESPUÉS DE AGREGAR OPCIONES
        $('#id_producto').select2({
            width: 'resolve',
            dropdownParent: $('#modalArticulo')
        });
    })
    .catch(error => {
        console.error('Error AJAX:', error);
    });
}

function pintarGridArticulos(articulos) {
    const tbody = document.querySelector('#gridArticulos tbody');
    tbody.innerHTML = '';
    articulos.forEach(a => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${a.nombre}</td>
            <td>${a.cantidad}</td>
            <td>${a.lote}</td>
            <td>
                <button class="btn btn-warning" onclick="editarArticulo(${a.id_informe_articulo},${a.id_producto},${a.cantidad},'${a.lote}')"><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick="eliminarArticulo(${a.id_informe_articulo})"><i class="glyphicon glyphicon-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function editarArticulo(id,id_producto,cantidad,lote) {
    $('#modalArticulo').modal('show');
    $('#id_informe_articulo').val(id);
    $('#id_producto').val(id_producto).trigger('change');
    $('#cantidad').val(cantidad);
    $('#lote').val(lote);
}
function eliminarArticulo(id) {
    Swal.fire({
        title: '¿Estás seguro que deseas eliminar este registro?',
        text: 'Se eliminará este artículo del informe en caso que presiones Sí, eliminar.',
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
                body: new URLSearchParams({action:'eliminar_articulo', id_informe_articulo:id})
            })
            .then(resp => resp.json())
            .then(r => {
                cargarArticulos(document.getElementById('id_informe').value);
            })
            .catch(error => {
                console.error('Error AJAX:', error);
            });
        }
    });
}
function guardarArticulo() {
    const datos = new FormData(document.getElementById('formArticulo'));
    datos.append('action', $('#id_informe_articulo').val() ? 'editar_articulo' : 'agregar_articulo');
    fetch('../procesos/informe_trabajo/informe_trabajo.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => {
        $('#modalArticulo').modal('hide');
        cargarArticulos(document.getElementById('id_informe').value);
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