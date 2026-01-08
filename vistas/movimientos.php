<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "MOVIMIENTOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
	<!DOCTYPE html>
	<html>
	<head>
		<title>INVENTARIO GENERAL -> MOVIMIENTOS</title>
		<?php require_once "menu.php"; ?>
		<?php $c= new conectar();
		     $conexion=$c->conexion(); 
		?>
		<!-- Select2 CSS y JS -->
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
	</head>
	<body>
		<div class="container" id="containarDatosMaestros"> 
			<div class="row"> 
				<div class="col-sm-12">
					<div id="tablaMovimientosLoad"></div>
				</div>
			</div>
		</div>
        <div class="container" id="containarDetalle"> 
			<div class="row"> 
				<div class="col-sm-12">
					<div id="tablaMovimientosDetalleLoad"></div>
				</div>
			</div>
		</div>
	   <!-- MODAL NUEVO MOVIMIENTO --> 
       <div class="modal fade" id="abremodalUpdateAgregarMovimeinto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">DETALLES DEL MOVIMIENTO</h4>
					</div>
					<div class="modal-body"> 
					 <form id="frmMovimientos" enctype="multipart/form-data">
					   <div class="row">
					     <div class="col-sm-6">
						  <label>TIPO MOVIMIENTO</label>
						  <select style="color: red;" class="form-control input-sm select2" id="tipoSelect" name="tipoSelect">
						  <?php 
								        $sql="SELECT id_tipo_movimiento,tipo
		                                from tipo_movimiento
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						  </select>  
						  <label>SUB TIPO MOVIMIENTO</label>
						  <select  style="color: blue;"  class="form-control input-sm select2" id="subtipoSelect" name="subtipoSelect">
							 <?php 
								        $sql="SELECT id_sub_tipo_movimiento,sub_tipo
		                                from sub_tipo_movimiento
										where estado = 1 and id_tipo_movimiento = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							 <?php endwhile; ?>
						    </select>  
							<label>LUGAR</label>
							<select style="color: #b95c00;" class="form-control input-sm select2" id="bodegaSelect" name="bodegaSelect">
								<?php 
									$sql = "SELECT id_bodega, nombreBodega FROM bodegas WHERE estado = 1";
									$result = mysqli_query($conexion, $sql);
									while ($ver = mysqli_fetch_row($result)):
								?>
									<option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
								<?php endwhile; ?>
							</select>
							<div id="extraCamposNuevoMovimiento">
								<label>FORMA PAGO</label>
								<select class="form-control input-sm select2" id="formapagoSelect" name="formapagoSelect">
									<option value="0">NO APLICA</option>
										<?php 
												$sql="SELECT id_forma_pago,forma_pago
												from forma_pago
												where estado = 1";
												$result=mysqli_query($conexion,$sql);
												while($ver=mysqli_fetch_row($result)): ?>
											 <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
										<?php endwhile; ?>
								</select>   
								<label>NO.DOCUMENTO/FACTURA</label>
								<input type="text" placeholder="Nùmero de documento" class="form-control input-sm" id="nodocumento" name="nodocumento"> 
								<label>FECHA PAGO</label>
								<input value="0001-01-01" type="date" class="form-control input-sm" id="fechapago" name="fechapago"> 
								<label>DIA PAGO</label>
								<input type="number" class="form-control input-sm" id="diapago" name="diapago" readonly>
							</div>
						</div>
						 <div class="col-sm-6">   
						  <label>PROVEEDOR</label>
						  <select style="color: #b95c00;" class="form-control input-sm select2" id="proveedorSelect" name="proveedorSelect">
							  <option value="0">NO APLICA</option>
							    <?php 
								        $sql="SELECT id_proveedor,razon_social
		                                from proveedor
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>  
						<label>FECHA DOCUMENTO</label>
						<input value="0001-01-01" type="date" class="form-control input-sm" id="fechadocumento" name="fechadocumento"> 
						<label>OBSERVACION</label>
						<textarea name="observacion" id="observacion" rows="5" cols="30"></textarea>
						<script type="text/javascript">
						// Inyectar la bodega activa de la sesión PHP a JS (igual que en ordenes_trabajo.php)
						var bodegaActivaSesion = null;
						<?php if (isset($_SESSION['bodega_seleccionada'])): ?>
						    bodegaActivaSesion = '<?php echo $_SESSION['bodega_seleccionada']; ?>';
						<?php endif; ?>

						// Seleccionar la bodega activa de la sesión al abrir el modal
						function seleccionarBodegaActiva() { 
							var $bodega = $('#bodegaSelect');
							if (bodegaActivaSesion && $bodega.find('option[value="'+bodegaActivaSesion+'"]').length > 0) {
								$bodega.val(bodegaActivaSesion).trigger('change');
							} else {
								$bodega.val($bodega.find('option').first().val()).trigger('change');
							}
						}
						$(document).ready(function(){
							function simplificarFormularioNuevoMovimiento() {
								var tipo = $('#tipoSelect option:selected').text().trim().toUpperCase();
								var subtipo = $('#subtipoSelect option:selected').text().trim().toUpperCase();
								if((tipo === 'ENTRADA' && subtipo === 'CARGOS') || (tipo === 'SALIDA' && subtipo === 'DESCARGO')) {
									$('#extraCamposNuevoMovimiento').hide();
								} else {
									$('#extraCamposNuevoMovimiento').show();
								}
							}
							$('#tipoSelect, #subtipoSelect').on('change', simplificarFormularioNuevoMovimiento);
							// Ejecutar al abrir modal: seleccionar bodega activa y valores por defecto
							$('#abremodalUpdateAgregarMovimeinto').on('show.bs.modal', function () {
								seleccionarBodegaActiva();
								// Seleccionar por defecto ENTRADA y CARGOS
								var $tipo = $('#tipoSelect');
								var $subtipo = $('#subtipoSelect');
								$tipo.find('option').each(function(){
									if($(this).text().trim().toUpperCase() === 'ENTRADA') {
										$tipo.val($(this).val()).trigger('change');
									}
								});
								var intentos = 0;
								function seleccionarCargosSiExiste() {
									var $subtipo = $('#subtipoSelect');
									var encontrado = false;
									$subtipo.find('option').each(function(){
										if($(this).text().trim().toUpperCase() === 'CARGOS') {
											$subtipo.val($(this).val()).trigger('change');
											encontrado = true;
										}
									});
									if (!encontrado && intentos < 10) {
										intentos++;
										setTimeout(seleccionarCargosSiExiste, 100);
									} else {
										simplificarFormularioNuevoMovimiento();
									}
								}
								seleccionarCargosSiExiste();
							});

							// Llamar seleccionarBodegaActiva() al hacer click en el botón NUEVO MOVIMIENTO si existe
							$(document).on('click', '[data-target="#abremodalUpdateAgregarMovimeinto"]', function() {
								setTimeout(function(){ seleccionarBodegaActiva(); }, 100);
							});
						});
						</script>
						</div>
					</div> 
					<p></p>  
					</form>
					</div>
					<div class="modal-footer">
						<div style="text-align: center;"> 
						   <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >CANCELAR</button>
                           <button title="Agregar nuevo movimiento" id="btnAgregaMovimiento" type="button" class="btn btn-success" data-dismiss="modal">GUARDAR</button>
						</div> 
				    </div>  
				</div>
			</div>
		</div>
		<!-- MODAL EDITAR MOVIMIENTO --> 
		<div class="modal fade" id="abremodalUpdateMovimiento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">DETALLES DEL MOVIMIENTO - MODO EDITAR</h4>
					</div>
					<div class="modal-body"> 
					 <form id="frmMovimientosU" enctype="multipart/form-data">
					   <input type="text" hidden="" id="idmovimiento" name="idmovimiento">
					   <div class="row">
					     <div class="col-sm-6">
						  <label>TIPO MOVIMIENTO</label>
						  <select   style="color: red;"  class="form-control input-sm select2" id="tipoSelectU" name="tipoSelectU">
						    <?php 
								        $sql="SELECT id_tipo_movimiento,tipo
		                                from tipo_movimiento
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>  
						   <label>SUB TIPO MOVIMIENTO</label>
						   <select  style="color: blue;"  class="form-control input-sm select2" id="subtipoSelectU" name="subtipoSelectU"></select>  
							<label>LUGAR</label>
						    <select style="color: #b95c00;" class="form-control input-sm select2" id="bodegaSelectU" name="bodegaSelectU">
							  <option value="A">Selecciona la bodega</option>
							    <?php 
								        $sql="SELECT id_bodega,nombreBodega
		                                from bodegas
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select> 
							<div id="extraCamposEditarMovimiento">
								<label>FORMA PAGO</label>
								<select class="form-control input-sm select2" id="formapagoSelectU" name="formapagoSelectU">
								 <option value="0">NO APLICA</option>
										<?php 
												$sql="SELECT id_forma_pago,forma_pago
												from forma_pago
												where estado = 1";
												$result=mysqli_query($conexion,$sql);
												while($ver=mysqli_fetch_row($result)): ?>
											 <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
										<?php endwhile; ?>
								</select>   
								<label>NO.DOCUMENTO/FACTURA</label>
								<input type="text" placeholder="Nùmero de documento" class="form-control input-sm" id="nodocumentoU" name="nodocumentoU"> 
								<label>FECHA PAGO</label>
								<input type="date" class="form-control input-sm" id="fechapagoU" name="fechapagoU"> 
								<label>DIA PAGO</label>
								<input type="number" class="form-control input-sm" id="diapagoU" name="diapagoU" readonly>
							</div>
						</div>
						 <div class="col-sm-6">   
						  <label>PROVEEDOR</label>
						  <select style="color: #b95c00;" class="form-control input-sm select2" id="proveedorSelectU" name="proveedorSelectU">
							 <option value="0">NO APLICA</option>
							    <?php 
								        $sql="SELECT id_proveedor,razon_social
		                                from proveedor
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>  
						<label>FECHA DOCUMENTO</label>
						<input type="date" class="form-control input-sm" id="fechadocumentoU" name="fechadocumentoU"> 
						<label>OBSERVACION</label>
						<textarea name="observacionU" id="observacionU" rows="5" cols="30"></textarea>
						<script type="text/javascript">
						$(document).ready(function(){
							function simplificarFormularioEditarMovimiento() {
								var tipo = $('#tipoSelectU option:selected').text().trim().toUpperCase();
								var subtipo = $('#subtipoSelectU option:selected').text().trim().toUpperCase();
								if((tipo === 'ENTRADA' && subtipo === 'CARGOS') || (tipo === 'SALIDA' && subtipo === 'DESCARGO')) {
									$('#extraCamposEditarMovimiento').hide();
								} else {
									$('#extraCamposEditarMovimiento').show();
								}
							}
							$('#tipoSelectU, #subtipoSelectU').on('change', simplificarFormularioEditarMovimiento);
							// Ejecutar al abrir modal por si ya está seleccionado
							$('#abremodalUpdateMovimiento').on('show.bs.modal', function () {
								setTimeout(simplificarFormularioEditarMovimiento, 200);
							});
						});
						</script>
						</div>
					</div> 
					<p></p>  
					</form>
					</div>
					<div class="modal-footer">
						<div style="text-align: center;"> 
						   <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >CANCELAR</button>
                           <button title="Agregar nuevo movimiento" id="btnActualizarMovimiento" type="button" class="btn btn-success" data-dismiss="modal">GUARDAR</button>
						</div> 
				    </div>  
				</div>
			</div>
		</div>

		<!-- MODAL VIEW DETALLE MOVIMIENTO --> 
		<div class="modal fade" id="abremodalDetalledateMovimiento" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">DETALLES DEL MOVIMIENTO - MODO VISTA</h4>
					</div>
					<div class="modal-body"> 
					 <form id="frmMovimientosD" enctype="multipart/form-data">
					   <input type="text" hidden="" id="idmovimientoD" disabled name="idmovimientoD">
					   <div class="row">
					     <div class="col-sm-6">
						  <label>TIPO MOVIMIENTO</label>
						  <select  style="color: red;"  class="form-control input-sm select2" id="tipoSelectD" disabled name="tipoSelectD">
							<?php 
								        $sql="SELECT id_tipo_movimiento,tipo
		                                from tipo_movimiento
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>  
						  <label>SUB TIPO MOVIMIENTO</label>
						  <select style="color: blue;" class="form-control input-sm select2" id="subtipoSelectD" disabled name="subtipoSelectD">
							 <?php 
								        $sql="SELECT id_sub_tipo_movimiento,sub_tipo
		                                from sub_tipo_movimiento
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>  
							<label>LUGAR</label>
						    <select  style="color: #b95c00;"   class="form-control input-sm select2" id="bodegaSelectD" disabled name="bodegaSelectD">
							  <option value="A">Selecciona la bodega</option>
							    <?php 
								        $sql="SELECT id_bodega,nombreBodega
		                                from bodegas
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select> 
							<label>FORMA PAGO</label>
						    <select class="form-control input-sm select2" id="formapagoSelectD" disabled name="formapagoSelectD">
							  <option value="A">Selecciona forma pago</option>
							    <?php 
								        $sql="SELECT id_forma_pago,forma_pago
		                                from forma_pago
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>   
						  <label>NO.DOCUMENTO/FACTURA</label>
						  <input type="text" placeholder="Nùmero de documento" disabled class="form-control input-sm" id="nodocumentoD" name="nodocumentoD"> 	   	
						</div>
						 <div class="col-sm-6">   
						  <label>PROVEEDOR</label>
						  <select style="color: #b95c00;" class="form-control input-sm select2" id="proveedorSelectD"  disabled name="proveedorSelectD">
							  <option value="A">Selecciona Proveedor</option>
							    <?php 
								        $sql="SELECT id_proveedor,razon_social
		                                from proveedor
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						    </select>  
						<label>FECHA DOCUMENTO</label>
						<input type="date" class="form-control input-sm" id="fechadocumentoD" disabled name="fechadocumentoD"> 
						<label>DIA PAGO</label>
						 <input type="number" class="form-control input-sm" id="diapagoD" disabled name="diapagoD">
						<label>FECHA PAGO</label>
						<input type="date" class="form-control input-sm" id="fechapagoD" disabled name="fechapagoD"> 
						<label>OBSERVACION</label>
						 <textarea name="observacionD" id="observacionD" disabled rows="5" cols="30"></textarea>
						</div>
					</div> 
					<p></p>  
					</form>
					</div>
					<div class="modal-footer">
						<div style="text-align: center;"> 
						   <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >REGRESAR</button> 
						</div> 
				    </div>  
				</div>
			</div>
		</div>  
	</body> 
	</html>

