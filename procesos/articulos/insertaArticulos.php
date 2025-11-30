<?php 
	session_start();
	$iduser=$_SESSION['iduser'];
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Articulos.php";

	$obj= new articulos();

	$datos=array();

	$nombreImg=$_FILES['imagen']['name'];
	$rutaAlmacenamiento=$_FILES['imagen']['tmp_name'];
	$carpeta='../../archivos/';
	$rutaFinal=$carpeta.$nombreImg;

	$datosImg=array(
		$_POST['categoriaSelect'],
		$nombreImg,
		$rutaFinal
	);

	// VALIDACIONES ANTES DE INSERTAR
	if ($obj->articuloExisteNombre( trim($_POST['nombre']))) {
		echo 2; // Nombre duplicado
		exit();
	}
	if (!empty($_POST['codigobarra'])) {
      if ($obj->articuloExisteCodigoBarra(trim($_POST['codigobarra']))) {
         echo 3; // Código barra duplicado
          exit();
      }
    }

	if(move_uploaded_file($rutaAlmacenamiento, $rutaFinal)){
		$idimagen=$obj->agregaImagen($datosImg);

		if($idimagen > 0){
			$datos[0]=$_POST['categoriaSelect'];
			$datos[1]=$idimagen;
			$datos[2]=$iduser;
			$datos[3]= trim($_POST['nombre']);
			$datos[4]=$_POST['descripcion'];
			$datos[5]=0;
			$datos[6]=$_POST['precio'];
			$datos[7]=$_POST['subcategoriaSelect']; 
			$datos[8]= trim($_POST['codigobarra']);
			$datos[9]=$_POST['proveedorSelect'];
			$datos[10]=$_POST['referencia'];
			echo $obj->insertaArticulo($datos);
		}else{
			echo 0;
		}
	}
 ?>