<?php 
 ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 

require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "ARTICULOS");
$accesoVistaPrecio = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "VER PRECIO ARTICULOS");
$accesoAgregarArticulo = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "AGREGAR ARTICULOS");
$accesoEditarArticulo = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "EDITAR ARTICULOS");
$accesoEliminarArticulo = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "ELIMINAR ARTICULOS");
$_SESSION['accesoVistaPrecio'] = $accesoVistaPrecio;
$_SESSION['accesoAgregarArticulo'] = $accesoAgregarArticulo;
$_SESSION['accesoEditarArticulo'] = $accesoEditarArticulo;
$_SESSION['accesoEliminarArticulo'] = $accesoEliminarArticulo;
if (!$acceso) {
    header("location:../index.php");
    exit();
}
?> 
<!DOCTYPE html>
<html>
<head>
    <title>REGISTROS GENERAL -> ARTICULOS</title>
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <?php require_once "menu.php"; ?>
    <?php  $c= new conectar();
    $conexion=$c->conexion(); 
    ?>
</head>
<body>
    <div class="container"> 
        <div class="row"> 
            <div class="col-sm-12">
                <div id="tablaArticulosLoad"></div>
            </div>
        </div>
    </div>

    <!-- Button trigger modal -->
    
    <!-- Modal EDIT ARTICULO -->
    <div class="modal fade" id="abremodalUpdateArticulo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog smodal-xl" role="document">
            <div class="modal-content"> 
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">ARTICULO ACTUALIZAR</h4>
                </div>
                <div class="modal-body">
                    <form id="frmArticulosU" enctype="multipart/form-data">
                     <input type="text" hidden="" id="idarticulo" name="idarticulo">
                     <div class="row">
                     <div class="col-sm-6">
                      <label>COD.BARRA</label>
                      <input type="text" placeholder="Ingresar el còdigo de barra" class="form-control input-sm" id="codigobarraU" name="codigobarraU">
                      <label>NOMBRE</label>
                      <input type="text" placeholder="Ingresar el nombre" class="form-control input-sm" id="nombreU" name="nombreU" required> 
                      <label>PROVEEDOR</label>
                      <select class="form-control input-sm" id="proveedorSelectU" name="proveedorSelectU" required>
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
                      <label>DESCRIPCION</label>
                      <input type="text"  placeholder="Ingresar la descripciòn" class="form-control input-sm" id="descripcionU" name="descripcionU" required> 
                     </div>
                     <div class="col-sm-6"> 
                      <label>REFERENCIA</label>
                      <input type="text"  placeholder="Ingresar la referencia" class="form-control input-sm" id="referenciaU" name="referenciaU" required> 
                     <label>CATEGORIA</label>
                      <select class="form-control input-sm" id="categoriaSelectU" name="categoriaSelectU" required>
                          <option value="A">Selecciona Categoria</option>
                            <?php 
                                    $sql="SELECT id_categoria,nombreCategoria
                                    from categorias
                                    where subcategoria = 0 and estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; ?>
                        </select>  
                      <label>SUB.CATEGORIA</label>
                      <select class="form-control input-sm" id="subcategoriaSelectU" name="subcategoriaSelectU" required>
                          <option value="A">Selecciona la Sub Categoria</option>
                            <?php 
                                    $sql="SELECT id_categoria,nombreCategoria
                                    from categorias
                                    where subcategoria = 1 and estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; ?>
                        </select>
                     <?php if ($accesoVistaPrecio): ?>
                     <label>PRECIO</label>
                     <input type="number" placeholder="Ingresar el precio" class="form-control input-sm" id="precioU" name="precioU">
                     <?php else: ?>
                     <input type="hidden" id="precioU" name="precioU">
                     <?php endif; ?>
                    </div>
                </div> 
                <p></p> 
                 <label>Imagen actual:</label> 
                 <img width="140" id="imagenShow" name="imagenShow" src="" height="140">
                 <p></p> 
                 <label style="color:#077581;">Seleccionar nueva Imagen</label> 
                 <input style="color: #c46f1b;font-weight: bold;" type="file" id="imagenU" name="imagenU" accept="image/png, image/gif, image/jpeg">
                <p></p> 
                </form>
                </div>
                <div class="modal-footer">
                    <div style="text-align: center;"> 
                         <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >Cancelar</button>
                         <button title="Actualizar articulo" id="btnActualizaarticulo" type="button" class="btn btn-warning btn-sucess-custom" data-dismiss="modal">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
   </div> 
   <div class="modal fade" id="abremodalUpdateAgregarArticulo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">DETALLES DEL ARTICULO</h4>
                </div>
                <div class="modal-body"> 
                 <form id="frmArticulos" enctype="multipart/form-data">
                   <div class="row">
                     <div class="col-sm-6">
                      <label>COD.BARRA</label>
                      <input type="text" placeholder="Ingresar el còdigo de barra" class="form-control input-sm" id="codigobarra" name="codigobarra">
                      <label>NOMBRE</label>
                      <input type="text" placeholder="Ingresar el nombre" class="form-control input-sm" id="nombre" name="nombre" required> 
                      <label>PROVEEDOR</label>
                      <select class="form-control input-sm" id="proveedorSelect" name="proveedorSelect" required>
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
                      <label>DESCRIPCION</label>
                      <input type="text"  placeholder="Ingresar la descripciòn" class="form-control input-sm" id="descripcion" name="descripcion" required> 
                     </div>
                     <div class="col-sm-6"> 
                      <label>REFERENCIA</label>
                      <input type="text"  placeholder="Ingresar la referencia" class="form-control input-sm" id="referencia" name="referencia" required> 
                      <label>CATEGORIA</label>
                      <select class="form-control input-sm" id="categoriaSelect" name="categoriaSelect" required>
                          <option value="A">Selecciona Categoria</option>
                            <?php 
                                    $sql="SELECT id_categoria,nombreCategoria
                                    from categorias
                                    where subcategoria = 0 and estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; ?>
                        </select>  
                      <label>SUB.CATEGORIA</label>
                      <select class="form-control input-sm" id="subcategoriaSelect" name="subcategoriaSelect" required>
                          <option value="A">Selecciona la Sub Categoria</option>
                            <?php 
                                    $sql="SELECT id_categoria,nombreCategoria
                                    from categorias
                                    where subcategoria = 1 and estado = 1";
                                    $result=mysqli_query($conexion,$sql);
                                    while($ver=mysqli_fetch_row($result)): ?>
                                   <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label>PRECIO</label>
                        <input type="number"placeholder="Ingresar el precio" class="form-control input-sm" id="precio" name="precio" required>
                    </div>
                </div> 
                <p></p> 
                <label style="color:#077581;">Imagen</label>
                <input style="color: #c46f1b;font-weight: bold;" type="file" id="imagen" name="imagen" accept="image/png, image/gif, image/jpeg">
                <p></p> 
                </form>
                </div>
                <div class="modal-footer">
                    <div style="text-align: center;"> 
                       <button title="Cancelar"  type="button" data-dismiss="modal" class="btn btn-danger" >CANCELAR</button>
                       <button title="Agregar nuevo articulo" id="btnAgregaArticulo" type="button" class="btn btn-success" data-dismiss="modal">GUARDAR</button>
                    </div> 
                </div>  
            </div>
        </div>
    </div>
