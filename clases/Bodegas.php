<?php 

class bodegas{

    // Valida si ya existe una bodega con ese nombre (sin importar mayúsculas/minúsculas/espacios)
    private function bodegaExiste($conexion, $nombreBodega, $id_bodega = null) {
        $nombreNormalizado = strtoupper(trim($nombreBodega));
        $condicion = "";
        if ($id_bodega !== null) {
            $condicion = " AND id_bodega != '$id_bodega'";
        }
        $sql = "SELECT COUNT(*) as total FROM bodegas 
                WHERE Estado = 1 and UPPER(TRIM(nombreBodega)) = '$nombreNormalizado' $condicion";
        $res = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($res);
        return ($row && $row['total'] > 0);
    }

    /**
     * $datos = array($idusuario, $bodega, $fecha, $idBodegaPadre)
     */
    public function agregaBodega($datos){
        $c= new conectar();
        $conexion=$c->conexion();

        // Validación de nombre único
        if ($this->bodegaExiste($conexion, $datos[1])) {
            return "EXISTE";
        }

        // Si no hay padre, debe ser principal
        $idBodegaPadre = ($datos[3] !== null && $datos[3] !== "") ? "'{$datos[3]}'" : "NULL";
        $esSecundaria = ($datos[3] !== null && $datos[3] !== "") ? 1 : 0;

        $sql="INSERT into bodegas(
                    id_usuario,
                    nombreBodega,
                    fechaCaptura,
                    id_bodega_principal,
                    es_bodega_secundaria
                )
                values (
                    '{$datos[0]}',
                    '{$datos[1]}',
                    '{$datos[2]}',
                    $idBodegaPadre,
                    $esSecundaria
                )";

        return mysqli_query($conexion,$sql) ? 1 : 0;
    }

    /**
     * $datos = array($idBodega, $bodega, $idBodegaPadre)
     */
    public function actualizaBodega($datos){
        $c= new conectar();
        $conexion=$c->conexion();

        // Validación de nombre único (excluyendo la propia)
        if ($this->bodegaExiste($conexion, $datos[1], $datos[0])) {
            return "EXISTE";
        }

        $idBodegaPadre = ($datos[2] !== null && $datos[2] !== "") ? "'{$datos[2]}'" : "NULL";
        $esSecundaria = ($datos[2] !== null && $datos[2] !== "") ? 1 : 0;

        $sql="UPDATE bodegas 
                SET nombreBodega='{$datos[1]}',
                    id_bodega_principal=$idBodegaPadre,
                    es_bodega_secundaria=$esSecundaria
                WHERE id_bodega='{$datos[0]}'";
        return mysqli_query($conexion,$sql) ? 1 : 0;
    }

    public function eliminaBodega($idbodega){ 
        $c= new conectar();
        $conexion=$c->conexion();

        $sql="UPDATE bodegas set estado=0
              where id_bodega='$idbodega'";
        return mysqli_query($conexion,$sql); 
    }
    public function inactivaBodega($idbodega){
        $c= new conectar();
        $conexion=$c->conexion();
        $sql="UPDATE bodegas set estado=0
                            where id_bodega='$idbodega'";
        return mysqli_query($conexion,$sql);
    }
     
    public function listar() {
       $c= new conectar();
       $conexion=$c->conexion();
       $sql = "SELECT id_bodega, nombreBodega AS nombre FROM bodegas";
       $result = mysqli_query($conexion, $sql);
       $datos = [];
       while($row = mysqli_fetch_assoc($result)){
           $datos[] = $row;
       }
       return $datos;
    }
    public function listarpermisos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_bodega, nombreBodega FROM bodegas WHERE estado = 1";
        $res = mysqli_query($conexion, $sql);
        $bodegas = [];
        while($row = mysqli_fetch_assoc($res)) {
            $bodegas[] = $row;
        }
        return $bodegas;
    }
}

?>