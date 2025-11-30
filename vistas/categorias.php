<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "CATEGORIAS");
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?> 
	<!DOCTYPE html>
	<html>
	<head>
		<title>REGISTROS GENERAL -> CATEGORIAS</title>
		<?php require_once "menu.php"; ?>
	</head>
	<body> 
		<div class="container">
			<h1>Categorias</h1>
			<div class="row">
				<div class="col-sm-4">
					<form id="frmCategorias">
						<label>Nombre Categoria</label>
						<input type="text" class="form-control input-sm" name="categoria" id="categoria">
                        <label>Es Subcategoria</label>
                        <input type="checkbox" id="subcategoria" name="subcategoria"> 
						<p></p>
						<span class="btn btn-primary" id="btnAgregaCategoria">Agregar</span>
					</form>
				</div>
				<div class="col-sm-6">
					<div id="tablaCategoriaLoad"></div>
				</div>
			</div>
		</div>

		<!-- Button trigger modal -->

		<!-- Modal -->
		<div class="modal fade" id="actualizaCategoria" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog smodal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Actualiza categorias</h4>
					</div>
					<div class="modal-body">
						<form id="frmCategoriaU">
							<input type="text" hidden="" id="idcategoria" name="idcategoria">
							<label>Categoria</label>
							<input type="text" id="categoriaU" name="categoriaU" class="form-control input-sm">
							<label>Es Subcategoria</label>
							<input type="checkbox" id="subcategoriaU" name="subcategoriaU" >
						</form> 
					</div>
					<div class="modal-footer">
					  <div style="text-align: center;"> 
						 <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
						 <button type="button" id="btnActualizaCategoria" class="btn btn-success" data-dismiss="modal">Guardar</button>
					  </div> 
					</div> 
				</div>
			</div>
		</div> 
	</body>
	</html>
	<script type="text/javascript">
		$(document).ready(function(){

			$('#tablaCategoriaLoad').load("categorias/tablaCategorias.php");

			$('#btnAgregaCategoria').click(function(){

				vacios=validarFormVacio('frmCategorias');

				if(vacios > 0){
					Swal.fire({
                               title: "Debes llenar todos los campos!!",
                                icon: "warning",
                               draggable: true,
                             }); 
					return false;
				}
                var vcategoria= $("#categoria").val();
			    var vsubcategoria= $("#subcategoria").prop("checked")==true?"1":"0";
                const inputValue= "iduser=<?php echo $_SESSION['iduser'];?>&categoria="+vcategoria+"&subcategoria="+vsubcategoria;
				$.ajax({
					type:"POST",
					data:inputValue,
					url:"../procesos/categorias/agregaCategoria.php",
					success:function(r){ 
					if(r==1){
					//esta linea nos permite limpiar el formulario al insetar un registro
					$('#frmCategorias')[0].reset();

					$('#tablaCategoriaLoad').load("categorias/tablaCategorias.php"); 
					Swal.fire({
                               title: "Categoria agregada con exito!!",
                                icon: "success",
                               draggable: true,
                             });
				 }else if(r==2){
                    Swal.fire({
                           title: "Ya existe una categoria/subcategoria activa con ese nombre!",
                          icon: "warning",
                          draggable: true,
                          });
                  }else{
					Swal.fire({
                               title: "No se pudo agregar categoria",
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
			$('#btnActualizaCategoria').click(function(){

				///datos=$('#frmCategoriaU').serialize();
				var vidcategoria= $("#idcategoria").val();
                var vcategoria= $("#categoriaU").val();
			    var vsubcategoria= $("#subcategoriaU").prop("checked")==true?"1":"0";
				 
                const inputValue= "idcategoria="+vidcategoria+"&categoriaU="+vcategoria+"&subcategoriaU="+vsubcategoria;
				$.ajax({
					type:"POST",
					data:inputValue,
					url:"../procesos/categorias/actualizaCategoria.php",
					success:function(r){
						if(r==1){
							$('#tablaCategoriaLoad').load("categorias/tablaCategorias.php"); 
							Swal.fire({
                               title: "Categoria actualizada con exito!!",
                                icon: "success",
                               draggable: true,
                             });
						 }
					  else if(r==2){
                           Swal.fire({
                           title: "Ya existe una categoria/subcategoria activa con ese nombre!",
                           icon: "warning",
                          draggable: true,
                           });
                     }else{
							Swal.fire({
                               title: "No se pudo actaulizar la categoria",
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
		function agregaDato(idCategoria,categoria,subcategoria){
			$('#idcategoria').val(idCategoria);
			$('#categoriaU').val(categoria); 
			if (subcategoria == 1)
		     {
	             $("#subcategoriaU").prop('checked', true);
		     }else{
                 $("#subcategoriaU").prop('checked', false);
		     } 
		}

function eliminaCategoria(idcategoria){ 
swal.fire({
       title: "¿Estás seguro que deseas eliminar esta categoria?",
      text: "Se eliminara esta categoria de la base de datos en caso que presiones Sí, eliminar.",
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
					data:"idcategoria=" + idcategoria,
					url:"../procesos/categorias/eliminarCategoria.php",
					success:function(r){
						if(r==1){
							$('#tablaCategoriaLoad').load("categorias/tablaCategorias.php"); 
							Swal.fire({
                               title: "Categoria eliminada con exito!!",
                                icon: "success",
                               draggable: true,
                             }); 
						}else{ 
							Swal.fire({
                               title: "No se pudo eliminar la categoria!!",
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
	<?php 
}else{
	header("location:../index.php");
}
?>