<?php
class Proyecto {
    public function agregaProyecto($datos) {
        $c= new conectar();
        $conexion=$c->conexion();
        // Validar nombre duplicado
        $sqlCheck = "SELECT COUNT(*) as existe FROM proyecto WHERE LOWER(nombre) = LOWER(?) AND estado = 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $datos[0]);
        $stmtCheck->execute();
        $stmtCheck->bind_result($existe);
        $stmtCheck->fetch();
        $stmtCheck->close();
        if ($existe > 0) {
            return "EXISTE";
        }
        $sql = "INSERT INTO proyecto (nombre, estado, fecha_registro) VALUES (?, 1, NOW())";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $datos[0]);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    public function actualizaProyecto($datos) {
        $c= new conectar();
        $conexion=$c->conexion();
        // Validar nombre duplicado (excluyendo el actual)
        $sqlCheck = "SELECT COUNT(*) as existe FROM proyecto WHERE LOWER(nombre) = LOWER(?) AND id != ? AND estado = 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $datos[1], $datos[0]);
        $stmtCheck->execute();
        $stmtCheck->bind_result($existe);
        $stmtCheck->fetch();
        $stmtCheck->close();
        if ($existe > 0) {
            return "EXISTE";
        }
        $sql = "UPDATE proyecto SET nombre = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $datos[1], $datos[0]);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    public function eliminaProyecto($id) {
        $c= new conectar();
        $conexion=$c->conexion(); 
        $sql = "UPDATE proyecto SET estado = 0 WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    public function obtenerProyectos() {
        $c= new conectar();
        $conexion=$c->conexion(); 
        $sql = "SELECT * FROM proyecto WHERE estado = 1 ORDER BY id DESC";
        $result = mysqli_query($conexion, $sql);
        $datos = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $datos[] = $row;
        }
        return $datos;
    }
}
