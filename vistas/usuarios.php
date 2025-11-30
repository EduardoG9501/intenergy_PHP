<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "USUARIOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
}  
?>
<!DOCTYPE html>
<html>
<head>
    <title>Usuarios</title>
    <?php require_once "menu.php"; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        .panel-title a { display: block; width: 100%; }
        .panel-heading { padding: 8px 12px; }
        .checkbox label { font-weight: normal; }
        #contenedorMenus .panel-group { margin-bottom: 0; }
        #contenedorMenus .panel-body { padding: 8px 16px; } 
    </style>
</head>
<body>
    <div class="container">
        <h1>Administrar usuarios</h1>
        <div class="row">
            <div class="col-sm-4">
                <form id="frmRegistro">
                    <label>Nombre</label>
                    <input type="text" class="form-control input-sm" name="nombre" id="nombre">
                    <label>Apellido</label>
                    <input type="text" class="form-control input-sm" name="apellido" id="apellido">
                    <label>Usuario</label>
                    <input type="text" class="form-control input-sm" name="usuario" id="usuario">
                    <label>Password</label>
                    <input type="text" class="form-control input-sm" name="password" id="password">
                    <p></p>
                    <span class="btn btn-primary" id="registro">Registrar</span>
                </form>
            </div>
            <div class="col-sm-7">
                <div id="tablaUsuariosLoad"></div>
            </div>
        </div>
    </div>

    <!-- Modal Permisos -->
    <div class="modal fade" id="modalPermisosUsuario" tabindex="-1" role="dialog">
     <div class="modal-dialog smodal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background:#007399; color:#fff;">
            <h4 class="modal-title" style="color:#fff;">Permisos de usuario</h4>
          </div>
          <div class="modal-body">
            <form id="formPermisosUsuario">
              <input type="hidden" id="perm_id_usuario" name="id_usuario">
              <div id="contenedorMenus"></div>
              <!-- Bodegas permitidas -->
              <div class="form-group">
                <label style="color: #042f97;"><b>Bodegas permitidas:</b></label>
                <input type="text" class="form-control" id="filtroBodegas" placeholder="Buscar bodega...">
                <div id="listaBodegas" style="max-height: 150px; overflow-y: auto; border: 1px solid #ccc; padding: 8px; background: #f5f5f5;"></div>
              </div>
            </form>
          </div>
          <div style="  text-align: center;" class="modal-footer mi-option-menu">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success" id="guardarPermisos">Guardar Permisos</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Actualiza Usuario -->
    <div class="modal fade" id="actualizaUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Actualiza Usuario</h4>
                </div>
                <div class="modal-body">
                    <form id="frmRegistroU">
                        <input type="text" hidden="" id="idUsuario" name="idUsuario">
                        <label class="label-custom">Nombre</label>
                        <input type="text" class="form-control input-sm" name="nombreU" id="nombreU">
                        <label class="label-custom">Apellido</label>
                        <input type="text" class="form-control input-sm" name="apellidoU" id="apellidoU">
                        <label class="label-custom">Usuario</label>
                        <input type="text" class="form-control input-sm" name="usuarioU" id="usuarioU">
                        <label style="color: #064171;">Contraseña</label>
                        <input  placeholder="Nueva contraseña" type="password" name="passwordU" id="passwordU" class="form-control input-sm">
                    </form>
                </div>
                <div class="modal-footer">
                  <div style="text-align: center;"> 
                    <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
                    <button id="btnActualizaUsuario" type="button" class="btn btn-success" data-dismiss="modal">Actualiza Usuario</button>
                  </div>
                </div>
            </div>
        </div>
    </div> 
 <!-- JS Bootstrap, jQuery, DataTables, Select2, SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>  
<!-- dataTables CSS y JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script type="text/javascript">
function agregaDatosUsuario(idusuario){
    $.ajax({
        type:"POST",
        data:"idusuario=" + idusuario,
        url:"../procesos/usuarios/obtenDatosUsuario.php",
        success:function(r){
            dato=jQuery.parseJSON(r);
            $('#idUsuario').val(dato['id_usuario']);
            $('#nombreU').val(dato['nombre']);
            $('#apellidoU').val(dato['apellido']);
            $('#usuarioU').val(dato['email']);
            $('#passwordU').val('');
        }
    });
}

