$(document).ready(function() {
    $('#btnAgregarObra').show();
    $('#btnActualizarObra').hide().addClass('d-none');
    $('#btnCancelarEdicionObra').hide().addClass('d-none');
    cargarObras();
    $('#formObra').on('submit', function(e) {
        e.preventDefault();
        let nombre = $('#nombreObra').val();
        let id = $('#idObra').val();
        let url = id ? '../procesos/obra/editarObra.php' : '../procesos/obra/agregarObra.php';
        $.post(url, $(this).serialize(), function(resp) {
            if (resp === 'ok') {
                let esEdicion = id && id !== '';
                Swal.fire({
                    title: esEdicion ? "Obra actualizada con éxito" : "Obra agregada con éxito",
                    icon: "success",
                    draggable: true,
                });
                cargarObras();
                $('#formObra')[0].reset();
                $('#btnAgregarObra').show();
                $('#btnActualizarObra').addClass('d-none').hide();
                $('#btnCancelarEdicionObra').addClass('d-none').hide();
                $('#idObra').val('');
            } else if (resp === "EXISTE") {
                Swal.fire({
                    title: "Nombre duplicado",
                    icon: "warning",
                    text: "Ya existe una obra con ese nombre.",
                });
                $('#btnAgregarObra').show();
                $('#btnActualizarObra').addClass('d-none').hide();
                $('#btnCancelarEdicionObra').addClass('d-none').hide();
                $('#idObra').val('');
            } else {
                let esEdicion = id && id !== '';
                Swal.fire({
                    title: esEdicion ? "Editar obra" : "Agregar obra",
                    icon: "error",
                    draggable: true,
                    text: esEdicion ? "No se pudo editar la obra" : "No se pudo agregar la obra"
                });
            }
        });
    });
    $('#btnActualizarObra').on('click', function() {
        $('#formObra').submit();
    });
    $('#btnCancelarEdicionObra').on('click', function() {
        $('#formObra')[0].reset();
        $('#btnAgregarObra').show();
        $('#btnActualizarObra').addClass('d-none').hide();
        $('#btnCancelarEdicionObra').addClass('d-none').hide();
        $('#idObra').val('');
        Swal.fire({
            title: "Edición cancelada",
            icon: "info",
            text: "Volviste al modo alta de obra.",
            draggable: true,
        });
    });
});
function cargarObras() {
    $.getJSON('../procesos/obra/obtenerObras.php', function(data) {
        let filas = '';
        $.each(data, function(i, item) {
            filas += `<tr>
                <td>${item.id}</td>
                <td>${item.nombre}</td>
                <td>${item.estado == 1 ? 'Activo' : 'Inactivo'}</td>
                <td>${item.fecha_registro}</td>
                <td><button class='btn btn-warning btn-sm' onclick='editarObra(${item.id}, "${item.nombre}")'><i class='fa fa-edit'></i></button></td>
                <td><button class='btn btn-danger btn-sm' onclick='eliminarObra(${item.id})'><i class='fa fa-trash'></i></button></td>
            </tr>`;
        });
        $('#tablaObras tbody').html(filas);
    });
}
function editarObra(id, nombre) {
    $('#idObra').val(id);
    $('#nombreObra').val(nombre);
    $('#btnAgregarObra').hide();
    $('#btnActualizarObra').show().removeClass('d-none');
    $('#btnCancelarEdicionObra').show().removeClass('d-none');
}
function eliminarObra(id) {
    Swal.fire({
        title: "¿Estás seguro que deseas eliminar esta obra?",
        html: "<span>Se eliminará esta obra de la base de datos en caso que presiones <b>Sí, eliminar</b>.</span>",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar!",
        cancelButtonText: "Cancelar",
        buttonsStyling: true
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('../procesos/obra/eliminarObra.php', {id}, function(resp) {
                if (resp === 'ok') {
                    cargarObras();
                    Swal.fire({
                        title: "Obra eliminada con éxito!!",
                        icon: "success",
                        draggable: true,
                    });
                } else {
                    Swal.fire({
                        title: "Eliminar obra",
                        icon: "error",
                        draggable: true,
                        text: "No se pudo eliminar la obra"
                    });
                }
            });
        }
    });
}
