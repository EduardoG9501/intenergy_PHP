<?php 

require_once "../../clases/Conexion.php";
require_once "../../clases/Movimientos.php";

   $obj= new movimientos();
   $datos=array();

   $datos[0] = $_POST['idmovimiento'];
   $datos[1] = $_POST['bodegaSelectU'];
   $datos[2] = $_POST['tipoSelectU'];
   $datos[3] = $_POST['subtipoSelectU'];
   $datos[4] = $_POST['proveedorSelectU'];
   $datos[5] = $_POST['nodocumentoU'];
   $datos[6] = $_POST['fechadocumentoU'];
   $datos[7] = $_POST['formapagoSelectU'];
   $datos[8] = $_POST['fechapagoU'];
   $datos[9] = $_POST['diapagoU'];
   $datos[10] = $_POST['observacionU']; 

   $result = $obj->actualizaMovimiento($datos);
   echo $result 
?>