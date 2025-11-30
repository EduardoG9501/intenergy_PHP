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
    from bodegas where estado = 1";
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
                    <div style ="text-align: center;font-size: x-large;color: #064171 !important;" class="panel panel-heading">Inicia Sesión</div> 
                    <!---<div style=" text-align: center;">
                          <img  class="img-responsive logo img-thumbnail" style="width:80%;padding: 15px;"  src="img/InteNergy.jpg">
                     </div> --->
                    <div class="panel panel-body">  
                        <form id="frmLogin">
                            <label style="color: #064171;">Usuario</label>
                            <input style="font-weight: bold;color: #3353de;    font-size: medium;" type="text" class="form-control input-sm" name="usuario" id="usuario">
                            <!-- Checkbox recordar usuario -->
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="recordarUsuario"> Recordar usuario
                                </label>
                            </div>
                            <label style="color: #064171;">Contraseña</label>
                            <input style="font-weight: bold;color: #3353de;    font-size: medium;"type="password" name="password" id="password" class="form-control input-sm">
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
    // Recordar usuario: Al cargar la página, si hay usuario guardado, lo pone y marca el checkbox
    $(document).ready(function(){
        if(localStorage.getItem('usuario_guardado')) {
            $('#usuario').val(localStorage.getItem('usuario_guardado'));
            $('#recordarUsuario').prop('checked', true);
        }

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

            // Aquí se maneja el almacenamiento según el checkbox
            if($('#recordarUsuario').is(':checked')) {
                localStorage.setItem('usuario_guardado', $('#usuario').val());
            } else {
                localStorage.removeItem('usuario_guardado');
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

        // Si el usuario desmarca el checkbox en cualquier momento, se elimina el usuario guardado
        $('#recordarUsuario').change(function(){
            if(!$(this).is(':checked')) {
                localStorage.removeItem('usuario_guardado');
            }
        });
    });

    // Get the input field
    var inputUsuario = document.getElementById("usuario");
    // Execute a function when the user presses a key on the keyboard
    inputUsuario.addEventListener("keypress", function(event) {
      // If the user presses the "Enter" key on the keyboard
      if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById("entrarSistema").click();
      }
    });

    var inputPassword = document.getElementById("password");
    inputPassword.addEventListener("keypress", function(event) {
      if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById("entrarSistema").click();
      }
    });
</script> 
<style>
.panel { 
   border-radius: 13px !important; 
}
.panel-primary > .panel-heading 
 {
    color: #000000  !important; 
    background-color: #eaeaea  !important; 
    border-color: #ffffff !important;
} 
.btn-sm, .btn-group-sm > .btn { 
    border-radius: 6px !important;
}
</style>