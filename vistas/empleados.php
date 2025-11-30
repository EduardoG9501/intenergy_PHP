<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "EMPLEADOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?> 
<!DOCTYPE html>
<html>
<head>
    <title>REGISTROS GENERAL -> EMPLEADOS</title>
    <?php require_once "menu.php"; ?>  
    <?php $c= new conectar();
    $conexion=$c->conexion(); 
    ?>
</head> 
<body>
    <div class="container">
        <h1>Empleados</h1>
        <div class="row">
            <div class="col-sm-4">
                <form id="frmEmpleados">
                    <label>CEDULA</label>
                    <input type="text" class="form-control input-sm" id="cedula" name="cedula">
                    <label>NOMBRES Y APELLIDOS</label>
                    <input type="text" class="form-control input-sm" id="nombre" name="nombre">
                    <label>GÈNERO</label> 
                    <select class="form-control input-sm" id="generoSelect" name="generoSelect">
                          <option value="A">Selecciona Genero</option>
                            <?php 
                                    $sql="SELECT id_genero,genero
                                    from genero
                                    where estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; 
                            ?>
                    </select>  
                    <label>NACIMIENTO</label>
                    <input type="date" class="form-control input-sm" id="nacimiento" name="nacimiento">
                    <label>CARGO/PUESTO</label>
                    <select class="form-control input-sm" id="cargoSelect" name="cargoSelect">
                          <option value="A">Selecciona Genero</option>
                            <?php 
                                    $sql="SELECT id_cargo,cargo
                                    from cargo_empleado
                                    where estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; 
                            ?>
                    </select>  
                    <label>TÈLEFONO</label>
                    <input type="number" class="form-control input-sm" id="telefono" name="telefono">
                    <label>DIRECCIÒN</label>
                    <input type="text" class="form-control input-sm" id="direccion" name="direccion">
                    <label>CORREO</label>
                    <input type="text" class="form-control input-sm" id="correo" name="correo"> 
                    <p></p>
                    <span class="btn btn-primary" id="btnAgregarEmpleado">Agregar</span>
                </form>
            </div>
            <div class="col-sm-8">
                <div id="tablaEmpleadosLoad"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="abremodalEmpleadosUpdate" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog smodal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Actualizar empleado</h4>
                </div> 
                <div class="modal-body">
                    <form id="frmEmpleadosU">
                    <div class="row">
                    <div class="col-sm-6">
                    <input type="text" hidden="" id="idempleadoU" name="idempleadoU"> 
                    <label>CEDULA</label>
                    <input type="text" class="form-control input-sm" id="cedulaU" name="cedulaU">
                    <label>NOMBRES Y APELLIDOS</label>
                    <input type="text" class="form-control input-sm" id="nombreU" name="nombreU">
                    <label>GÈNERO</label> 
                    <select class="form-control input-sm" id="generoSelectU" name="generoSelectU">
                          <option value="A">Selecciona Genero</option>
                            <?php 
                                    $sql="SELECT id_genero,genero
                                    from genero
                                    where estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; 
                            ?>
                    </select>
                    <label>NACIMIENTO</label>
                    <input type="date" class="form-control input-sm" id="nacimientoU" name="nacimientoU"> 
                    </div>
                    <div class="col-sm-6">  
                    <label>CARGO/PUESTO</label>
                    <select class="form-control input-sm" id="cargoSelectU" name="cargoSelectU">
                          <option value="A">Selecciona Genero</option>
                            <?php 
                                    $sql="SELECT id_cargo,cargo
                                    from cargo_empleado
                                    where estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; 
                            ?>
                    </select>  
                    <label>TÈLEFONO</label>
                    <input type="number" class="form-control input-sm" id="telefonoU" name="telefonoU">
                    <label>DIRECCIÒN</label>
                    <input type="text" class="form-control input-sm" id="direccionU" name="direccionU">
                    <label>CORREO</label>
                    <input type="text" class="form-control input-sm" id="correoU" name="correoU"> 
                 </div>
                </form>
                </div>
                <p></p>
                <p></p>  
                <div class="modal-footer">
                    <div style="text-align: center;"> 
                    <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
                    <button id="btnActualizarEmpleadoU" type="button" class="btn btn-success" data-dismiss="modal">Actualizar</button>
                   </div>
                </div> 
            </div>
        </div>
    </div> 
</body>
</html>

