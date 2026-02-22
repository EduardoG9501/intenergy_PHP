<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
if(isset($_SESSION['usuario'])){
require_once "../clases/Conexion.php";
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "REPORTE DE EJECUSION DE OBRA");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REPORTE DE EJECUCIÓN DE OBRA</title>
    <?php require_once "menu.php"; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
        <style>
            .panel-filtros { background: #f5f5f5; border-radius: 6px; padding: 16px 12px; margin-bottom: 25px; box-shadow: 0 1px 3px #ddd; }
            .form-inline .form-control, .form-inline .select2-container { margin-right: 10px; }
            .table th, .table td { text-align: center; font-size: 11px; padding: 3px 4px; }
            #btnExportarExcel, #btnImprimir { margin-left: 10px; }
            .select2-container { min-width: 200px; }
            .titulo-reporte { text-decoration: underline; color:#1c5d99; font-weight:bold; font-size:20px; margin-bottom:15px; }
            tfoot tr { background: #2986c5 !important; color: #fff !important; font-weight: bold; }
            tfoot td { font-size: 16px; }
            /* Borde azul eliminado de la última columna de Materiales */
        </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">REPORTE DE EJECUCIÓN DE OBRA</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosEjecucionObra" class="form-inline" autocomplete="off">
            <div class="row" style="border: 3px solid #b0daf5;padding: 5px; margin: 0.5px; background:#f7fcff;border-radius: 11px;">
                <div class="col-sm-2">
                    <label for="fecha_inicial">Desde:</label>
                    <input type="date" id="fecha_inicial" name="fecha_inicial" class="form-control">
                    <label for="fecha_final">Hasta:</label>
                    <input type="date" id="fecha_final" name="fecha_final" class="form-control">
                </div>
                <div class="col-sm-3">
                    <label for="proyecto">Proyecto:</label>
                    <select id="proyecto" name="proyecto" class="form-control" style="width:200px"></select>
                    <label for="obra">Nombre de la Obra:</label>
                    <select id="obra" name="obra" class="form-control" style="width:200px"></select>
                </div>
                <div class="col-sm-3">
                    <label for="bodega">B. Lugar:</label>
                    <select id="bodega" name="bodega" class="form-control" style="width:200px"><option value="">Todos</option></select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" style="width: 150px; margin-top: 15px;" class="btn btn-primary">Buscar</button>
                </div>
                <div class="col-sm-2">
                    <button type="button" style="width: 150px; margin-top: 15px;" id="btnExportarExcel" class="btn btn-success">
                        <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button type="button" style="width: 150px; margin-top: 15px;" id="btnImprimir" class="btn btn-info">
                        <i class="fa-solid fa-file-pdf" style="color: red;"></i> Imprimir PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- Eliminado buscador personalizado global, se usarán los de DataTables en cada grid -->

        <!-- Buscador personalizado para Materiales -->
        <div style="text-align:right; margin-bottom:0; margin-top:-10px;">
            <input type="text" id="buscadorMateriales" class="form-control" style="display:inline-block; width:250px; margin-top:0; margin-bottom:0;" placeholder="Buscar en Materiales...">
        </div>
    <div class="seccion-reporte">
        <h4 style="margin-top:30px;">MATERIALES A UTILIZAR</h4>
        <table id="tablaMaterialesEjecucionObra" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID ORDEN</th>
                    <th>PROYECTO</th>
                    <th>NOMBRE DE LA OBRA</th>
                    <th>FECHA</th>
                    <th>LUGAR</th>
                    <th>ARTICULO</th>
                    <th>CANTIDAD</th>
                    <th>CONTABILIZADO</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se llena por JS -->
            </tbody>
            <tfoot>
                <tr style="background:#2986c5;color:#fff;font-weight:bold;">
                    <td colspan="6" style="text-align:right;">TOTALES GENERALES:</td>
                    <td id="ftTotalCantidadMateriales">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="seccion-reporte">
        <h4 style="margin-top:30px;">HORAS DE TRABAJO</h4>
        <table id="tablaHorasTrabajoEjecucionObra" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID ORDEN</th>
                    <th>PROYECTO</th>
                    <th>NOMBRE DE LA OBRA</th>
                    <th>FECHA</th>
                    <th>LUGAR</th>
                    <th>EMPLEADO</th>
                    <th>HORA ENTRADA</th>
                    <th>HORA SALIDA</th>
                    <th>H. NORMAL</th>
                    <th>H. EXTRAS</th>
                    <th>H. EXTRAORDINARIAS</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se llena por JS -->
            </tbody>
            <tfoot>
                <tr style="background:#2986c5;color:#fff;font-weight:bold;">
                    <td colspan="8" style="text-align:right;">TOTAL:</td>
                    <td id="ftTotalHorasNormales">0.00</td>
                    <td id="ftTotalHorasExtras">0.00</td>
                    <td id="ftTotalHorasExtraordinarias">0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="seccion-reporte">
        <h4 style="margin-top:30px;">INFORMES DIARIOS</h4>
        <table id="tablaInformesDiariosEjecucionObra" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID INFORME</th>
                    <th>NOMBRE DE LA OBRA</th>
                    <th>ORDEN TRABAJO</th>
                    <th>FECHA</th>
                    <th>LUGAR</th>
                    <th>UBICACIÓN</th>
                    <th>OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <!-- Se llena por JS -->
            </tbody>
        </table>
        <div id="detalleInformesDiarios">
            <!-- Aquí se mostrarán los detalles: Artículos, Empleado, Descripción, Imagen -->
        </div>
    </div>
</div>
<script>
// Utilidad para convertir imagen a base64 (para el logo en PDF)
function getBase64Image(img) {
    var canvas = document.createElement("canvas");
    canvas.width = img.naturalWidth || img.width;
    canvas.height = img.naturalHeight || img.height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    var dataURL = canvas.toDataURL("image/png");
    return dataURL.replace(/^data:image\/png;base64,/, "");
}
</script>
    <script src="../librerias/alertifyjs/alertify.min.js"></script>
    <script src="../js/reporte_ejecucion_obra.js"></script>
</body>
</html>
<?php 
}else{
    header("location:index.php");
}
?>