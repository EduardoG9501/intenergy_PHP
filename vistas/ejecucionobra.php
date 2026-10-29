<?php 
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
if(isset($_SESSION['usuario'])){ 
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "ejecucionobra.php");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
<!DOCTYPE html>
<html>
<head>
	<title>OPERACIONES -> EJECUCIÓN DE OBRA</title>
	<?php require_once "menu.php"; ?> 
	<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
	<div class="container">  
	<?php if (isset($_GET['idorden'])): ?>
		<button id="btnRegresarOrden" class="btn btn-warning" onclick="window.location.href='ordenes_trabajo.php'">
			&larr; Regresar
		</button>
		<span style="font-weight:bold; font-size:16px; margin-left:10px;">Orden Trabajo: <span style="font-weight:bold;color: #880202ff;" id="identificadorOrden"></span></span>
		<script>window._idOrdenTrabajo = '<?php echo $_GET['idorden']; ?>';</script>
	<?php else: ?>
		<script>window._idOrdenTrabajo = null;</script>
	<?php endif; ?>
	<button id="btnAgregar" class="btn btn-primary">
	  <i class="glyphicon glyphicon-plus"></i> NUEVO
	</button> 
	<button id="btnExportExcel" class="btn btn-success">
	   <i class="glyphicon glyphicon-download"></i> Excel
	</button>
	<button id="btnExportPDF" class="btn btn-danger">
		 <i class="glyphicon glyphicon-download"></i> PDF
	</button>
		<!-- Logo oculto para PDF -->
		<img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
		<hr>    
		<table id="gridEjecucionObra" class="table table-striped nowrap" style="width:100%">
		<caption><hash class="hash-custom">Ejecución de Obra</hash></caption>  
		<thead> 
			 <tr>
				<th>ID</th>
				<th style="width: 50px;">Orden Trabajo</th>
				<th>Fecha Solicitud</th>
				<th>Feriado</th>
				<th>Observación</th>
				<th>Estado</th>
				<th style="width: 150px;">Acciones</th>
			</tr>
		</thead>
		<tbody>
			<!-- Las ejecuciones se cargan aquí por JS -->
		</tbody>
		</table>
		<!-- Formulario Modal -->
		<div id="formModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<form id="formEjecucionObra" class="modal-content">
				<div class="modal-header">
					<h3 id="formModalLabel">NUEVA EJECUCIÓN DE OBRA</h3>
					<button id="closex" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="id_ejecucion_obra" name="id_ejecucion_obra">
					<div class="row">
						<div class="col-sm-6">
							<label class="label-custom">ORDEN DE TRABAJO:</label> 
							<select name="id_orden" id="id_orden" style="width: 270px;" required></select>
							<label class="label-custom">FECHA SOLICITUD:</label>
							<input class="form-control input-sm" type="date" id="fecha_solicitud" name="fecha_solicitud" required>
							<label class="label-custom">FERIADO:</label>
							<select class="form-control input-sm" id="feriado" name="feriado" required>
								<option value="0">No</option>
								<option value="1">Sí</option>
							</select>
						</div>
						<div class="col-sm-6">
							<label class="label-custom">OBSERVACIÓN:</label>
							<textarea style="width: 100%;" id="observacion" name="observacion"></textarea>  
							<div id="div_estado_ejecucion" style="display:none;">
							<label class="label-custom">ESTADO DE LA EJECUCIÓN:</label>
								<select class="form-control input-sm" id="id_estado_ejecucion_obra" name="id_estado_ejecucion_obra"></select>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer form-acciones" style="text-align: center;"> 
					<button type="button" id="btnCancelar" class="btn btn-danger">CANCELAR</button>
					<button type="submit" id="btnGuardar" class="btn btn-success">GUARDAR</button>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
<style>
.modal-header .close { margin-top: -50px !important; }
.modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
.modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center;}
#gridEjecucionObra tbody tr:hover { background-color: #e0f7fa; cursor: pointer; transition: background 0.2s;}
label { display: flex; flex-direction: column; font-size: 14px;}
input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
</style>
<script type="text/javascript" src="../js/ejecucion_obra.js"></script>
<?php } else { header("location:../index.php"); } ?>