function eliminarUsuario(idusuario){
    alertify.confirm('¿Desea eliminar este usuario?', function(){ 
        $.ajax({
            type:"POST",
            data:"idusuario=" + idusuario,
            url:"../procesos/usuarios/eliminarUsuario.php",
            success:function(r){
                if(r==1){
                    $('#tablaUsuariosLoad').load('usuarios/tablaUsuarios.php');
                    alertify.success("Eliminado con exito!!");
                }else{
                    alertify.error("No se pudo eliminar :(");
                }
            }
        });
    }, function(){ 
        alertify.error('Cancelo !')
    });
}
function activarUsuario(idusuario){
    alertify.confirm('¿Desea activar este usuario?', function(){ 
        $.ajax({
            type:"POST",
            data:"idusuario=" + idusuario,
            url:"../procesos/usuarios/activarUsuario.php",
            success:function(r){
                if(r==1){
                    $('#tablaUsuariosLoad').load('usuarios/tablaUsuarios.php');
                    alertify.success("Usuario activado con exito!!");
                }else{
                    alertify.error("No se pudo activar el usuario :(");
                }
            }
        });
    }, function(){ 
        alertify.error('Cancelo !')
    });
}

// Agrupa menús por menu_padre
function agruparMenusPorPadre(menus) {
    let grupos = {};
    menus.forEach(function(menu){
        if (!grupos[menu.menu_padre]) grupos[menu.menu_padre] = [];
        grupos[menu.menu_padre].push(menu);
    });
    return grupos;
}

function renderPermisos(menus, permisos) {
    // Convierte permisos a string por seguridad
    permisos = permisos.map(String);
    let grupos = agruparMenusPorPadre(menus);
    let html = `<div class="panel-group" id="accordionPermisos">`;

    let idx = 0;
    for (let padre in grupos) {
        html += `
        <div class="panel panel-default">
            <div class="panel-heading" style="background:#014e67;color:#fff">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordionPermisos" href="#collapse${idx}" style="color:#fff;text-decoration:none">
                        <b>${padre}</b>
                    </a>
                </h4>
            </div>
            <div id="collapse${idx}" class="panel-collapse collapse${idx==0?' in':''}">
                <div class="panel-body">
        `;
        // hijos
        grupos[padre].forEach(function(menu){
            let checked = permisos.includes(menu.id_menu.toString()) ? 'checked' : '';
            html += `<div class="checkbox"><label>
                        <input type="checkbox" name="menus[]" value="${menu.id_menu}" ${checked}>
                        ${menu.menu_hijo}
                    </label></div>`;
        });
        html += `
                </div>
            </div>
        </div>
        `;
        idx++;
    }
    html += `</div>`;
    return html;
}

function abrirPermisosUsuario(id_usuario) {
    $('#perm_id_usuario').val(id_usuario);

    // Obtener todos los menús del sistema desde el proceso centralizado
    $.ajax({
        url: '../procesos/usuarios/permisos.php',
        type: 'POST',
        data: {action: 'listar_menus'},
        success: function(resMenus) {
            var menus = JSON.parse(resMenus);

            // Obtener permisos actuales del usuario
            $.ajax({
                url: '../procesos/usuarios/permisos.php',
                type: 'POST',
                data: {action: 'listar', id_usuario: id_usuario},
                success: function(resPermisos) {
                    var permisos;
                    try {
                        var obj = JSON.parse(resPermisos);
                        // Si es un objeto y tiene .menus, usa eso; si es array, úsalo directo
                        permisos = Array.isArray(obj) ? obj : (obj.menus ? obj.menus : []);
                    } catch(e) {
                        permisos = [];
                    }
                    $('#contenedorMenus').html(renderPermisos(menus, permisos));
                    cargarPermisosBodega(id_usuario);
                    $('#modalPermisosUsuario').modal('show');
                }
            });
        }
    });
}

