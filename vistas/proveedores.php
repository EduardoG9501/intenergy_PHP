<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "PROVEEDORES");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
	<!DOCTYPE html>
	<html>
	<head>
		<title>REGISTROS GENERAL -> PROVEEDORES</title>
		<?php require_once "menu.php"; ?>  
		<?php   $c= new conectar();
		$conexion=$c->conexion(); 
		?>
	</head> 
	<body>
		<div class="container">
			<h1>Proveedores</h1>
			<div class="row">
				<div class="col-sm-4">
					<form id="frmProveedores">
						<label>Tipo Identificacion</label> 
                        <select class="form-control input-sm" id="tipoIdentificacionSelect" name="tipoIdentificacionSelect">
							  <option value="A">Selecciona Tipo Identificacion</option>
							    <?php 
								        $sql="SELECT id_tipo_identificacion,tipo
		                                from tipoidentificacion
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; 
								?>
						</select>  
						<label>Nro.Identificacion</label>
						<input type="text" class="form-control input-sm" id="noidentificacion" name="noidentificacion">
						<label>Razòn Social</label>
						<input type="text" class="form-control input-sm" id="razonsocial" name="razonsocial">
						<label>Direcciòn</label>
						<input type="text" class="form-control input-sm" id="direccion" name="direccion">
						<label>Tèlefono</label>
						<input type="text" class="form-control input-sm" id="telefono" name="telefono">
						<label>Correo</label>
						<input type="text" class="form-control input-sm" id="correo" name="correo">
						<label>Nombre Comercial</label>
						<input type="text" class="form-control input-sm" id="nombrecomercial" name="nombrecomercial">
						<label>Tipo Contribuyente</label>
						 <select class="form-control input-sm" id="tipoIdTipoContribuyenteSelect" name="tipoIdTipoContribuyenteSelect">
							  <option value="0">Selecciona Tipo Contribuyente</option>
							    <?php 
								        $sql="SELECT id_tipo_contribuyente,tipo
		                                from tipocontribuyente
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						</select>  
						<label>Agente Retenciòn</label>
						<input type="text" class="form-control input-sm" id="agenteretencion" name="agenteretencion">
						<p></p>
						<span class="btn btn-primary" id="btnAgregarProveedor">Agregar</span>
					</form>
				</div>
				<div class="col-sm-8">
					<div id="tablaProveedoresLoad"></div>
				</div>
			</div>
		</div>

		<!-- Button trigger modal -->


		<!-- Modal -->
		<div class="modal fade" id="abremodalProveedoresUpdate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog smodal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Actualizar proveedor</h4>
					</div>
					<div class="modal-body">
						<form id="frmProveedoresU">  
						 <input type="text" hidden="" id="idproveedorU" name="idproveedorU"> 
						<div class="row">
					    <div class="col-sm-6">
						<label>Tipo Identificacion</label> 
                        <select class="form-control input-sm" id="tipoIdentificacionSelectU" name="tipoIdentificacionSelectU">
							  <option value="A">Selecciona Tipo Identificacion</option>
							    <?php 
								        $sql="SELECT id_tipo_identificacion,tipo
		                                from tipoidentificacion
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; 
								?>
						</select>  
						<label>Nro.Identificacion</label>
						<input type="text" class="form-control input-sm" id="noidentificacionU" name="noidentificacionU">
						<label>Razòn Social</label>
						<input type="text" class="form-control input-sm" id="razonsocialU" name="razonsocialU">
						<label>Direcciòn</label>
						<input type="text" class="form-control input-sm" id="direccionU" name="direccionU">
						<label>Tèlefono</label>
						<input type="text" class="form-control input-sm" id="telefonoU" name="telefonoU">
						</div>
						<div class="col-sm-6">
						<label>Correo</label>
						<input type="text" class="form-control input-sm" id="correoU" name="correoU">
						<label>Nombre Comercial</label>
						<input type="text" class="form-control input-sm" id="nombrecomercialU" name="nombrecomercialU">
						<label>Tipo Contribuyente</label>
						 <select class="form-control input-sm" id="tipoIdTipoContribuyenteSelectU" name="tipoIdTipoContribuyenteSelectU">
							  <option value="0">Selecciona Tipo Contribuyente</option>
							    <?php 
								        $sql="SELECT id_tipo_contribuyente,tipo
		                                from tipocontribuyente
										where estado = 1";
		                                $result=mysqli_query($conexion,$sql);
								        while($ver=mysqli_fetch_row($result)): ?>
								       <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							    <?php endwhile; ?>
						</select>  
						<label>Agente Retenciòn</label>
						<input type="text" class="form-control input-sm" id="agenteretencionU" name="agenteretencionU">
					    </div>
					</form>
					</div>
					<p></p>
					<p></p>  
					<div class="modal-footer">
						<div style="text-align: center;"> 
                        <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
					   	<button id="btnActualizarProveedorU" type="button" class="btn btn-success" data-dismiss="modal">Actualizar</button>
					   </div>
					</div> 
				</div>
			</div>
		</div> 
	</body>
	</html>

	<script type="text/javascript">
		function agregaDatosProveedor(idproveedor){ 
			$.ajax({
				type:"POST",
				data:"idproveedor=" + idproveedor,
				url:"../procesos/proveedores/obtenDatosProveedor.php",
				success:function(r){  
					dato=jQuery.parseJSON(r);
					$('#idproveedorU').val(dato['id_proveedor']); 
					$('#tipoIdentificacionSelectU').val(dato['tipo_identificacion']);  
					$('#noidentificacionU').val(dato['numero_identificacion']);
					$('#razonsocialU').val(dato['razon_social']);
					$('#direccionU').val(dato['direccion']);
					$('#telefonoU').val(dato['telefono']);
					$('#correoU').val(dato['email']);
					$('#nombrecomercialU').val(dato['nombre_comercial']);
					$('#tipoIdTipoContribuyenteSelectU').val(dato['tipo_contribuyente']);
					$('#agenteretencionU').val(dato['agente_retencion']);

				}
			});
		}

