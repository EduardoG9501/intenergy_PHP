<?php
class Iva {
    public function listar() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_iva, monto, CODIGO_SRI, Codigo_Porc_Iva FROM iva";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public function editar($data) {
        $c = new conectar();
        $conexion = $c->conexion();
        $id_iva = intval($data['id_iva']);
        $monto = floatval($data['monto']);
        $CODIGO_SRI = mysqli_real_escape_string($conexion, $data['CODIGO_SRI']);
        $Codigo_Porc_Iva = mysqli_real_escape_string($conexion, $data['Codigo_Porc_Iva']);
        $sql = "UPDATE iva SET monto='$monto', CODIGO_SRI='$CODIGO_SRI', Codigo_Porc_Iva='$Codigo_Porc_Iva' WHERE id_iva = $id_iva";
        return mysqli_query($conexion, $sql);
    }
}
?>