$('#guardarPermisos').click(function() {
    var id_usuario = $('#perm_id_usuario').val();
    var menus = [];
    $('#formPermisosUsuario input[name="menus[]"]:checked').each(function(){
        menus.push($(this).val());
    });
    // obtiene las bodegas marcadas
    var bodegas = [];
    $('#listaBodegas input.checkBodega:checked').each(function(){
        bodegas.push($(this).val());
    });

    // Guardar menús
    $.ajax({
        url: '../procesos/usuarios/permisos.php',
        type: 'POST',
        data: {action: 'asignar', id_usuario: id_usuario, menus: menus},
        success: function(r) {
            var res = JSON.parse(r);
            if(res.success){
                // Guardar bodegas usando el proceso centralizado
                $.ajax({
                    url: '../procesos/usuarios/permisos.php',
                    type: 'POST',
                    data: {action: 'asignar_bodegas', id_usuario: id_usuario, bodegas: bodegas},
                    success: function(resp) {
                        $('#modalPermisosUsuario').modal('hide');
                        alertify.success("Permisos asignados con éxito");
                    }
                });
            }else{
                alertify.error("No se pudo asignar permisos");
            }
        }
    });
});

$(document).ready(function(){
    $('#btnActualizaUsuario').click(function(){
        datos=$('#frmRegistroU').serialize();
        $.ajax({
            type:"POST",
            data:datos,
            url:"../procesos/usuarios/actualizaUsuario.php",
            success:function(r){
                if(r==1){
                    $('#tablaUsuariosLoad').load('usuarios/tablaUsuarios.php');
                    alertify.success("Actualizado con exito :D");
                }else{
                    alert(r);
                    alertify.error("No se pudo actualizar :(");
                }
            }
        });
    });
    $('#tablaUsuariosLoad').load('usuarios/tablaUsuarios.php');
    $('#registro').click(function(){
        vacios=validarFormVacio('frmRegistro');
        if(vacios > 0){
            alertify.alert("Debes llenar todos los campos!!");
            return false;
        }
        datos=$('#frmRegistro').serialize();
        $.ajax({
            type:"POST",
            data:datos,
            url:"../procesos/regLogin/registrarUsuario.php",
            success:function(r){
                if(r==1){
                    $('#frmRegistro')[0].reset();
                    $('#tablaUsuariosLoad').load('usuarios/tablaUsuarios.php');
                    alertify.success("Agregado con exito");
                }else{
                    alertify.error("Fallo al agregar :(");
                }
            }
        });
    });
});  

function cargarPermisosBodega(id_usuario) {
    // Cargar todas las bodegas activas
    $.ajax({
        url: '../procesos/bodegas/listarpermisos.php',
        type: 'GET',
        success: function(data) {
            var bodegas = JSON.parse(data);

            // Ahora pide las bodegas ya asignadas
            $.ajax({
                url: '../procesos/usuarios/permisos.php',
                type: 'POST',
                data: {action: 'listar_bodegas', id_usuario: id_usuario},
                success: function(data) {
                    var asignadas = JSON.parse(data);

                    // Renderiza la lista con checkboxes
                    let html = '';
                    bodegas.forEach(function(bodega) {
                        let checked = asignadas.includes(bodega.id_bodega) || asignadas.includes(bodega.id_bodega.toString()) ? 'checked' : '';
                        html += `
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" class="checkBodega" value="${bodega.id_bodega}" ${checked}>
                                ${bodega.nombreBodega}
                            </label>
                        </div>
                        `;
                    });
                    $('#listaBodegas').html(html);

                    // Filtro en tiempo real
                    $('#filtroBodegas').off('keyup').on('keyup', function(){
                        let value = $(this).val().toLowerCase();
                        $('#listaBodegas .checkbox').each(function(){
                            let text = $(this).text().toLowerCase();
                            $(this).toggle(text.indexOf(value) > -1);
                        });
                    });
                }
            });
        }
    });
}
</script>
<?php 
}else{
    header("location:../index.php");
}
?>
<style>
@media (max-width: 767px) {
  .mi-option-menu {
    text-align: center;
    width: 60% !important; 
  }
}
</style>