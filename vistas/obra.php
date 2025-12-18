<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
session_start();
if(isset($_SESSION['usuario'])){ 
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "OBRA");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?>  
<!DOCTYPE html>
<html>
<head>
    <title>LISTA DE OBRAS</title>
    <?php require_once "menu.php"; ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="container">
        <h1>LISTA DE OBRAS</h1>
        <div class="row">
            <div class="col-md-6">
                <form id="formObra">
                    <div class="form-group">
                        <label for="nombreObra">Nombre de la Obra</label>
                        <input type="text" class="form-control" id="nombreObra" name="nombre" required>
                    </div>
                    <input type="hidden" id="idObra" name="id">
                    <button type="submit" class="btn btn-primary" id="btnAgregarObra">Agregar</button>
                    <button type="button" class="btn btn-warning d-none" id="btnActualizarObra">Actualizar</button>
                    <button type="button" class="btn btn-secondary d-none" id="btnCancelarEdicionObra">Cancelar edición</button>
                </form>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table id="tablaObras" class="table table-bordered table-striped">
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/obra.js"></script>
</body>
</html>
<?php 
}else{
    header("location:../index.php");
}
?>
