<?php
class Obra {
    public function agregaObra($datos) {
        $c= new conectar();
        $conexion=$c->conexion();
        // Validar nombre duplicado
        $sqlCheck = "SELECT COUNT(*) as existe FROM obra WHERE LOWER(nombre) = LOWER(?) AND estado = 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $datos[0]);
        $stmtCheck->execute();
        $stmtCheck->bind_result($existe);
        $stmtCheck->fetch();
        $stmtCheck->close();
        if ($existe > 0) {
            return "EXISTE";
        }
        $sql = "INSERT INTO obra (nombre, estado, fecha_registro) VALUES (?, 1, NOW())";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $datos[0]);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    public function actualizaObra($datos) {
        $c= new conectar();
        $conexion=$c->conexion();
        // Validar nombre duplicado (excluyendo el actual)
        $sqlCheck = "SELECT COUNT(*) as existe FROM obra WHERE LOWER(nombre) = LOWER(?) AND id != ? AND estado = 1";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $datos[1], $datos[0]);
        $stmtCheck->execute();
        $stmtCheck->bind_result($existe);
        $stmtCheck->fetch();
        $stmtCheck->close();
        if ($existe > 0) {
            return "EXISTE";
        }
        $sql = "UPDATE obra SET nombre = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $datos[1], $datos[0]);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    public function eliminaObra($id) {
        $c= new conectar();
        $conexion=$c->conexion(); 
        $sql = "UPDATE obra SET estado = 0 WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    public function obtenerObras() {
        $c= new conectar();
        $conexion=$c->conexion();
        $sql = "SELECT id, nombre, estado, fecha_registro FROM obra WHERE estado = 1 ORDER BY id DESC";
        $result = $conexion->query($sql);
        $datos = array();
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        return $datos;
    }
}
