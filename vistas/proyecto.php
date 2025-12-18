<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 

/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "PROYECTO");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?>  
<!DOCTYPE html>
<html>
<head>
    <title>REGISTROS GENERAL -> PROYECTOS</title>
    <?php require_once "menu.php"; ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="container">
        <h1>LISTA DE PROYECTOS</h1>
        <div class="row">
            <div class="col-md-6">
                <form id="formProyecto">
                    <div class="form-group">
                        <label for="nombre">Nombre del Proyecto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <input type="hidden" id="idProyecto" name="id">
                    <button type="submit" class="btn btn-primary" id="btnAgregar">Agregar</button>
                    <button type="button" class="btn btn-warning d-none" id="btnActualizar">Actualizar</button>
                    <button type="button" class="btn btn-secondary d-none" id="btnCancelarEdicion">Cancelar edición</button>
                </form>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table id="tablaProyectos" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/proyecto.js"></script>
    <script>
    $(document).ready(function() {
        setTimeout(function() {
            if ($.fn.DataTable.isDataTable('#tablaProyectos')) {
                $('#tablaProyectos').DataTable().destroy();
            }
            $('#tablaProyectos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        }, 500);
    });
    </script>
</body>
</html>
<?php 
}else{
    header("location:../index.php");
}
?>