<script type="text/javascript">
    function agregaDatosEmpleado(idempleado){ 
        $.ajax({
            type:"POST",
            data:"idempleado=" + idempleado,
            url:"../procesos/empleados/obtenDatosEmpleado.php",
            success:function(r){  
                dato=jQuery.parseJSON(r);
                $('#idempleadoU').val(dato['id_empleados']); 
                $('#cedulaU').val(dato['cedula']);  
                $('#nombreU').val(dato['nombres_apellidos']);
                $('#generoSelectU').val(dato['id_genero']);
                $('#nacimientoU').val(dato['fecha_nacimiento']);
                $('#cargoSelectU').val(dato['id_cargo']);
                $('#telefonoU').val(dato['telefono']);
                $('#direccionU').val(dato['direccion']);
                $('#correoU').val(dato['email']);  
            }
        });
    }

    function eliminarEmpleado(idempleado){ 
        swal.fire({
            title: "¿Estás seguro que deseas eliminar este empleado?",
            text: "Se eliminara este empleado de la base de datos en caso que presiones Sí, eliminar.",
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
                    data:"idempleado=" + idempleado,
                    url:"../procesos/empleados/eliminarEmpleado.php",
                    success:function(r){ 
                        if(r==1){
                            $('#tablaEmpleadosLoad').load("empleados/tablaEmpleados.php"); 
                            Swal.fire({
                            title: "Empleado eliminado con exito!!",
                                icon: "success",
                            draggable: true,
                            }); 
                        }else{
                            Swal.fire({
                            title: "No se pudo eliminar el empleado!!",
                                icon: "error",
                            draggable: true,
                            }); 
                        }
                    }
                });
            }
        }); 	
    }
    function activarEmpleado(idempleado){ 
        swal.fire({
            title: "¿Estás seguro que deseas activar este empleado?",
            text: "Se activara este empleado de la base de datos en caso que presiones Sí, activar.",
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
                    data:"idempleado=" + idempleado,
                    url:"../procesos/empleados/activarEmpleado.php",
                    success:function(r){ 
                        if(r==1){
                            $('#tablaEmpleadosLoad').load("empleados/tablaEmpleados.php"); 
                            Swal.fire({
                            title: "Empleados activado con exito!!",
                                icon: "success",
                            draggable: true,
                            }); 
                        }else{
                            Swal.fire({
                            title: "No se pudo activado el empleado!!",
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

        $('#tablaEmpleadosLoad').load("empleados/tablaEmpleados.php");

        $('#btnAgregarEmpleado').click(function(){

            vacios=validarFormVacio('frmEmpleados');

            if(vacios > 0){ 
                Swal.fire({
                            title: "Debes llenar todos los campos!!",
                            icon: "warning",
                            draggable: true,
                            }); 
                return false;
            }

            datos=$('#frmEmpleados').serialize();

            $.ajax({
                type:"POST",
                data:datos,
                url:"../procesos/empleados/agregaEmpleado.php",
                success:function(r){ 
                    if(r==2){
                        Swal.fire({
                            title: "La cédula ya existe.",
                            icon: "error",
                            text: "No puedes registrar más de un empleado con la misma cédula.",
                            draggable: true,
                        });
                        return false;
                    }
                    if(r==1){
                        $('#frmEmpleados')[0].reset();
                        $('#tablaEmpleadosLoad').load("empleados/tablaEmpleados.php"); 
                            Swal.fire({
                            title: "Empleado agregado con exito",
                            icon: "success",
                            draggable: true,
                            });
                    }else{
                            Swal.fire({
                            title: "No se pudo agregar el empleado",
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
        $('#btnActualizarEmpleadoU').click(function(){
            datos=$('#frmEmpleadosU').serialize();

            $.ajax({
                type:"POST",
                data:datos,
                url:"../procesos/empleados/actualizaEmpleado.php",
                success:function(r){  
                    if(r==2){
                        Swal.fire({
                            title: "La cédula ya existe.",
                            icon: "error",
                            text: "No puedes registrar más de un empleado con la misma cédula.",
                            draggable: true,
                        });
                        return false;
                    }
                    if(r==1){
                        $('#frmEmpleadosU')[0].reset();
                        $('#tablaEmpleadosLoad').load("empleados/tablaEmpleados.php"); 
                        Swal.fire({
                            title: "Empleado actualizado con exito",
                            icon: "success",
                            draggable: true,
                            }); 
                    }else{
                        Swal.fire({
                            title: "No se pudo actualizar el empleado",
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