<!-- INICIO AJUSTE CORRECTO DE DÍAS ENTRE FECHA DOCUMENTO Y FECHA PAGO -->
<script type="text/javascript">
	window.editandoMovimiento = false;
// Obtiene fecha actual en formato yyyy-mm-dd
function getTodayStr() {
    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var dd = String(today.getDate()).padStart(2, '0');
    return yyyy + '-' + mm + '-' + dd;
}

// Diferencia en días entre dos fechas (date1 y date2 en yyyy-mm-dd)
function daysBetween(date1, date2) {
    var d1 = new Date(date1);
    var d2 = new Date(date2);
    d1.setHours(0,0,0,0);
    d2.setHours(0,0,0,0);
    return Math.floor((d2.getTime() - d1.getTime()) / (1000 * 60 * 60 * 24));
}

// NUEVO MOVIMIENTO
$(document).ready(function() {
    $('#fechapago').attr('min', getTodayStr());

    function actualizarFechaPago() {
        var fechaDocumento = $('#fechadocumento').val();
        var diasPago = parseInt($('#diapago').val());
        if (fechaDocumento && !isNaN(diasPago)) {
            var fecha = new Date(fechaDocumento);
            fecha.setDate(fecha.getDate() + diasPago);
            var yyyy = fecha.getFullYear();
            var mm = String(fecha.getMonth() + 1).padStart(2, '0');
            var dd = String(fecha.getDate()).padStart(2, '0');
            var nuevaFechaPago = yyyy + '-' + mm + '-' + dd;
            $('#fechapago').val(nuevaFechaPago);
            $('#fechapago').attr('min', getTodayStr());
        }
    }
    $('#fechadocumento, #diapago').on('change keyup', actualizarFechaPago);

    function actualizarDiasPago() {
        var fechaDocumento = $('#fechadocumento').val();
        var fechaPago = $('#fechapago').val();
        if (fechaDocumento && fechaPago) {
            var fecha1 = new Date(fechaDocumento);
            var fecha2 = new Date(fechaPago);
            var today = new Date(getTodayStr());
            fecha1.setHours(0,0,0,0);
            fecha2.setHours(0,0,0,0);
            if (fecha2 < today) {
                Swal.fire({
                    title: "La fecha de pago no puede ser menor que hoy",
                    icon: "error",
                });
                $('#fechapago').val(getTodayStr());
                fecha2 = today;
            }
            var diffDays = daysBetween(fechaDocumento, $('#fechapago').val());
            $('#diapago').val(diffDays);
        }
    }
    $('#fechapago').on('change', actualizarDiasPago);

    $('#diapago').prop('readonly', true);
});

