<?php 
	require_once "../../clases/Conexion.php";
	$c = new conectar();
	$conexion = $c->conexion();
?>    

<!-- Filtros y controles (fuera del <table>) -->
<div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
    <input type="text" hidden="" id="idmovimiento" name="idmovimiento">
    <div class="col-sm-2">
        <label class="label-custom">FECHA INICIO:</label>
        <input type="date" class="form-control input-sm" id="finicio" name="finicio">
        <label class="label-custom">FECHA FIN:</label>
        <input type="date" class="form-control input-sm" id="ffin" name="ffin">
    </div>
    <div class="col-sm-4">
        <label class="label-custom">ARTICULO: </label>
        <select class="form-control input-sm" id="articuloSelect" name="articuloSelect">
            <option value="0">TODOS</option>
            <?php 
                $sql = "SELECT id_producto,nombre FROM articulos WHERE estado = 1";
                $result = mysqli_query($conexion, $sql);
                while($ver = mysqli_fetch_row($result)): ?>
                    <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
            <?php endwhile; ?>
        </select>
        <label style="margin-top: 5px;" class="label-custom">LUGAR: </label>
        <select class="form-control input-sm" id="almacenSelect" name="almacenSelect">
            <option value="0">TODOS</option>
            <?php 
                $sql="SELECT id_bodega,nombreBodega FROM bodegas WHERE estado = 1";
                $result = mysqli_query($conexion, $sql);
                while($ver = mysqli_fetch_row($result)): ?>
                    <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="col-sm-4">
        <label style="margin-top: 5px;" class="label-custom">UBICACION: </label>
        <select class="form-control input-sm" id="almacenSelectSecundaria" name="almacenSelectSecundaria">
            <option value="0">TODOS</option>
            <?php 
                $sql="SELECT id_bodega,nombreBodega FROM bodegas WHERE estado = 1";
                $result = mysqli_query($conexion, $sql);
                while($ver = mysqli_fetch_row($result)): ?>
                    <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
            <?php endwhile; ?>
        </select>
        <label style="margin-top: 5px;" class="label-custom">ACCIONES:</label>
        <div>
            <button title="Agregar articulo" id="btnGenerarReporte" class="btn btn-success">
                <i class="glyphicon glyphicon-search"></i> GENERAR REPORTE
            </button>
        </div>
    </div>
    <div class="col-sm-2">
        <!-- Menú exportar -->
        <div class="dropdown mb-2">
            <div id="tablaStockExportTemp" style="display:none"></div>
            <button class="btn btn-primary dropdown-toggle" type="button" id="btnExportMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="glyphicon glyphicon-download"></i> Exportar
            </button>
            <div class="dropdown-menu" aria-labelledby="btnExportMenu">
                <a class="dropdown-item" href="#" id="btnExportExcel">
                    <i class="fa fa-file-excel" style="color:green"></i> EXCEL
                </a>
                <a class="dropdown-item" href="#" id="btnExportPDF">
                    <i class="fa fa-file-pdf" style="color:red"></i> PDF
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Aquí se carga la tabla de datos por AJAX -->
<div id="tablaStockLoadDetalle"></div>
<hr>

<script type="text/javascript">
$(document).ready(function() {
    // Inicializa fechas por defecto
    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var dd = String(today.getDate()).padStart(2, '0');
    var currentDate = yyyy + '-' + mm + '-' + dd;
    document.getElementById('ffin').value = currentDate;
    document.getElementById('finicio').value = currentDate;

    // Función para construir la URL AJAX para el reporte
    function getStockUrl() {
        var varticuloSelect = $("#articuloSelect").val();
        var valmacenSelect = $("#almacenSelect").val();
        var valmacenSelectSecundaria = $("#almacenSelectSecundaria").val();
        var vfinicio = $("#finicio").val();
        var vffin = $("#ffin").val();
        return "reportes/tablaStockDetalle.php?"
            + "varticuloSelect='" + varticuloSelect
            + "'&valmacenSelect='" + valmacenSelect
            + "'&valmacenSelectSecundaria='" + valmacenSelectSecundaria
            + "'&vfinicio='" + vfinicio
            + "'&vffin='" + vffin + "'";
    }

    // Cargar el grid al inicio
    detalleStock("0", "0", "0", "0", "0");

    // Botón para generar el reporte
    $('#btnGenerarReporte').off('click').on('click', function() {
        var varticuloSelect = $("#articuloSelect").val();
        var valmacenSelect = $("#almacenSelect").val();
        var valmacenSelectSecundaria = $("#almacenSelectSecundaria").val();
        var vfinicio = $("#finicio").val();
        var vffin = $("#ffin").val();
        detalleStock(varticuloSelect, valmacenSelect, valmacenSelectSecundaria, vfinicio, vffin);
    });

    // Función para cargar el grid por AJAX
    function detalleStock(varticuloSelect, valmacenSelect, valmacenSelectSecundaria, vfinicio, vffin) {
        $('#tablaStockLoadDetalle').load(
            "reportes/tablaStockDetalle.php?varticuloSelect='" + varticuloSelect
            + "'&valmacenSelect='" + valmacenSelect
            + "'&valmacenSelectSecundaria='" + valmacenSelectSecundaria
            + "'&vfinicio='" + vfinicio
            + "'&vffin='" + vffin + "'"
        );
    } 
});
</script>
<style>
hr {
    border-top: 2px solid #eadfdf;
}
</style>