<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/InformeTrabajo.php";
$id_informe = $_GET['id_informe'];
$informe = new InformeTrabajo();
$imagenes = $informe->listarImagenes($id_informe);
?>
<style>
  /* Forzar separación entre botones de SweetAlert2 en todos los diálogos */
  .swal2-actions .btn + .btn {
    margin-left: 1rem !important;
  }
</style>
<div class="table-responsive">
    <button class="btn btn-default" onclick="$('#modalAgregarImagen').modal('show')">
        <i class="glyphicon glyphicon-plus"></i> Agregar imagen
    </button>
    <br><br>
    <table id="gridImagenes"  class="table table-striped nowrap" style="width:100%"> 
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Imagen</th>
                <th>Fecha subida</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($imagenes as $img): ?>
            <tr>
                <td><?= htmlspecialchars($img['nombre']) ?></td>
                <td>
                    <img src="../<?= htmlspecialchars($img['ruta']) ?>" alt="img" style="max-width:120px;max-height:80px">
                </td>
                <td><?= htmlspecialchars($img['fecha_subida']) ?></td>
                <td>
                    <button class="btn btn-warning" onclick="editarImagen(<?= $img['id_informe_imagen'] ?>)">
                        <i class="glyphicon glyphicon-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="eliminarImagen(<?= $img['id_informe_imagen'] ?>)">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar imagen -->
<div class="modal fade" id="modalAgregarImagen" tabindex="-1" role="dialog" aria-labelledby="modalAgregarImagenLabel">
  <div class="modal-dialog" role="document">
    <form id="formAgregarImagen" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modalAgregarImagenLabel">Agregar imagen</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_informe" value="<?= $id_informe ?>">
          <input type="file" name="archivoImagen" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Subir</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal para editar imagen -->
<div class="modal fade" id="modalEditarImagen" tabindex="-1" role="dialog" aria-labelledby="modalEditarImagenLabel">
  <div class="modal-dialog" role="document">
    <form id="formEditarImagen" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="modalEditarImagenLabel">Editar imagen</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_informe_imagen" id="id_informe_imagen">
          <input type="hidden" name="id_informe" value="<?= $id_informe ?>">
          <input type="file" name="archivoImagen" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Actualizar</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function recargarTablaImagenes() {
    var urlRelative = window.parent.location.href;
    $.post('../procesos/informe_trabajo/informe_trabajo.php', {
        action: 'listar_imagenes',
        id_informe: <?= $id_informe ?>
    }, function(resp) {
        let r = JSON.parse(resp);
        let $tbody = $('#gridImagenes tbody');
        $tbody.empty();
        r.forEach(function(img) {
            $tbody.append(`<tr>
                <td>${img.nombre}</td>
                <td><img src="../${img.ruta}" alt="img" style="max-width:120px;max-height:80px"></td>
                <td>${img.fecha_subida}</td>
                <td>
                    <button class="btn btn-warning" onclick="editarImagen(${img.id_informe_imagen})">
                        <i class="glyphicon glyphicon-edit"></i>
                    </button>
                    <button class="btn btn-danger" onclick="eliminarImagen(${img.id_informe_imagen})">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </td>
            </tr>`);
        });
    });
}

$('#formAgregarImagen').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('action', 'agregar_imagen');
    $.ajax({
        url: '../procesos/informe_trabajo/informe_trabajo.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            let r = JSON.parse(resp);
            if (r[0] && r[0].success) {
                $('#modalAgregarImagen').modal('hide');
                Swal.fire({
                    title: '¡Imagen subida correctamente!',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false
                }).then(() => {
                    recargarTablaImagenes();
                });
            } else {
                Swal.fire({
                    title: 'Error al subir la imagen.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: { confirmButton: 'btn btn-danger' },
                    buttonsStyling: false
                });
            }
        }
    });
});

function editarImagen(id) {
    $('#id_informe_imagen').val(id);
    $('#modalEditarImagen').modal('show');
}

$('#formEditarImagen').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('action', 'editar_imagen');
    $.ajax({
        url: '../procesos/informe_trabajo/informe_trabajo.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(resp){
            let r = JSON.parse(resp);
            if (r.success) {
                $('#modalEditarImagen').modal('hide');
                Swal.fire({
                    title: '¡Imagen actualizada!',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false
                }).then(() => {
                    recargarTablaImagenes();
                });
            } else {
                Swal.fire({
                    title: 'Error al actualizar la imagen.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: { confirmButton: 'btn btn-danger' },
                    buttonsStyling: false
                });
            }
        }
    });
});

function eliminarImagen(id) {
    Swal.fire({
        title: '¿Estás seguro que deseas eliminar esta imagen?',
        text: 'Se eliminará la imagen del informe en caso que presiones Sí, eliminar.',
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
            $.post('../procesos/informe_trabajo/informe_trabajo.php', {
                action: 'eliminar_imagen',
                id_informe_imagen: id
            }, function(resp) {
                let r = JSON.parse(resp);
                if (r.success) {
                    Swal.fire({
                        title: 'Imagen eliminada.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        customClass: { confirmButton: 'btn btn-success' },
                        buttonsStyling: false
                    }).then(() => {
                        recargarTablaImagenes();
                    });
                } else {
                    Swal.fire({
                        title: 'Error al eliminar la imagen.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        customClass: { confirmButton: 'btn btn-danger' },
                        buttonsStyling: false
                    });
                }
            });
        }
    });
}
</script>