function eliminarProveedor(idproveedor){ 
swal.fire({
       title: "¿Estás seguro que deseas eliminar este proveedor?",
      text: "Se eliminara este proveedor de la base de datos en caso que presiones Sí, eliminar.",
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
					data:"idproveedor=" + idproveedor,
					url:"../procesos/proveedores/eliminarProveedor.php",
					success:function(r){ 
						if(r==1){
							$('#tablaProveedoresLoad').load("proveedores/tablaProveedores.php"); 
							Swal.fire({
                               title: "Proveedor eliminado con exito!!",
                                icon: "success",
                               draggable: true,
                             }); 
						}else{
							Swal.fire({
                               title: "No se pudo eliminar el proveedor!!",
                                icon: "error",
                               draggable: true,
                             }); 
						}
					}
				});
        }
    }); 	
}
function activarProveedor(idproveedor){ 
swal.fire({
       title: "¿Estás seguro que deseas activar este proveedor?",
      text: "Se activara este proveedor de la base de datos en caso que presiones Sí, activar.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#10671e",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, activar!",
      cancelButtonText: "Cancelar"
    }).then(function(result) { 
        if (result.value) { 
			$.ajax({
					type:"POST",
					data:"idproveedor=" + idproveedor,
					url:"../procesos/proveedores/activarProveedor.php",
					success:function(r){ 
						if(r==1){
							$('#tablaProveedoresLoad').load("proveedores/tablaProveedores.php"); 
							Swal.fire({
                               title: "Proveedor activado con exito!!",
                                icon: "success",
                               draggable: true,
                             }); 
						}else{
							Swal.fire({
                               title: "No se pudo activado el proveedor!!",
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

			$('#tablaProveedoresLoad').load("proveedores/tablaProveedores.php");

			$('#btnAgregarProveedor').click(function(){

				vacios=validarFormVacio('frmProveedores');

				if(vacios > 0){ 
					Swal.fire({
                               title: "Debes llenar todos los campos!!",
                                icon: "warning",
                               draggable: true,
                             }); 
					return false;
				}

				datos=$('#frmProveedores').serialize();

				$.ajax({
					type:"POST",
					data:datos,
					url:"../procesos/proveedores/agregaProveedor.php",
					success:function(r){
						if(r==1){
							$('#frmProveedores')[0].reset();
							$('#tablaProveedoresLoad').load("proveedores/tablaProveedores.php"); 
							 Swal.fire({
                               title: "Proveedor agregado con exito",
                                icon: "success",
                               draggable: true,
                             });
						}else if(r=="4"){
                             Swal.fire({title: "Ya existe un proveedor con ese Razón Social y Nombre Comercial", icon: "warning"});
                        }else if(r=="2"){
                             Swal.fire({title: "Ya existe un proveedor con esa Razón Social", icon: "warning"});
                        }else if(r=="3"){
                             Swal.fire({title: "Ya existe un proveedor con ese Nombre Comercial", icon: "warning"});
                        }else{
							 Swal.fire({
                               title: "No se pudo agregar el proveedor",
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
			$('#btnActualizarProveedorU').click(function(){
				datos=$('#frmProveedoresU').serialize();

				$.ajax({
					type:"POST",
					data:datos,
					url:"../procesos/proveedores/actualizaProveedor.php",
					success:function(r){  
						if(r==1){
							$('#frmProveedoresU')[0].reset();
							$('#tablaProveedoresLoad').load("proveedores/tablaProveedores.php"); 
							Swal.fire({
                               title: "Proveedor actualizado con exito",
                                icon: "success",
                               draggable: true,
                             }); 
					}else if(r=="4"){
                         Swal.fire({title: "Ya existe un proveedor con ese Razón Social y Nombre Comercial", icon: "warning"});
                    }else if(r=="2"){
                             Swal.fire({title: "Ya existe un proveedor con esa Razón Social", icon: "warning"});
                     }else if(r=="3"){
                           Swal.fire({title: "Ya existe un proveedor con ese Nombre Comercial", icon: "warning"});
                      }else{
							Swal.fire({
                               title: "No se pudo actualizar el proveedor",
                                icon: "error",
                               draggable: true,
                             }); 
						}
					}
				});
			})
		})
	</script>


	<?php 
}else{
	header("location:../index.php");
}
?>