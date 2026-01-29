<?php 
session_start();
if (!isset($_SESSION['usuario'])) { header('location:../index.php'); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
        <!-- jQuery debe ir antes que DataTables y cualquier plugin que lo use -->
    <script src="../librerias/jquery-3.2.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <title>HORAS DE TRABAJO</title>
    <?php require_once "menu.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
        <style>
        .modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); justify-content: center; align-items: center;}
        .modal.show { display: flex !important; }
        </style>
</head>
<body>
<div class="container">
    <div style="display:flex; align-items:center; gap:20px; margin-bottom:10px;">
        <button id="btnRegresar" class="btn btn-warning" onclick="window.history.back()">&larr; Regresar</button>
        <div style="display:flex; align-items:center; gap:16px;">
            <label style="font-weight:bold; margin-bottom:0;">Fecha:</label>
            <span id="fecha_ejecucion_obra" style="font-weight:bold; color:#1976d2; font-size:16px;"></span>
            <label style="font-weight:bold; margin-bottom:0; margin-left:18px;">Feriado:</label>
            <span id="feriado_ejecucion_obra" style="font-weight:bold; color:#1976d2; font-size:16px;"></span>
        </div>
    </div>
    <h2>HORAS DE TRABAJO</h2>
    <button id="btnAgregarHTD" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i> NUEVO</button>
    <button id="btnExportExcelHTD" class="btn btn-success"><i class="glyphicon glyphicon-download"></i> Excel</button>
    <button id="btnExportPDFHTD" class="btn btn-danger"><i class="glyphicon glyphicon-download"></i> PDF</button>
    <hr>
    <table id="gridHorasTrabajoDiario" class="table table-striped nowrap" style="width:100%">
        <caption><hash class="hash-custom"></hash></caption>
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Hora Entrada</th>
    <script>
    // Forzar centrado del modal usando la clase .show
    $(document).ready(function() {
        // Mostrar modal centrado
        $('#btnAgregarHTD').off('click').on('click', function() {
            limpiarFormulario();
            $('#formModalHTD').addClass('show');
        });
        $('#btnCancelarHTD, #closexHTD').off('click').on('click', function() {
            $('#formModalHTD').removeClass('show');
        });
        // Cerrar modal al hacer click fuera del contenido
        $('#formModalHTD').on('click', function(e) {
            if (e.target === this) {
                $(this).removeClass('show');
            }
        });
    });
    </script>
                <th>Hora Salida</th>
                <th>#. Horas Normales</th>
                <th>#. Horas Extra</th>
                <th>#. Horas Extraordinarias</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Carga dinámica por JS -->
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right">Totales:</th>
                <th id="totalNormales">0</th>
                <th id="totalExtra">0</th>
                <th id="totalExtraordinarias">0</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <!-- Modal Formulario -->
    <div id="formModalHTD" class="modal" tabindex="-1" role="dialog">
        <form id="formHorasTrabajoDiario" class="modal-content">
            <div class="modal-header">
                <h3 id="formModalLabelHTD">NUEVO REGISTRO DE HORAS</h3>
                <button id="closexHTD" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id_horas_trabajo_diario_detalle" name="id_horas_trabajo_diario_detalle">
                <input type="hidden" id="id_ejecucion_obra" name="id_ejecucion_obra">
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-sm-12" style="display:flex; flex-direction:row; align-items:center; justify-content:space-between;">
                        <div style="display:flex; flex-direction:column; align-items:flex-start; width:20%; min-width:110px;">
                            <label style="font-weight:bold;">Fecha:</label>
                            <span id="fecha_ejecucion_obra_modal" style="font-weight:bold; color:#1976d2; font-size:16px;"></span>
                            <input type="hidden" id="fecha_solicitud" name="fecha_solicitud">
                        </div>
                        <div style="display:flex; flex-direction:column; align-items:flex-start; width:80%;">
                            <label style="font-weight:bold; text-align:left; width:100%;">Empleado:</label>
                            <select id="id_empleado" name="id_empleado" required style="width:100%; min-width:200px;"></select>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-bottom:10px;">
                    <div class="col-sm-2" style="display:flex; flex-direction:column; align-items:center;">
                        <label style="font-weight:bold;">Feriado:</label>
                        <select id="feriadoHTD" name="feriado" required disabled readonly style="width:100%;">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                        <input type="hidden" id="feriadoHTD_hidden" name="feriado">
                    </div>
                    <div class="col-sm-5" style="display:flex; flex-direction:column; align-items:center;">
                        <label style="font-weight:bold;">Hora Entrada:</label>
                        <input type="time" id="hora_entrada" name="hora_entrada" required style="width:100%;">
                    </div>
                    <div class="col-sm-5" style="display:flex; flex-direction:column; align-items:center;">
                        <label style="font-weight:bold;">Hora Salida:</label>
                        <input type="time" id="hora_salida" name="hora_salida" required style="width:100%;">
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-xs-12" style="background:#e3f2fd; padding:8px; border-radius:6px;">
                        <div class="form-group" style="display:flex; align-items:flex-start; gap:30px; justify-content:center;">
                            <div style="display:flex; flex-direction:column; align-items:center;">
                                <label style="margin-bottom:2px; font-weight:bold;"># H. NORMALES</label>
                                <input type="number" step="0.01" min="0" value="0" class="form-control" id="cantidad_horas_normal" name="cantidad_horas_normal" readonly style="width:80px; text-align:center; font-weight:bold; color:#1976d2;">
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:center;">
                                <label style="margin-bottom:2px; font-weight:bold;"># H. EXTRAS</label>
                                <input type="number" step="0.01" min="0" value="0" class="form-control" id="cantidad_horas_extra" name="cantidad_horas_extra" readonly style="width:80px; text-align:center; font-weight:bold; color:#1976d2;">
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:center;">
                                <label style="margin-bottom:2px; font-weight:bold;"># H. EXTRAORDINARIA</label>
                                <input type="number" step="0.01" min="0" value="0" class="form-control" id="cantidad_horas_extraordinaria" name="cantidad_horas_extraordinaria" readonly style="width:80px; text-align:center; font-weight:bold; color:#1976d2;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer form-acciones" style="text-align: center;">
                <button type="button" id="btnCancelarHTD" class="btn btn-danger">CANCELAR</button>
                <button type="submit" id="btnGuardarHTD" class="btn btn-success">GUARDAR</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="../js/horas_trabajo_diario.js"></script>
