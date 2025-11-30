<?php 

class cargosempleos{

    public function agregaCargo($datos){
        $c = new conectar();
        $conexion = $c->conexion();

        // Limpiar espacios del nombre del cargo
        $cargo = trim($datos[1]);

        // Validar si el cargo ya existe (ignorando espacios)
        $sql = "SELECT COUNT(*) FROM cargo_empleado WHERE TRIM(cargo) = ? and estado = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $cargo);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();

        if ($existe > 0) {
            return 2; // Cargo repetido
        }

        $sql="INSERT into cargo_empleado(id_usuario,
                                        cargo,
                                        fechaCaptura)
                        values (?, ?, ?)";
        $stmtInsert = $conexion->prepare($sql);
        $stmtInsert->bind_param("iss", $datos[0], $cargo, $datos[2]);
        $result = $stmtInsert->execute();
        $stmtInsert->close();

        return $result ? 1 : 0;
    }

    public function actualizaCargo($datos){
        $c = new conectar();
        $conexion = $c->conexion();

        $cargo = trim($datos[1]);
        $idcargo = $datos[0];

        // Validar si el cargo ya existe en otro registro
        $sql = "SELECT COUNT(*) FROM cargo_empleado WHERE TRIM(cargo) = ? AND id_cargo != ? and estado = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $cargo, $idcargo);
        $stmt->execute();
        $stmt->bind_result($existe);
        $stmt->fetch();
        $stmt->close();

        if ($existe > 0) {
            return 2; // Cargo repetido
        }

        $sql="UPDATE cargo_empleado set cargo=? where id_cargo=?";
        $stmtUpdate = $conexion->prepare($sql);
        $stmtUpdate->bind_param("si", $cargo, $idcargo);
        $result = $stmtUpdate->execute();
        $stmtUpdate->close();

        return $result ? 1 : 0;
    }

    public function eliminaCargo($idcargo){ 
        $c = new conectar();
        $conexion = $c->conexion();

        $sql = "UPDATE cargo_empleado set estado=0 where id_cargo=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idcargo);
        $result = $stmt->execute();
        $stmt->close();

        return $result; 
    }

    public function inactivaCargo($idcargo){
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "UPDATE cargo_empleado set estado=0 where id_cargo=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idcargo);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}

?>