<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "CARGOS EMPLEADOS");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?> 
<!DOCTYPE html>
<html>
<head>
    <title>REGISTROS GENERAL -> CARGOS EMPLEADOS</title>
    <?php require_once "menu.php"; ?>
</head>
<body> 
    <div class="container">
        <h1>Cargos Empleados</h1>
        <div class="row">
            <div class="col-sm-4">
                <form id="frmCargos">
                    <label>Cargo Empleado</label>
                    <input type="text" class="form-control input-sm" name="cargo" id="cargo">
                    <p></p>
                    <span class="btn btn-primary" id="btnAgregaCargo">Agregar</span>
                </form>
            </div>
            <div class="col-sm-6">
                <div id="tablaCargoLoad"></div>
            </div>
        </div>
    </div> 
    <!-- Modal -->
    <div class="modal fade" id="actualizaEmpleado" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog smodal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Datos del Cargo</h4>
                </div>
                <div class="modal-body">
                    <form id="frmCargosU">
                        <input type="text" hidden="" id="idcargo" name="idcargo">
                        <label>Cargo Empleado</label>
                        <input type="text" id="cargoU" name="cargoU" class="form-control input-sm">
                    </form> 
                </div>
                <div class="modal-footer">
                    <div style="text-align: center;"> 
                        <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
                        <button type="button" id="btnActualizaCargo" class="btn btn-success" data-dismiss="modal">Guardar</button>
                    </div> 
                </div>
            </div>
        </div>
    </div>

</body>
</html>
<script type="text/javascript">
    $(document).ready(function(){ 
        $('#tablaCargoLoad').load("cargoempleados/tablaCargo.php"); 
        $('#btnAgregaCargo').click(function(){ 
            vacios=validarFormVacio('frmCargos'); 
            if(vacios > 0){ 
                Swal.fire({
                    title: "Campos requeridos",
                    icon: "warning",
                    draggable: true,
                    text: "Debes llenar todos los campos!!"
                }); 
                return false;
            } 
            datos=$('#frmCargos').serialize();
            $.ajax({
                type:"POST",
                data:datos,
                url:"../procesos/cargosempleos/agregaCargo.php",
                success:function(r){
                    if(r==2){
                        Swal.fire({
                            title: "Cargo ya existe",
                            icon: "error",
                            draggable: true,
                            text: "No puedes registrar dos cargos con el mismo nombre."
                        });
                        return false;
                    }
                    if(r==1){
                        $('#frmCargos')[0].reset();
                        $('#tablaCargoLoad').load("cargoempleados/tablaCargo.php"); 
                        Swal.fire({
                            title: "Cargo agregado con exito",
                            icon: "success",
                            draggable: true,
                        });
                    }else{
                        Swal.fire({
                            title: "Agregar cargo",
                            icon: "error",
                            draggable: true,
                            text: "No se pudo agregar el cargo"
                        }); 
                    }
                }
            });
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#btnActualizaCargo').click(function(){
            datos=$('#frmCargosU').serialize();
            $.ajax({
                type:"POST",
                data:datos,
                url:"../procesos/cargosempleos/actualizaCargo.php",
                success:function(r){
                    if(r==2){
                        Swal.fire({
                            title: "Cargo ya existe",
                            icon: "error",
                            draggable: true,
                            text: "No puedes registrar dos cargos con el mismo nombre."
                        });
                        return false;
                    }
                    if(r==1){
                        $('#tablaCargoLoad').load("cargoempleados/tablaCargo.php"); 
                        Swal.fire({
                            title: "Cargo actualizado con exito",
                            icon: "success",
                            draggable: true,
                        });
                    }else{
                        Swal.fire({
                            title: "Actualizar cargo",
                            icon: "error",
                            draggable: true,
                            text: "No se pudo actualizar el cargo"
                        });  
                    }
                }
            });
        });
    });
</script>

<script type="text/javascript">
    function agregaDato(idcargo,cargo){
        $('#idcargo').val(idcargo);
        $('#cargoU').val(cargo);
    }

    function eliminaCargo(idcargo){
        swal.fire({
            title: "¿Estás seguro que deseas eliminar este cargo?",
            text: "Se eliminara este cargo de la base de datos en caso que presiones Sí, eliminar.",
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
                    data:"idcargo=" + idcargo,
                    url:"../procesos/cargosempleos/eliminarCargo.php",
                    success:function(r){
                        if(r==1){
                            $('#tablaCargoLoad').load("cargoempleados/tablaCargo.php"); 
                            Swal.fire({
                                title: "Cargo eliminado con exito!!",
                                icon: "success",
                                draggable: true,
                            });
                        }else{
                            Swal.fire({
                                title: "Eliminar cargo",
                                icon: "error",
                                draggable: true,
                                text: "No se pudo eliminar el cargo"
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