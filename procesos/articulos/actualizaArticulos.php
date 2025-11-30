<?php 

require_once "../../clases/Conexion.php";
require_once "../../clases/Articulos.php";

$obj= new articulos();
$datos=array();
$nombreImg=$_FILES['imagenU']['name'];

// VALIDACIONES ANTES DE ACTUALIZAR (excluir el mismo id)
if ($obj->articuloExisteNombre(($_POST['nombreU']), $_POST['idarticulo'])) {
	echo 2; // Nombre duplicado
	exit();
}
if (!empty($_POST['codigobarraU'])) {
    if ($obj->articuloExisteCodigoBarra(trim($_POST['codigobarraU']), $_POST['idarticulo'])) {
        echo 3; // Código barra duplicado
        exit();
    }
}

/*1. Producto con IMAGEN*/
if ($nombreImg != "")
{
	$obj->eliminarImagen($_POST['idarticulo']);   
	$rutaAlmacenamiento=$_FILES['imagenU']['tmp_name'];
	$carpeta='../../archivos/';
	$rutaFinal=$carpeta.$nombreImg;

	$datosImg=array(
		$_POST['categoriaSelectU'],
		$nombreImg,
		$rutaFinal
	 ); 

	if(move_uploaded_file($rutaAlmacenamiento, $rutaFinal)){ 
		$idimagen=$obj->agregaImagen($datosImg); 
		if($idimagen > 0){ 
			$datos[0]=$_POST['idarticulo'];
			$datos[1]=$_POST['categoriaSelectU'];
			$datos[2]= trim($_POST['nombreU']);
			$datos[3]=$_POST['descripcionU'];
			$datos[4]=0;
			$datos[5]=$_POST['precioU'];
			$datos[6]=$_POST['subcategoriaSelectU'];
			$datos[7]= trim($_POST['codigobarraU']);
			$datos[8]=$_POST['proveedorSelectU'];
			$datos[9]=$_POST['referenciaU'];
			$datos[10]=$idimagen;
			echo $obj->actualizaArticulo($datos);
		}else{
			echo 0;
		} 
	}  
} 
else {

	/*2. Producto SIN IMAGEN*/
	$datos[0] = $_POST['idarticulo'];
	$datos[1] = $_POST['categoriaSelectU'];
	$datos[2] =  trim($_POST['nombreU']);
	$datos[3] = $_POST['descripcionU'];
	$datos[4] = 0;
	$datos[5] = $_POST['precioU'];
	$datos[6] =	$_POST['subcategoriaSelectU'];
	$datos[7] = trim($_POST['codigobarraU']);
	$datos[8] = $_POST['proveedorSelectU'];
	$datos[9] = $_POST['referenciaU'];
	$datos[10] =	0;
	echo $obj->actualizaArticulo($datos);
}
?>