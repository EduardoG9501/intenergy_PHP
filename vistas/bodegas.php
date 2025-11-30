<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 

/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "BODEGAS");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
 ?> 
	<!DOCTYPE html>
	<html>
	<head>
		<title>REGISTROS GENERAL -> BODEGAS</title>
		<?php require_once "menu.php"; ?>
		<!-- Select2 CSS SOLO para los select filtrables -->
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	</head>
	<body> 
		<div class="container">
			<h1>Bodegas</h1>
			<div class="row">
				<div class="col-sm-3">
					<form id="frmBodegas">
						<label>Bodegas</label>
						<input type="text" class="form-control input-sm" name="bodega" id="bodega">

						<!-- Checkbox para principal y select para bodega padre -->
						<div class="form-check mt-2">
							<input class="form-check-input" type="checkbox" value="1" id="esPrincipal" name="esPrincipal" checked>
							<label class="form-check-label" for="esPrincipal">¿Es bodega principal?</label>
						</div>
						<div class="mt-2" id="divBodegaPadre" style="display:none;">
							<label for="bodegaPadre">Bodega principal:</label>
							<select class="form-control input-sm select2" name="bodegaPadre" id="bodegaPadre">
								<option value="">Seleccione...</option>
								<?php
								$c = new conectar();
								$conexion = $c->conexion();
								// Solo bodegas principales: es_bodega_secundaria = 0 y estado = 1
								$sql = "SELECT id_bodega, nombreBodega FROM bodegas WHERE es_bodega_secundaria = 0 AND estado = 1";
								$result = mysqli_query($conexion, $sql);
								while($row = mysqli_fetch_assoc($result)){
									echo "<option value='".$row['id_bodega']."'>".$row['nombreBodega']."</option>";
								}
								?>
							</select>
						</div>
						<!-- FIN NUEVO -->

						<p></p>
						<span class="btn btn-primary" id="btnAgregaBodega">Agregar</span>
					</form>
				</div>
				<div class="col-sm-9">
					<div id="tablaBodegaLoad"></div>
				</div>
			</div>
		</div> 
		<!-- Button trigger modal --> 
		<!-- Modal -->
		<div class="modal fade" id="actualizaBodega" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog smodal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Actualiza bodega</h4>
					</div>
					<div class="modal-body">
						<form id="frmBodegaU">
							<input type="text" hidden="" id="idbodega" name="idbodega">  
							<label>Bodega</label>
							<input type="text" id="bodegaU" name="bodegaU" class="form-control input-sm">

							<!-- Checkbox para principal y select para bodega padre (edición) -->
							<div class="form-check mt-2">
								<input class="form-check-input" type="checkbox" value="1" id="esPrincipalU" name="esPrincipalU">
								<label class="form-check-label" for="esPrincipalU">¿Es bodega principal?</label>
							</div>
							<div class="mt-2" id="divBodegaPadreU" style="display:none;">
								<label for="bodegaPadreU">Bodega principal:</label>
								<select class="form-control input-sm select2" name="bodegaPadreU" id="bodegaPadreU">
									<option value="">Seleccione...</option>
									<?php
									$sql = "SELECT id_bodega, nombreBodega FROM bodegas WHERE es_bodega_secundaria = 0 AND estado = 1";
									$result = mysqli_query($conexion, $sql);
									while($row = mysqli_fetch_assoc($result)){
										echo "<option value='".$row['id_bodega']."'>".$row['nombreBodega']."</option>";
									}
									?>
								</select>
							</div>
							<!-- FIN NUEVO -->

						</form> 
					</div>
					<div class="modal-footer">
					  <div style="text-align: center;"> 
						 <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
						<button type="button" id="btnActualizaBodega" class="btn btn-success" data-dismiss="modal">Guardar</button>
					  </div> 
					</div>
				</div>
			</div>
		</div>

		<!-- Select2 JS SOLO para los select filtrables -->
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	</body>
	</html>
	<script type="text/javascript">
		$(document).ready(function(){ 
			// Inicializar select2 SOLO para los select que tienen clase select2 
			 $('.select2').select2({
                 width: '100%',
                placeholder: "Seleccione...",
                allowClear: true,
                dropdownParent: $('#divBodegaPadre').length ? $('#divBodegaPadre') : $(document.body)
             });

			$('#tablaBodegaLoad').load("bodegas/tablaBodegas.php"); 
			$('#btnAgregaBodega').click(function(){ 
				// VALIDACIÓN PERSONALIZADA: solo pide bodegaPadre si NO es principal
				let vacio = false;
				if ($('#bodega').val().trim() === "") vacio = true;
				if (!$('#esPrincipal').is(':checked')) {
					if ($('#bodegaPadre').val() === "" || $('#bodegaPadre').val() === null) vacio = true;
				}
				if (vacio) {
					Swal.fire({
					   title: "Campos requeridos",
						icon: "warning",
						draggable: true,
					   text: "Debes llenar todos los campos!!"
					}); 
					return false;
				}
				datos=$('#frmBodegas').serialize();
				$.ajax({
					type:"POST",
					data:datos,
					url:"../procesos/bodegas/agregaBodega.php",
					success:function(r){
						if(r==1){
							$('#frmBodegas')[0].reset();
							$('#bodegaPadre').val('').trigger('change'); // Limpia select2
							$('#tablaBodegaLoad').load("bodegas/tablaBodegas.php"); 
							Swal.fire({
								title: "Bodega agregada con exito",
								icon: "success",
								draggable: true,
							});
						}else if(r=="EXISTE"){
							Swal.fire({
								title: "Nombre duplicado",
								icon: "warning",
								text: "Ya existe una bodega con ese nombre.",
								draggable: true,
							});
						}else{
							Swal.fire({
								title: "Agregar bodega",
								icon: "error",
								draggable: true,
								text: "No se pudo agregar bodega"
							}); 
						}
					}
				});
			});

			// Mostrar/ocultar el select de bodega padre (alta)
			$('#esPrincipal').on('change', function() {
				if(this.checked) {
					$('#divBodegaPadre').hide();
					$('#bodegaPadre').val('').trigger('change'); // Limpia select2
				} else {
					$('#divBodegaPadre').show();
				}
			});
			if($('#esPrincipal').is(':checked')) {
				$('#divBodegaPadre').hide();
			} else {
				$('#divBodegaPadre').show();
			}
		});
	</script>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#btnActualizaBodega').click(function(){
  
			    datos=$('#frmBodegaU').serialize();
				$.ajax({
					type:"POST",
					data:datos,
					url:"../procesos/bodegas/actualizaBodega.php",
					success:function(r){ 
						if(r==1){
							$('#tablaBodegaLoad').load("bodegas/tablaBodegas.php"); 
							Swal.fire({
								title: "Bodega actualizada con exito",
								icon: "success",
								draggable: true,
							});
						}else if(r=="EXISTE"){
							Swal.fire({
								title: "Nombre duplicado",
								icon: "warning",
								text: "Ya existe una bodega con ese nombre.",
								draggable: true,
							});
						}else{
							Swal.fire({
								title: "Actualizar bodega",
								icon: "error",
								draggable: true,
								text: "No se pudo actaulizar la bodega"
							});  
						}
					}
				});
			});

			// Mostrar/ocultar el select de bodega padre (edición)
			$('#esPrincipalU').on('change', function() {
				if(this.checked) {
					$('#divBodegaPadreU').hide();
					$('#bodegaPadreU').val('').trigger('change'); // Limpia select2
				} else {
					$('#divBodegaPadreU').show();
				}
			});
		});
	</script>

	<script type="text/javascript"> 
        // Ampliar agregaDato para marcar check y select correctamente en edición
        // Recibe: idbodega, bodega, esPrincipal (1/0), idPadre
  function agregaDato(idbodega, bodega, esPrincipal, idPadre){
    $('#idbodega').val(idbodega);
    $('#bodegaU').val(bodega);

    $('#esPrincipalU').prop('checked', esPrincipal == 0);

    if(esPrincipal == 0){
        $('#divBodegaPadreU').hide();
        $('#bodegaPadreU').val('').trigger('change');
    } else {
        $('#divBodegaPadreU').show();
        // Destruye select2 si ya estaba inicializado
        if ($('#bodegaPadreU').hasClass("select2-hidden-accessible")) {
            $('#bodegaPadreU').select2('destroy');
        }
        // Inicializa select2 SOLO cuando está visible
        $('#bodegaPadreU').select2({
            width: '100%',
            placeholder: "Seleccione...",
            allowClear: true,
            dropdownParent: $('#actualizaBodega') // ¡Esto es importante para modales!
        });
        $('#bodegaPadreU').val(idPadre ? idPadre : '').trigger('change');
    }
}

function eliminaBodega(idbodega){
swal.fire({
       title: "¿Estás seguro que deseas eliminar esta bodega?",
      text: "Se eliminara esta bodega de la base de datos en caso que presiones Sí, eliminar.",
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
					data:"idbodega=" + idbodega,
					url:"../procesos/bodegas/eliminarBodega.php",
					success:function(r){
						if(r==1){
							$('#tablaBodegaLoad').load("bodegas/tablaBodegas.php"); 
							Swal.fire({
                               title: "Bodega eliminada con exito!!",
                                icon: "success",
                               draggable: true,
                             });
						}else{
							Swal.fire({
                             title: "Eliminar bodega",
                             icon: "error",
                             draggable: true,
                             text: "No se pudo eliminar la bodega"
                            });   
						}
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