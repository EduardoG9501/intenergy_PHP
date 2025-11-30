<?php 

class categorias{

    public function existeNombreCategoria($nombre, $idcategoria = 0) {
        $c = new conectar();
        $conexion = $c->conexion();
        if ($idcategoria > 0) {
            $sql = "SELECT COUNT(*) as existe FROM categorias WHERE LOWER(nombreCategoria) = LOWER(?) AND id_categoria != ? AND estado = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si", $nombre, $idcategoria);
        } else {
            $sql = "SELECT COUNT(*) as existe FROM categorias WHERE LOWER(nombreCategoria) = LOWER(?) AND estado = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $nombre);
        }
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();
        return $existe > 0;
    }

    public function agregaCategoria($datos){
        $c= new conectar();
        $conexion=$c->conexion();

        $sql="INSERT into categorias(id_usuario,
                                    nombreCategoria,  
                                    fechaCaptura,
                                    subcategoria)
                values ('$datos[0]',
                        '$datos[1]',
                        '$datos[2]',
                        $datos[3])";

        return mysqli_query($conexion,$sql);
    }

    public function actualizaCategoria($datos){
        $c= new conectar();
        $conexion=$c->conexion();

        $sql="UPDATE categorias set nombreCategoria='$datos[1]',subcategoria= $datos[2]
                            where id_categoria='$datos[0]'";
        return mysqli_query($conexion,$sql);
    }

    public function eliminaCategoria($idcategoria){ 
        $c= new conectar();
        $conexion=$c->conexion(); 
        $sql="UPDATE categorias set estado=0
                  where id_categoria='$idcategoria'";
        return mysqli_query($conexion,$sql);
    }

}
?>