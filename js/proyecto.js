$(document).ready(function() {
    // Al cargar, solo mostrar Agregar y ocultar los demás
    $('#btnAgregar').show();
    $('#btnActualizar').hide().addClass('d-none');
    $('#btnCancelarEdicion').hide().addClass('d-none');
    cargarProyectos();
    $('#formProyecto').on('submit', function(e) {
        e.preventDefault();
        let nombre = $('#nombre').val();
        let id = $('#idProyecto').val();
        let url = id ? '../procesos/proyecto/editarProyecto.php' : '../procesos/proyecto/agregarProyecto.php';
        $.post(url, $(this).serialize(), function(resp) {
                if (resp === 'ok') {
                    let esEdicion = id && id !== '';
                    Swal.fire({
                        title: esEdicion ? "Proyecto actualizado con éxito" : "Proyecto agregado con éxito",
                        icon: "success",
                        draggable: true,
                    });
                    cargarProyectos();
                    $('#formProyecto')[0].reset();
                    $('#btnAgregar').show();
                    $('#btnActualizar').addClass('d-none').hide();
                    $('#btnCancelarEdicion').addClass('d-none').hide();
                    $('#idProyecto').val('');
            } else if (resp === "EXISTE") {
                Swal.fire({
                    title: "Nombre duplicado",
                    icon: "warning",
                    text: "Ya existe un proyecto con ese nombre.",
                });
                $('#btnAgregar').show(); // Show Add button
                $('#btnActualizar').addClass('d-none'); // Hide Update button
                $('#btnCancelarEdicion').addClass('d-none'); // Hide Cancel button
                $('#idProyecto').val('');
            } else {
                let esEdicion = id && id !== '';
                Swal.fire({
                    title: esEdicion ? "Editar proyecto" : "Agregar proyecto",
                    icon: "error",
                    draggable: true,
                    text: esEdicion ? "No se pudo editar el proyecto" : "No se pudo agregar el proyecto"
                });
            }
        });
    });
    $('#btnActualizar').on('click', function() {
        $('#formProyecto').submit();
    });
    $('#btnCancelarEdicion').on('click', function() {
        $('#formProyecto')[0].reset();
        $('#btnAgregar').show();
        $('#btnActualizar').addClass('d-none');
        $('#btnCancelarEdicion').addClass('d-none');
        $('#idProyecto').val('');
        Swal.fire({
            title: "Edición cancelada",
            icon: "info",
            text: "Volviste al modo alta de proyecto.",
            draggable: true,
        });
    });
});
function cargarProyectos() {
    $.getJSON('../procesos/proyecto/obtenerProyectos.php', function(data) {
        let filas = '';
        $.each(data, function(i, item) {
            filas += `<tr>
                <td>${item.id}</td>
                <td>${item.nombre}</td>
                <td>${item.estado == 1 ? 'Activo' : 'Inactivo'}</td>
                <td>${item.fecha_registro}</td>
                <td><button class='btn btn-warning btn-sm' onclick='editarProyecto(${item.id}, "${item.nombre}")'><i class='fa fa-edit'></i></button></td>
                <td><button class='btn btn-danger btn-sm' onclick='eliminarProyecto(${item.id})'><i class='fa fa-trash'></i></button></td>
            </tr>`;
        });
        $('#tablaProyectos tbody').html(filas);
    });
}
function editarProyecto(id, nombre) {
    $('#idProyecto').val(id);
    $('#nombre').val(nombre);
    $('#btnAgregar').hide();
    $('#btnActualizar').show().removeClass('d-none');
    $('#btnCancelarEdicion').show().removeClass('d-none');
}
function eliminarProyecto(id) {
    Swal.fire({
        title: "¿Estás seguro que deseas eliminar este proyecto?",
        html: "<span>Se eliminará este proyecto de la base de datos en caso que presiones <b>Sí, eliminar</b>.</span>",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar!",
        cancelButtonText: "Cancelar",
        buttonsStyling: true
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('../procesos/proyecto/eliminarProyecto.php', {id}, function(resp) {
                if (resp === 'ok') {
                    cargarProyectos();
                    Swal.fire({
                        title: "Proyecto eliminado con éxito!!",
                        icon: "success",
                        draggable: true,
                    });
                } else {
                    Swal.fire({
                        title: "Eliminar proyecto",
                        icon: "error",
                        draggable: true,
                        text: "No se pudo eliminar el proyecto"
                    });
                }
            });
        }
    });
}
