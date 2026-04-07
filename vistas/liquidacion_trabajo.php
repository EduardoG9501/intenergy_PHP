<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();

if (isset($_SESSION['usuario'])) {
    require_once "../clases/Conexion.php";
    require_once '../clases/Usuarios.php';
    $usuarioObj = new usuarios();
    $acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "LIQUIDACION DE TRABAJO");
    if (!$acceso) {
        header("location:../index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>LIQUIDACION DE TRABAJO</title>
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
        .caja-observacion {
            border: 2px solid #b0daf5;
            border-radius: 6px;
            background: #f7fcff;
            padding: 10px;
            margin-top: 20px;
        }
        .titulo-caja {
            background: #d9edf7;
            border: 1px solid #9acfea;
            font-weight: bold;
            padding: 6px 8px;
            margin-bottom: 8px;
        }
        #previewImagenesLiquidacion {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .img-liquidacion-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px;
            background: #fff;
            width: 150px;
        }
        .img-liquidacion-item img {
            width: 100%;
            height: 95px;
            object-fit: cover;
            border-radius: 4px;
        }
        #listaImagenesPendientes {
            margin-top: 8px;
            font-size: 12px;
        }
        .item-pendiente-imagen {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            border: 1px solid #dcdcdc;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 4px;
        }
        .btn-agregar-imagen {
            margin-top: 0;
            width: 220px;
            display: inline-block;
            text-align: center;
            cursor: pointer;
        }
        .seccion-imagenes-adjuntas {
            border: 1px solid #cfe3f6;
            border-radius: 6px;
            background: #fafdff;
            margin-top: 10px;
            padding: 8px;
        }
        .titulo-imagenes-adjuntas {
            font-weight: bold;
            color: #1c5d99;
            margin-bottom: 8px;
            border-bottom: 1px dashed #b8d6ef;
            padding-bottom: 4px;
        }
        #imgZoomModalReporte {
            display: none;
            position: fixed;
            z-index: 99999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(44, 62, 80, 0.35);
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }
        #imgZoomModalReporte.visible {
            display: flex;
        }
        #imgZoomModalReporte img {
            max-width: 90vw;
            max-height: 90vh;
            box-shadow: 0 2px 28px #3393cb;
            border-radius: 14px;
            border: 4px solid #cde6fe;
            background: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">LIQUIDACION DE TRABAJO</div>
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
                    <label for="id_orden">ID Orden:</label>
                    <input type="text" id="id_orden" name="id_orden" class="form-control" style="width:200px" placeholder="ID ORDEN">
                </div>
                <div class="col-sm-2">
                    <button type="submit" style="width: 150px; margin-top: 15px;" class="btn btn-primary">
                        <i class="fa fa-search"></i> BUSCAR
                    </button>
                    <button type="button" style="width: 150px; margin-top: 10px;" id="btnGuardarLiquidacion" class="btn btn-warning">
                        <i class="fa fa-save"></i> GUARDAR
                    </button>
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

    <div class="caja-observacion">
        <div class="titulo-caja">MANO DE OBRA:</div>
        <textarea id="txtManoObra" class="form-control" rows="4" placeholder="Ingrese el detalle y recomendaciones de mano de obra..."></textarea>
    </div>

    <div class="caja-observacion">
        <div class="titulo-caja">IMPORTANTE:</div>
        <div>
            <textarea id="txtImportante" class="form-control" rows="4" placeholder="Ingrese observaciones importantes..."></textarea>
        </div>

        <div class="seccion-imagenes-adjuntas">
            <div class="titulo-imagenes-adjuntas">IMÁGENES ADJUNTAS:</div>
            <div style="text-align:right; margin-bottom:8px;">
                <input type="file" id="imagenes_liquidacion" class="form-control" accept="image/*" multiple style="position:absolute; left:-9999px; width:1px; height:1px; opacity:0;">
                <label for="imagenes_liquidacion" id="btnAgregarImagen" class="btn btn-primary  btn-agregar-imagen">+ Agregar imagen</label>
            </div>
            <div id="listaImagenesPendientes"></div>
            <div id="previewImagenesLiquidacion"></div>
        </div>
    </div>

    <div style="border-top:1px solid #d9e8f5; margin-top:10px; padding-top:10px; text-align:right; margin-bottom:0;">
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
            <tbody></tbody>
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
            <tbody></tbody>
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
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
function getBase64Image(img) {
    var canvas = document.createElement("canvas");
    canvas.width = img.naturalWidth || img.width;
    canvas.height = img.naturalHeight || img.height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    var dataURL = canvas.toDataURL("image/png");
    return dataURL.replace(/^data:image\/png;base64,/, "");
}
window.REPORTE_EJECUCION_ENDPOINT = '../procesos/reportes/liquidacion_trabajo.php';
window.REPORTE_EJECUCION_TITULO = 'LIQUIDACION DE TRABAJO';
window.REPORTE_EJECUCION_ARCHIVO = 'liquidacion_trabajo';
window.REPORTE_INFORMES_INCLUYE_ORDEN = true;
</script>
<div id="imgZoomModalReporte">
    <img src="" id="imgZoomModalReporteImg" alt="Zoom Imagen" />
</div>
<script src="../librerias/alertifyjs/alertify.min.js"></script>
<script src="../js/reporte_ejecucion_obra.js?v=<?php echo filemtime('../js/reporte_ejecucion_obra.js'); ?>"></script>
<script src="../js/liquidacion_trabajo.js"></script>
</body>
</html>
<?php
} else {
    header("location:index.php");
}
?>
