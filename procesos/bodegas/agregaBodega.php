<?php 
    session_start();
    require_once "../../clases/Conexion.php";
    require_once "../../clases/Bodegas.php";
    $fecha = date("Y-m-d");
    $idusuario = $_SESSION['iduser'];
    $bodega = $_POST['bodega'];
    $esPrincipal = isset($_POST['esPrincipal']) ? 1 : 0;
    $idBodegaPadre = (isset($_POST['bodegaPadre']) && $_POST['bodegaPadre'] != '') ? $_POST['bodegaPadre'] : null;

    // Si es principal, no debe tener bodega padre ni ser secundaria
    if ($esPrincipal) {
        $idBodegaPadre = null;
    }
    $esSecundaria = ($idBodegaPadre !== null) ? 1 : 0;

    $datos = array(
        $idusuario,
        $bodega,
        $fecha,
        $idBodegaPadre,
        $esSecundaria
    );

    $obj = new bodegas();

    $resultado = $obj->agregaBodega($datos);

    if ($resultado === "EXISTE") {
        echo "EXISTE";
    } else {
        echo $resultado;
    }
?>