// EDITAR MOVIMIENTO
$(document).ready(function() {
    $('#fechapagoU').attr('min', getTodayStr());

    function actualizarFechaPagoU() {
        var fechaDocumento = $('#fechadocumentoU').val();
        var diasPago = parseInt($('#diapagoU').val());
        if (fechaDocumento && !isNaN(diasPago)) {
            var fecha = new Date(fechaDocumento);
            fecha.setDate(fecha.getDate() + diasPago);
            var yyyy = fecha.getFullYear();
            var mm = String(fecha.getMonth() + 1).padStart(2, '0');
            var dd = String(fecha.getDate()).padStart(2, '0');
            var nuevaFechaPago = yyyy + '-' + mm + '-' + dd;
            $('#fechapagoU').val(nuevaFechaPago);
            $('#fechapagoU').attr('min', getTodayStr());
        }
    }
    $('#fechadocumentoU, #diapagoU').on('change keyup', actualizarFechaPagoU);

    function actualizarDiasPagoU() {
        var fechaDocumento = $('#fechadocumentoU').val();
        var fechaPago = $('#fechapagoU').val();
        if (fechaDocumento && fechaPago) {
            var fecha1 = new Date(fechaDocumento);
            var fecha2 = new Date(fechaPago);
            var today = new Date(getTodayStr());
            fecha1.setHours(0,0,0,0);
            fecha2.setHours(0,0,0,0); 
            if (fecha2 < today) {
                Swal.fire({
                    title: "La fecha de pago no puede ser menor que hoy",
                    icon: "error",
                });
                $('#fechapagoU').val(getTodayStr());
                fecha2 = today;
            }
            var diffDays = daysBetween(fechaDocumento, $('#fechapagoU').val());
            $('#diapagoU').val(diffDays);
        }
    }
    $('#fechapagoU').on('change', actualizarDiasPagoU);

    $('#diapagoU').prop('readonly', true);
});
</script>
<!-- FIN AJUSTE CORRECTO DE DÍAS ENTRE FECHA DOCUMENTO Y FECHA PAGO -->