</body>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</html>

<script type="text/javascript">
    var urlRelative = window.parent.location.href;
    function agregaDatosArticulo(idarticulo){
        $.ajax({
            type:"POST",
            data:"idart=" + idarticulo,
            url:"../procesos/articulos/obtenDatosArticulo.php",
            success:function(r){   
                dato=jQuery.parseJSON(r); 
                $('#idarticulo').val(dato['id_producto']);
                $('#categoriaSelectU').val(dato['id_categoria']).trigger('change');
                $('#nombreU').val(dato['nombre']);
                $('#descripcionU').val(dato['descripcion']);
                $('#precioU').val(dato['precio']);
                $('#referenciaU').val(dato['referencia']); 
                $('#proveedorSelectU').val(dato['id_proveedor']).trigger('change');
                $('#codigobarraU').val(dato['codigobarra']); 
                $('#subcategoriaSelectU').val(dato['id_subcategoria']).trigger('change');  
                if (dato['ruta'] != "")
                 {
                     $("#imagenShow").attr("src", urlRelative +"/" + dato['ruta']);
                 } 
            }
        });
    }

    function eliminaArticulo(idArticulo){ 
        swal.fire({
            title: "¿Estás seguro que deseas eliminar este articulo?",
            text: "Se eliminara este articulo de la base de datos en caso que presiones Sí, eliminar.",
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
                    data:"idarticulo=" + idArticulo,
                    url:"../procesos/articulos/eliminarArticulo.php",
                    success:function(r){ 
                        if(r==1){
                            $('#tablaArticulosLoad').load("articulos/tablaArticulos.php"); 
                            Swal.fire({
                                title: "Articulo eliminado con exito!!",
                                icon: "success",
                                draggable: true,
                            }); 
                        }else{
                            Swal.fire({
                                title: "No se pudo eliminar el articulo!!",
                                icon: "error",
                                draggable: true,
                            }); 
                        }
                    }
                });
            }
        });     
    }
    function activarArticulo(idArticulo){ 
        swal.fire({
            title: "¿Estás seguro que deseas activar este articulo?",
            text: "Se activara este articulo de la base de datos en caso que presiones Sí, activar.",
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
                    data:"idarticulo=" + idArticulo,
                    url:"../procesos/articulos/activarArticulo.php",
                    success:function(r){ 
                        if(r==1){
                            $('#tablaArticulosLoad').load("articulos/tablaArticulos.php"); 
                            Swal.fire({
                                title: "Articulo activado con exito!!",
                                icon: "success",
                                draggable: true,
                            }); 
                        }else{
                            Swal.fire({
                                title: "No se pudo activado el articulo!!",
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
        // Aplica Select2 a todos los selects de proveedor, categoria y subcategoria en ambos formularios
        $('#proveedorSelect, #categoriaSelect, #subcategoriaSelect').select2({
            width: '100%',
            dropdownParent: $('#abremodalUpdateAgregarArticulo'),
            placeholder: "Buscar..."
        });
        $('#proveedorSelectU, #categoriaSelectU, #subcategoriaSelectU').select2({
            width: '100%',
            dropdownParent: $('#abremodalUpdateArticulo'),
            placeholder: "Buscar..."
        });

        $('#btnActualizaarticulo').click(function(){ 
            vacios=validarFormVacioArticulosActualizar('frmArticulosU');  
            if(vacios > 0 ){ 
                Swal.fire({
                    title: "Debes llenar todos los campos!!",
                    icon: "warning",
                    draggable: true,
                });
                return false;
            } 
            var formData = new FormData(document.getElementById("frmArticulosU")); 
            $.ajax({
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                url:"../procesos/articulos/actualizaArticulos.php",
                success:function(r){  
                    if(r==1){
                        $('#tablaArticulosLoad').load("articulos/tablaArticulos.php"); 
                        Swal.fire({
                            title: "Actualizado con exito!!",
                            icon: "success",
                            draggable: true,
                        });
                    }else if(r == 2){
                        Swal.fire({
                            title: "Error: El nombre del artículo ya existe.",
                            icon: "error",
                            text : "Por favor ingresa un nombre diferente.",
                            draggable: true,
                        });
                    }else if(r == 3){
                        Swal.fire({
                            title: "Error: El código de barra ya existe.",
                            icon: "error",
                            text : "Por favor ingresa un código de barra único.",
                            draggable: true,
                        });
                    }else{
                        Swal.fire({
                            title: "Error al actualizar!!",
                            icon: "error",
                            draggable: true,
                        }); 
                    }
                }
            });
        });

        $('#tablaArticulosLoad').load("articulos/tablaArticulos.php"); 
        $('#btnAgregaArticulo').click(function(){ 
            var imagen =$('#imagen').val(); 
            vacios=validarFormVacioArticulosInsert('frmArticulos');  
            if(vacios > 0 || imagen == ""){ 
                Swal.fire({
                    title: "Debes llenar todos los campos!!",
                    icon: "warning",
                    draggable: true,
                });
                return false;
            }  
            var formData = new FormData(document.getElementById("frmArticulos")); 
            $.ajax({
                url: "../procesos/articulos/insertaArticulos.php",
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,

                success:function(r){  
                    if(r == 1){
                        $('#frmArticulos')[0].reset();
                        $('#tablaArticulosLoad').load("articulos/tablaArticulos.php"); 
                        Swal.fire({
                            title: "Articulo agregado con exito!!",
                            icon: "success",
                            draggable: true,
                        });
                    }else if(r == 2){
                        Swal.fire({
                            title: "Error: El nombre del artículo ya existe.",
                            icon: "error",
                            text : "Por favor ingresa un nombre diferente.",
                            draggable: true,
                        });
                    }else if(r == 3){
                        Swal.fire({
                            title: "Error: El código de barra ya existe.",
                            icon: "error",
                            text : "Por favor ingresa un código de barra único.",
                            draggable: true,
                        });
                    }else{
                        Swal.fire({
                            title: "Error al agregar el artículo.",
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
<?php 
}else{
    header("location:../index.php");
}
?>