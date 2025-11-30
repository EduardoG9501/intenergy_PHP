<?php 
    require_once "../../clases/Conexion.php";
    require_once "../../clases/Bodegas.php";

    $idBodega = $_POST['idbodega'];
    $bodega = $_POST['bodegaU'];
    $esPrincipal = isset($_POST['esPrincipalU']) ? 1 : 0;
    $idBodegaPadre = (isset($_POST['bodegaPadreU']) && $_POST['bodegaPadreU'] != '') ? $_POST['bodegaPadreU'] : null;

    if ($esPrincipal) {
        $idBodegaPadre = null;
    }
    $esSecundaria = ($idBodegaPadre !== null) ? 1 : 0;

    $datos = array(
        $idBodega,
        $bodega,
        $idBodegaPadre,
        $esSecundaria
    );

    $obj = new bodegas();

    $resultado = $obj->actualizaBodega($datos);

    if ($resultado === "EXISTE") {
        echo "EXISTE";
    } else {
        echo $resultado;
    }
?>