<!-- El resto de tus scripts JS y funcionalidad se mantienen igual -->
<script type="text/javascript"> 
$(document).ready(function() { 
   setDateValue();
   $("#diapago").val('0');   
});
function setDateValue ()
{
  var today = new Date();
  var yyyy = today.getFullYear();
  var mm = String(today.getMonth() + 1).padStart(2, '0'); 
  var dd = String(today.getDate()).padStart(2, '0');     
  var currentDate = yyyy + '-' + mm + '-' + dd;
  document.getElementById('fechadocumento').value = currentDate;
  document.getElementById('fechapago').value = currentDate;
}
function agregaDatosMovimiento(idmovimiento){
	window.editandoMovimiento = true; // Desactiva validación temporalmente
	$.ajax({
		type:"POST",
		data:"idmovimiento=" + idmovimiento,
		url:"../procesos/movimientos/obtenDatosMovimiento.php",
		success:function(r){   
			var dato = jQuery.parseJSON(r); 
			$('#idmovimiento').val(dato['id_movimiento']);
			$('#tipoSelectU').val(dato['id_tipo_movimiento']);
			// Llenar subtipos por AJAX y seleccionar el correcto
			cargarSubMovimiento(dato['id_tipo_movimiento'], dato['id_sub_tipo_movimiento']);
			$('#bodegaSelectU').val(dato['id_bodega']);
			$('#formapagoSelectU').val(dato['id_forma_pago']); 
			$('#proveedorSelectU').val(dato['id_proveedor']);  
			// Refrescar select2 para los selects que no dependen de AJAX
			$('#tipoSelectU, #bodegaSelectU, #formapagoSelectU, #proveedorSelectU').trigger('change');
			$('#fechadocumentoU').val(dato['fechaDocumento']); 
			$('#diapagoU').val(dato['cant_dia_pago']); 
			$('#nodocumentoU').val(dato['no_documento']);   
			$('#fechapagoU').val(dato['fechaPago']); 
			$('#observacionU').val(dato['observacion']);  
			setTimeout(function() {
				window.editandoMovimiento = false; // Reactiva validación después de llenar
			}, 500); // espera a que termine el llenado y render, puedes ajustar el tiempo
		}
	});
}
function agregaDatosMovimientoDetalle(idmovimiento){
    $.ajax({
        type:"POST",
        data:"idmovimiento=" + idmovimiento,
        url:"../procesos/movimientos/obtenDatosMovimiento.php",
        success:function(r){   
            var dato = jQuery.parseJSON(r); 
            $('#idmovimientoD').val(dato['id_movimiento']);
            $('#tipoSelectD').val(dato['id_tipo_movimiento']);
            $('#subtipoSelectD').val(dato['id_sub_tipo_movimiento']);
            $('#bodegaSelectD').val(dato['id_bodega']);
            $('#formapagoSelectD').val(dato['id_forma_pago']); 
            $('#proveedorSelectD').val(dato['id_proveedor']);  
            // Si usas select2 en los select del detalle, refresca el valor visual:
            $('#tipoSelectD, #subtipoSelectD, #bodegaSelectD, #formapagoSelectD, #proveedorSelectD').trigger('change');
            $('#nodocumentoD').val(dato['no_documento']); 
			$('#fechadocumentoD').val(dato['fechaDocumento']); 
            $('#diapagoD').val(dato['cant_dia_pago']);   
            $('#fechapagoD').val(dato['fechaPago']); 
            $('#observacionD').val(dato['observacion']);   
            // Abre el modal SOLO cuando los datos estén listos
            //$('#abremodalDetalledateMovimiento').modal('show');
        }
    }); 
}
function detalleMovimiento(idmovimiento, estado,guardado){
	       $('#tablaMovimientosDetalleLoad').load("movimientos/detalleMovimiento.php?idmovimiento='"+ idmovimiento+ "'&estado='"+ estado+ "'&guardado='"+ guardado+ "'"); 
		   $('#containarDatosMaestros').hide()
		   $('#containarDetalle').show()
		}
function regresar(idmovimiento){ 
		   $('#tablaMovimientosLoad').load("movimientos/tablaMovimientos.php"); 
		   $('#containarDetalle').hide()
		   $('#containarDatosMaestros').show()
		}
