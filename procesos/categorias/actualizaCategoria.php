<?php 
require_once "../../clases/Conexion.php";
require_once "../../clases/Categorias.php";

$idcategoria = intval($_POST['idcategoria']);
$categoria = trim($_POST['categoriaU']);
$subcategoria = $_POST['subcategoriaU'];

$obj = new categorias();

// Validación de nombre repetido solo en estado=1 (excluye el mismo id)
if ($obj->existeNombreCategoria($categoria, $idcategoria)) {
    echo "2";
    exit;
}

$datos = array(
    $idcategoria,
    $categoria,
    $subcategoria
);

echo $obj->actualizaCategoria($datos);
?>