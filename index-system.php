<?php 
	
	require_once "clases/Conexion.php";
	$obj= new conectar();
	$conexion=$obj->conexion();

	$sql="SELECT * from usuarios where email='admin'";
	$result=mysqli_query($conexion,$sql);
	$validar=0;
	if(mysqli_num_rows($result) > 0){
		$validar=1;
	} 
	$sql="SELECT id_bodega,nombreBodega
	from bodegas";
	$resultBodega=mysqli_query($conexion,$sql);
 ?>


<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1">
	<title>Login de usuario</title>
	<link rel="stylesheet" type="text/css" href="librerias/bootstrap/css/bootstrap.css">
	<script src="librerias/jquery-3.2.1.min.js"></script>
	<script src="js/funciones.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background: #04101a;background: linear-gradient(90deg,rgba(4, 16, 26, 1) 0%, rgba(4, 39, 46, 1) 50%, rgba(10, 51, 89, 1) 100%);">
	<br><br><br>
	<div class="container">
		<div class="row">
			<div class="col-sm-4"></div>
			<div class="col-sm-4">
				<div class="panel panel-primary">
					<div class="panel panel-heading">Sistema de Gestiòn de Inventario (SGI)</div> 
					<div style=" text-align: center;">
                          <img  class="img-responsive logo img-thumbnail" style="width:80%;padding: 15px;"  src="img/InteNergy.jpg">
				     </div> 
					<div class="panel panel-body">  
						<form id="frmLogin">
							<label style="color: #064171;">Usuario</label>
							<input type="text" class="form-control input-sm" name="usuario" id="usuario">
							<label style="color: #064171;">Contraseña</label>
							<input type="password" name="password" id="password" class="form-control input-sm">
							<label style="color: #064171;">Bodega</label>
						    <select class="form-control input-sm" id="categoriaSelect" name="categoriaSelect">
							   <option value="A">Selecciona Bodega</option>
							  <?php while($ver=mysqli_fetch_row($resultBodega)): ?>
								<option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
							<?php endwhile; ?>
						</select>
							<p></p>
							<span style="width: 100%;" class="btn btn-primary btn-sm" id="entrarSistema">Ingresar</span>
							<?php  if(!$validar): ?>
							<a href="registro.php" class="btn btn-danger btn-sm">Registrar</a>
							<?php endif; ?>
						</form>
					</div>  
				</div>
			</div>
			<div class="col-sm-4"></div>
		</div>
	</div>
</body>
</html>

<script type="text/javascript">
	$(document).ready(function(){
		$('#entrarSistema').click(function(){

		vacios=validarFormVacio('frmLogin');

			if(vacios > 0){ 
				Swal.fire({
                       title: "Campos requeridos",
                        icon: "error",
                        draggable: true,
                       text: "Debes llenar todos los campos!!"
                      });
				return false;
			}

		datos=$('#frmLogin').serialize();
		$.ajax({
			type:"POST",
			data:datos,
			url:"procesos/regLogin/login.php",
			success:function(r){

				if(r==1){
					window.location="vistas/inicio.php";
				}else{
					 Swal.fire({
                       title: "Sin permiso",
                        icon: "error",
                        draggable: true,
                       text: "Usuario o contraseña ingresados son invalidos."
                      });
				}
			}
		});
	});
	});
	
	// Get the input field
var inputUsuario = document.getElementById("usuario");
 
// Execute a function when the user presses a key on the keyboard
inputUsuario.addEventListener("keypress", function(event) {
  // If the user presses the "Enter" key on the keyboard
  if (event.key === "Enter") {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("entrarSistema").click();
  }
  });

var inputPassword = document.getElementById("password");
  // Execute a function when the user presses a key on the keyboard
inputPassword.addEventListener("keypress", function(event) {
  // If the user presses the "Enter" key on the keyboard
  if (event.key === "Enter") {
    // Cancel the default action, if needed
    event.preventDefault();
    // Trigger the button element with a click
    document.getElementById("entrarSistema").click();
  }
});
</script> 