function cargarSubMovimiento(idTipoMovimiento, idsubtipo){   
	var $subtipo = $("#subtipoSelectU");
	$subtipo.empty(); // Limpiar antes de llenar para evitar duplicados
	$.ajax({
		type:"POST",
		data:"filtro=" + idTipoMovimiento,
		url:"../procesos/movimientos/filtrarSubTipoMovimiento.php",
		success:function(r){    
			var dato = jQuery.parseJSON(r);  
			const obj = JSON.parse(dato);
			if (obj != null) {
				obj.forEach((option) => {
					const optionElement = document.createElement("option"); 
					optionElement.value = option.id_sub_tipo_movimiento;
					optionElement.text = option.sub_tipo;
					$subtipo.append(optionElement);
				}); 
				// Seleccionar el subtipo correcto y refrescar select2
				setTimeout(function() {
					$subtipo.val(idsubtipo).trigger('change');
				}, 50);
				// INICIALIZAR SELECT2 AL RECARGAR SUBTIPO U
				$subtipo.select2({
					width: '100%',
					dropdownParent: $('#abremodalUpdateMovimiento')
				});
			} else {
				Swal.fire({
					title: "No se encontraron resultados con el idTipoMovimiento: " + idTipoMovimiento,
					icon: "warning",
					draggable: true,
				}); 
			} 
		} 
	});
}
function eliminaMovimiento(idmovimiento){ 
swal.fire({
       title: "¿Estás seguro que deseas eliminar este movimiento?",
      text: "Se eliminara este movimiento y los movimientos de los articulos en caso que presiones Sí, eliminar.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#10671e",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, eliminar!",
      cancelButtonText: "Cancelar"
    }).then(function(result) { 
        if (result.value) { 
			$.ajax({
					type:"POST",
					data:"idmovimiento=" + idmovimiento,
					url:"../procesos/movimientos/eliminarMovimiento.php",
					success:function(r){  
						if(r==1){
							$('#tablaMovimientosLoad').load("movimientos/tablaMovimientos.php"); 
							Swal.fire({
                               title: "Movimiento eliminado con exito!!",
                                icon: "success",
                               draggable: true,
                             }); 
						}else{
							Swal.fire({
                               title: "No se pudo eliminar el movimiento!!",
                                icon: "error",
                               draggable: true,
                             }); 
						}
					}
				});
        }
    }); 	
}  
</script> 
	<script type="text/javascript">
		$(document).ready(function(){
			$('#btnActualizarMovimiento').click(function(){  
			   var vidmovimiento= $("#idmovimiento").val(); 
			   var vtipoSelect= $("#tipoSelectU").val();
               var vsubtipoSelect= $("#subtipoSelectU").val();
			   var vbodegaSelect=$("#bodegaSelectU").val();
               var vformapagoSelect=$("#formapagoSelectU").val();
               var vnodocumento=$("#nodocumentoU").val();
			   var vproveedorSelect=$("#proveedorSelectU").val();
			   var vfechadocumento=$("#fechadocumentoU").val();
		       var vdiapago=$("#diapagoU").val();
			   var vfechapago=$("#fechapagoU").val();
               var vobservacion=$("#observacionU").val();
					 
			   if (vbodegaSelect == "" || vbodegaSelect == "A" || vbodegaSelect == null) 
			   {
				Swal.fire({
                           title: "Seleccione la bodega",
                           icon: "error",
						   text : "Seleccione la bodega antes de procedar a guardar.",
                           draggable: true,
                        }); 
						return false;
			    }  
				if (vformapagoSelect == "0" && vsubtipoSelect =="1")
				  {
                         Swal.fire({
                           title: "Seleccione la forma de pago",
                           icon: "error",
						   text : "Seleccione la forma de pago antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				  }
				 if (vnodocumento == ""  && vsubtipoSelect =="1")
				  {
                         Swal.fire({
                           title: "Ingresar el NO.DOCUMENTO/FACTURA",
                           icon: "error",
						   text : "Debe de ingresar el NO.DOCUMENTO/FACTURA.",
                          draggable: true,
                        });  
						return false;
				  }
			      if (vproveedorSelect == "0"  && vtipoSelect =="1")
				  {
                          Swal.fire({
                           title: "Seleccione el proveedor.",
                           icon: "error",
						   text : "Seleccione el proveedor antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				  }
				  if ((vfechadocumento == "" || vfechadocumento == "0001-01-01")  && vtipoSelect =="1")
				  {
                          Swal.fire({
                           title: "Seleccione la fecha del documento.",
                           icon: "error",
						   text : "Seleccione la fecha del documento antes de procedar a guardar.",
                          draggable: true,
                        }); 
						return false; 
				  } 
				  if ((vdiapago == "" || vdiapago == "0")  && vformapagoSelect == "1")
				  {
                          Swal.fire({
                           title: "Ingresar los dias de pago.",
                           icon: "error",
						   text : "Ingresar los dias de pago antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				  }
				  if ((vfechapago == "" || vfechapago == "0001-01-01") && vformapagoSelect == "1")
				  {
                          Swal.fire({
                           title: "Seleccione la fecha de pago.",
                           icon: "error",
						   text : "Seleccione la fecha de pago antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				 }   
			    const inputValue= "idmovimiento="+vidmovimiento+"&tipoSelectU="+vtipoSelect+"&subtipoSelectU="+vsubtipoSelect+"&bodegaSelectU="+vbodegaSelect+"&formapagoSelectU="+vformapagoSelect+"&nodocumentoU="+vnodocumento+"&proveedorSelectU="+vproveedorSelect+"&fechadocumentoU="+vfechadocumento+"&diapagoU="+vdiapago+"&fechapagoU="+vfechapago+"&observacionU="+vobservacion;				     
				$.ajax({ 
                    type:"POST",
					data:inputValue, 
					url:"../procesos/movimientos/actualizaMovimientos.php", 
					success:function(r){ 
						if(r == 2){
							Swal.fire({
								title: "Factura repetida para proveedor",
								icon: "error",
								text : "Ya existe una factura con ese número para el proveedor seleccionado en compras.",
								draggable: true,
							}); 
							return false;
						}
						if(r==1){
							$('#tablaMovimientosLoad').load("movimientos/tablaMovimientos.php"); 
							Swal.fire({
                               title: "Movimiento actualizado con exito!!",
                                icon: "success",
                               draggable: true,
                             });
						}else{
							Swal.fire({
                               title: "Error al actualizar el movimiento!!",
                                icon: "error",
                               draggable: true,
                             }); 
						}
					}
				});
			});
		});
	</script> 
	<script type="text/javascript">
		$(document).ready(function(){
			$('#tablaMovimientosLoad').load("movimientos/tablaMovimientos.php"); 
			$('#btnAgregaMovimiento').click(function(){ 
				 
			   var vtipoSelect= $("#tipoSelect").val();
               var vsubtipoSelect= $("#subtipoSelect").val();
			   var vbodegaSelect=$("#bodegaSelect").val();
               var vformapagoSelect=$("#formapagoSelect").val();
               var vnodocumento=$("#nodocumento").val();
			   var vproveedorSelect=$("#proveedorSelect").val();
			   var vfechadocumento=$("#fechadocumento").val();
		       var vdiapago=$("#diapago").val();
			   var vfechapago=$("#fechapago").val();
               var vobservacion=$("#observacion").val();
			   
			   if (vbodegaSelect == "" || vbodegaSelect == "A" || vbodegaSelect == null) 
			   {
				Swal.fire({
                           title: "Seleccione la bodega",
                           icon: "error",
						   text : "Seleccione la bodega antes de procedar a guardar.",
                           draggable: true,
                        }); 
						return false;
			    }  
				if (vformapagoSelect == "0" && vsubtipoSelect =="1")
				  {
                         Swal.fire({
                           title: "Seleccione la forma de pago",
                           icon: "error",
						   text : "Seleccione la forma de pago antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				  }
				 if (vnodocumento == ""  && vsubtipoSelect =="1")
				  {
                         Swal.fire({
                           title: "Ingresar el NO.DOCUMENTO/FACTURA",
                           icon: "error",
						   text : "Debe de ingresar el NO.DOCUMENTO/FACTURA.",
                          draggable: true,
                        });  
						return false;
				  }
			      if (vproveedorSelect == "0"  && vtipoSelect =="1")
				  {
                          Swal.fire({
                           title: "Seleccione el proveedor.",
                           icon: "error",
						   text : "Seleccione el proveedor antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				  }
				  if ((vfechadocumento == "" || vfechadocumento == "0001-01-01")  && vtipoSelect =="1") 
				  {
                          Swal.fire({
                           title: "Seleccione la fecha del documento.",
                           icon: "error",
						   text : "Seleccione la fecha del documento antes de procedar a guardar.",
                          draggable: true,
                        }); 
						return false; 
				  }
				  if ((vdiapago == "" || vdiapago == "0")  && vformapagoSelect == "1")
				  {
                          Swal.fire({
                           title: "Ingresar los dias de pago.",
                           icon: "error",
						   text : "Ingresar los dias de pago antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				  }
				  if ((vfechapago == "" || vfechapago == "0001-01-01") && vformapagoSelect == "1")
				  {
                          Swal.fire({
                           title: "Seleccione la fecha de pago.",
                           icon: "error",
						   text : "Seleccione la fecha de pago antes de procedar a guardar.",
                          draggable: true,
                        });  
						return false;
				 }  
			    const inputValue= "tipoSelect="+vtipoSelect+"&subtipoSelect="+vsubtipoSelect+"&bodegaSelect="+vbodegaSelect+"&formapagoSelect="+vformapagoSelect+"&nodocumento="+vnodocumento+"&proveedorSelect="+vproveedorSelect+"&fechadocumento="+vfechadocumento+"&diapago="+vdiapago+"&fechapago="+vfechapago+"&observacion="+vobservacion;				     
				$.ajax({ 
                    type:"POST",
					data:inputValue, 
					url: "../procesos/movimientos/agregaMovimiento.php",
					success:function(r){    
						if(r == 2){
							Swal.fire({
								title: "Factura repetida para proveedor",
								icon: "error",
								text : "Ya existe una factura con ese número para el proveedor seleccionado en compras.",
								draggable: true,
							}); 
							return false;
						}
						if(r >= 1){  
							setDateValue();
							$('#frmMovimientos')[0].reset();
							$('#tablaMovimientosLoad').load("movimientos/tablaMovimientos.php"); 
							detalleMovimiento(r,'1','0'); 
						}else{
							Swal.fire({
                                title: "Error al agregar el movimiento.",
                                icon: "error",
								text : "Favor intentar nuevamente.",
                               draggable: true,
                             }); 
						}
					}
				});
				
			});
		});
	</script>  
	<script type="text/javascript">
		$(document).ready(function(){ 
			$('#tipoSelect').on('change', function() {
			 
			var idTipoMovimiento = $(this).find(":selected").val()	  
             
			 $("#formapagoSelect").val('0'); 
             $("#nodocumento").val('');
			 $("#diapago").val('0');  
			 $("#fechapago").val('0001-01-01');   
			 $("#subtipoSelect").empty();
	          $.ajax({
				type:"POST",
				data:"filtro=" + idTipoMovimiento,
				url:"../procesos/movimientos/filtrarSubTipoMovimiento.php",
				success:function(r){    
					dato=jQuery.parseJSON(r);  
					const obj = JSON.parse(dato);
					if (obj != null)
			     	{
                        obj.forEach((option) => {
                        const optionElement = document.createElement("option"); 
                        const selectElement = document.getElementById("subtipoSelect");
 
                         if (typeof option === "string") {
                             optionElement.value = option.id_sub_tipo_movimiento;
                             optionElement.text = option.sub_tipo;
                        } else {
                              optionElement.value = option.id_sub_tipo_movimiento;
                              optionElement.text = option.sub_tipo;
                         } 
                        selectElement.appendChild(optionElement);
                    }); 
					// INICIALIZAR SELECT2 AL RECARGAR SUBTIPO
					$('#subtipoSelect').select2({
						width: '100%',
						dropdownParent: $('#abremodalUpdateAgregarMovimeinto')
					});
					// Seleccionar automáticamente CARGOS si existe
					var $subtipo = $('#subtipoSelect');
					var encontrado = false;
					$subtipo.find('option').each(function(){
						if($(this).text().trim().toUpperCase() === 'CARGOS' || $(this).text().trim().toUpperCase() === 'DESCARGO' ) {
							$subtipo.val($(this).val()).trigger('change');
							encontrado = true;
						}
					});
				  }
				  else{
					Swal.fire({
                               title: "No se encontraron resultados con el idTipoMovimiento: " + idTipoMovimiento,
                                icon: "warning",
                               draggable: true,
                             }); 
				  } 
				} 
			});
			});
		});
	</script> 
	<script type="text/javascript">
		$(document).ready(function(){ 
			$('#subtipoSelect').on('change', function() {
			    var idSubTipoMovimiento = $(this).find(":selected").val(); 
                $("#formapagoSelect").val('0'); 
                $("#nodocumento").val('');
				$("#diapago").val('0');  
				$("#fechapago").val('0001-01-01');   
			});
		});
	</script> 
	<script type="text/javascript">
		$(document).ready(function(){ 
	 function actualizarDiasPago() {
        var fechaDocumento = $('#fechadocumento').val();
        var fechaPago = $('#fechapago').val();
        if (fechaDocumento && fechaPago) {
            var fecha1 = new Date(fechaDocumento);
            var fecha2 = new Date(fechaPago);
            var today = new Date(getTodayStr());
            fecha1.setHours(0,0,0,0);
            fecha2.setHours(0,0,0,0);
            if (fecha2 < today) {
                Swal.fire({
                    title: "La fecha de pago no puede ser menor que hoy",
                    icon: "error",
                });
                $('#fechapago').val(getTodayStr());
                fecha2 = today;
            }
            var diffDays = daysBetween(fechaDocumento, $('#fechapago').val());
            $('#diapago').val(diffDays);
        }
    }
			$('#formapagoSelect').on('change', function() {
			    var idformaPago = $(this).find(":selected").val(); 
                if ( idformaPago == "2" || idformaPago == "0" )
				{
                    $("#diapago").val('0');    
				} 
				else{
			        actualizarDiasPago();
				}
			});
		});
	</script> 
	<script type="text/javascript">
		$(document).ready(function(){ 

	 function actualizarDiasPagoU() {
        var fechaDocumento = $('#fechadocumentoU').val();
        var fechaPago = $('#fechapagoU').val();
        if (fechaDocumento && fechaPago) {
            var fecha1 = new Date(fechaDocumento);
            var fecha2 = new Date(fechaPago);
            var today = new Date(getTodayStr());
            fecha1.setHours(0,0,0,0);
            fecha2.setHours(0,0,0,0);
            if (fecha2 < today) {
                Swal.fire({
                    title: "La fecha de pago no puede ser menor que hoy",
                    icon: "error",
                });
                $('#fechapagoU').val(getTodayStr());
                fecha2 = today;
            }
            var diffDays = daysBetween(fechaDocumento, $('#fechapagoU').val());
            $('#diapagoU').val(diffDays);
        }
    }

			$('#formapagoSelectU').on('change', function() {
			    var idformaPago = $(this).find(":selected").val(); 
                if ( idformaPago == "2" || idformaPago == "0" )
				{ 
					$("#diapagoU").val('0');  
				} 
				else{
			    	  actualizarDiasPagoU();
				}
			});
		});
	</script> 
	<script type="text/javascript">
		$(document).ready(function(){ 
			$('#subtipoSelectU').on('change', function() {
			    var idSubTipoMovimiento = $(this).find(":selected").val(); 
                ////$("#formapagoSelectU").val('0'); 
                $("#nodocumentoU").val('');
				$("#diapagoU").val('0');  
				$("#fechapagoU").val('0001-01-01');   
			});
		});
	</script>  
	</script> 
	<script type="text/javascript">
		$(document).ready(function(){ 
			$('#tipoSelectU').on('change', function() {
				// Evitar recarga de subtipos si estamos editando
				if (window.editandoMovimiento) return;
				var idTipoMovimiento = $(this).find(":selected").val();
				$("#nodocumentoU").val('');
				$("#diapagoU").val('0');  
				$("#fechapagoU").val('0001-01-01');    
				$("#subtipoSelectU").empty();
				$.ajax({
					type:"POST",
					data:"filtro=" + idTipoMovimiento,
					url:"../procesos/movimientos/filtrarSubTipoMovimiento.php",
					success:function(r){    
						var dato = jQuery.parseJSON(r);  
						const obj = JSON.parse(dato);
						if (obj != null) {
							obj.forEach((option) => {
								const optionElement = document.createElement("option"); 
								optionElement.value = option.id_sub_tipo_movimiento;
								optionElement.text = option.sub_tipo;
								document.getElementById("subtipoSelectU").appendChild(optionElement);
							}); 
							// INICIALIZAR SELECT2 AL RECARGAR SUBTIPO U
							$('#subtipoSelectU').select2({
								width: '100%',
								dropdownParent: $('#abremodalUpdateMovimiento')
							});
						} else {
							Swal.fire({
								title: "No se encontraron resultados con el idTipoMovimiento: " + idTipoMovimiento,
								icon: "warning",
								draggable: true,
							}); 
						} 
					} 
				});
			});
		});
	</script>

	<!-- INICIALIZADOR SELECT2 en todos los modales -->
	<script type="text/javascript">
	$(document).ready(function(){
	    // Modal NUEVO
	    $('#tipoSelect, #subtipoSelect, #bodegaSelect, #formapagoSelect, #proveedorSelect').select2({
	        width: '100%',
	        dropdownParent: $('#abremodalUpdateAgregarMovimeinto')
	    });
	    // Modal EDITAR
	    $('#tipoSelectU, #subtipoSelectU, #bodegaSelectU, #formapagoSelectU, #proveedorSelectU').select2({
	        width: '100%',
	        dropdownParent: $('#abremodalUpdateMovimiento')
	    });
	    // Modal DETALLE/VISTA
	    $('#tipoSelectD, #subtipoSelectD, #bodegaSelectD, #formapagoSelectD, #proveedorSelectD').select2({
	        width: '100%',
	        dropdownParent: $('#abremodalDetalledateMovimiento')
	    });
	});
	// FUNCIONES DE RESET Y CARGA CORRECTA DE SELECTS (al final de tu archivo JS o antes del </body>)
function resetFormNuevoMovimiento() {
    $('#frmMovimientos')[0].reset();
    // IDs de tus selects
    let selects = ['#tipoSelect', '#subtipoSelect', '#bodegaSelect', '#formapagoSelect', '#proveedorSelect'];
    selects.forEach(function(sel) {
        const $sel = $(sel);
        if ($sel.find('option').length > 0) {
            $sel.val($sel.find('option').first().val()).trigger('change');
        }
    });
    // Recargar subtipo según primer tipo seleccionado (por AJAX)
    var firstTipo = $('#tipoSelect').val();
    cargarSubtipoNuevo(firstTipo);
    setDateValue();
    $("#diapago").val('0');
    $("#observacion").val('');
}
function cargarSubtipoNuevo(idTipoMovimiento) {
    $("#subtipoSelect").empty();
    $.ajax({
        type: "POST",
        data: "filtro=" + idTipoMovimiento,
        url: "../procesos/movimientos/filtrarSubTipoMovimiento.php",
        success: function(r) {
            let dato = jQuery.parseJSON(r);
            const obj = JSON.parse(dato);
            if (obj != null && obj.length > 0) {
                obj.forEach(option => {
                    const optionElement = document.createElement("option");
                    optionElement.value = option.id_sub_tipo_movimiento;
                    optionElement.text = option.sub_tipo;
                    document.getElementById("subtipoSelect").appendChild(optionElement);
                });
                $('#subtipoSelect').val($('#subtipoSelect option:first').val()).trigger('change');
            }
            $('#subtipoSelect').select2({
                width: '100%',
                dropdownParent: $('#abremodalUpdateAgregarMovimeinto')
            });
        }
    });
}
$('#abremodalUpdateAgregarMovimeinto').on('show.bs.modal', function () {
    resetFormNuevoMovimiento();
});
</script>
<script type="text/javascript">
// --- FUNCIONES PARA RESETEAR EL FORMULARIO DE NUEVO MOVIMIENTO ---

// Resetea todos los campos y selects del formulario de nuevo movimiento
function resetFormNuevoMovimiento() {
    $('#frmMovimientos')[0].reset();

    // Setea todos los selects al primer valor disponible
    let selects = ['#tipoSelect', '#bodegaSelect', '#formapagoSelect', '#proveedorSelect'];
    selects.forEach(function(sel) {
        const $sel = $(sel);
        if ($sel.find('option').length > 0) {
            $sel.val($sel.find('option').first().val()).trigger('change');
        } else {
            $sel.val('').trigger('change');
        }
    });

    // Recarga subtipo según primer tipo seleccionado (por AJAX)
    var firstTipo = $('#tipoSelect').val();
	///$("#subtipoSelect").empty();
    ///cargarSubtipoNuevo(firstTipo);

    // Setea fechas y campos adicionales a sus valores por defecto
    setDateValue();
    $('#diapago').val('0');
    $('#fechapago').val('0001-01-01');
    $('#nodocumento').val('');
    $('#observacion').val('');

    // Refresca select2 para que se vea actualizado
    $('#tipoSelect, #subtipoSelect, #bodegaSelect, #formapagoSelect, #proveedorSelect').select2({
        width: '100%',
        dropdownParent: $('#abremodalUpdateAgregarMovimeinto')
    });
}

// Recarga los subtipos por AJAX según el tipo seleccionado
function cargarSubtipoNuevo(idTipoMovimiento) {
    $("#subtipoSelect").empty();
    $.ajax({
        type: "POST",
        data: "filtro=" + idTipoMovimiento,
        url: "../procesos/movimientos/filtrarSubTipoMovimiento.php",
        success: function(r) {
            let dato = jQuery.parseJSON(r);
            const obj = JSON.parse(dato);
            if (obj != null && obj.length > 0) {
                obj.forEach(option => {
                    const optionElement = document.createElement("option");
                    optionElement.value = option.id_sub_tipo_movimiento;
                    optionElement.text = option.sub_tipo;
                    document.getElementById("subtipoSelect").appendChild(optionElement);
                });
                $('#subtipoSelect').val($('#subtipoSelect option:first').val()).trigger('change');
            }
            $('#subtipoSelect').select2({
                width: '100%',
                dropdownParent: $('#abremodalUpdateAgregarMovimeinto')
            });
        }
    });
}

// Llama al reset cada vez que se abre el modal de nuevo movimiento
$('#abremodalUpdateAgregarMovimeinto').on('show.bs.modal', function () {
	resetFormNuevoMovimiento();
	// Seleccionar la bodega activa de la sesión después de resetear el formulario
	var bodegaActivaSesion = null;
	<?php if (isset($_SESSION['bodega_seleccionada'])): ?>
		bodegaActivaSesion = '<?php echo $_SESSION['bodega_seleccionada']; ?>';
	<?php endif; ?>
	setTimeout(function() {
		var $bodega = $('#bodegaSelect');
		if (bodegaActivaSesion && $bodega.find('option[value="'+bodegaActivaSesion+'"]').length > 0) {
			$bodega.val(bodegaActivaSesion).trigger('change');
		}
	}, 200);
});
</script>
<?php 
}else{
	header("location:../index.php");
}

?>