<script>
// Sincronizar la fecha en el modal con la fecha global
$(document).ready(function() {
    function syncFechaModal() {
        var fecha = $('#fecha_ejecucion_obra').text();
        $('#fecha_ejecucion_obra_modal').text(fecha);
    }
    // Al abrir el modal, sincronizar la fecha
    $('#btnAgregarHTD').on('click', syncFechaModal);
    // También al cargar por si acaso
    syncFechaModal();
});
</script>
<script>
// Sincronizar el valor de feriado en la cabecera
$(document).ready(function() {
    var id_ejecucion_obra = getParameterByName('id_ejecucion_obra') || '';
    if (id_ejecucion_obra) {
        $.get('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'getFeriado', id_ejecucion_obra: id_ejecucion_obra}, function(resp) {
            var r = JSON.parse(resp);
            if (r.success) {
                var feriadoTxt = (r.feriado == 1) ? 'Sí' : 'No';
                $('#feriado_ejecucion_obra').text(feriadoTxt);
            }
        });
    }
});
</script>
</body>
</html>
<style>
.modal-header .close { margin-top: -50px !important; }
.modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
.modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center;}
#gridHorasTrabajoDiario tbody tr:hover { background-color: #e0f7fa; cursor: pointer; transition: background 0.2s;}
label { display: flex; flex-direction: column; font-size: 14px;}
input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}

/* Azul cabecera y pie igual a horas_trabjo.php */
#gridHorasTrabajoDiario thead th {
    background: #2196f3;
    color: #fff;
    border-color: #1976d2;
}
#gridHorasTrabajoDiario tfoot th {
    background: #2196f3;
    color: #fff;
    border-color: #1976d2;
    font-weight: bold;
}
</style>
