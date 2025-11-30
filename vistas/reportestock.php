<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "STOCK DE ARTICULOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES -> STOCK DE ARTICULOS</title>
    <?php require_once "menu.php"; ?>
    <?php  
    $c= new conectar();
    $conexion=$c->conexion(); 
    ?>
    <!-- Select2 CSS y JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<!-- ExcelJS para exportar Excel con formato -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <!-- FileSaver para guardar el archivo -->
     <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <style>
        .select2-container {min-width:220px;}
    </style>
</head>
<body> 
    <div class="container" id="containarDatosMaestros"> 
        <div class="row"> 
            <div class="col-sm-12">
                <div id="tablaStockLoad"></div>
            </div>
        </div>
    </div> 
</body> 
</html> 
<script type="text/javascript"> 
$(document).ready(function(){ 
    // Carga la vista de filtros y exportación, NO inicializa DataTable aquí
    $('#tablaStockLoad').load("reportes/tablaStock.php", function(){
        // Aplica Select2 a todos los selects cargados
        $('select').each(function(){
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    width: $(this).css("width") || '220px',
                    placeholder: "Seleccione una opción",
                    allowClear: true,
                    dropdownParent: $(this).parent()
                });
            }
        });
    });

    // Si se cargan selects por AJAX después, también aplica select2
    $(document).on('mouseenter', 'select', function(){
        if (!$(this).hasClass("select2-hidden-accessible")) {
            $(this).select2({
                width: $(this).css("width") || '220px',
                placeholder: "Seleccione una opción",
                allowClear: true,
                dropdownParent: $(this).parent()
            });
        }
    });
});
</script> 
<?php 
}else{
    header("location:index.php");
}
?>