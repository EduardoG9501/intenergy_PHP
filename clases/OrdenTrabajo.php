<?php
$PAIS = "ec"; // "ec" para Ecuador, "ni" para Nicaragua

if ($PAIS == "ec") {
    date_default_timezone_set('America/Guayaquil');
} else if ($PAIS == "ni") {
    date_default_timezone_set('America/Managua');
}  

class OrdenTrabajo { 
    public function listar($id_bodega = null) {
        $c = new conectar();
        $conexion = $c->conexion();

        $filtro_bodega = "";
        if ($id_bodega) {
            $filtro_bodega = "WHERE ot.id_bodega_principal = '".intval($id_bodega)."'";
        }

        $sql = "SELECT ot.id_orden,
                       ot.identificador,
                       ot.costo,
                       ot.fecha_inicial,
                       ot.fecha_final,
                       bp.nombreBodega as bodega_principal,
                       bs.nombreBodega as bodega_secundaria,
                       eo.estado as estado_nombre
                FROM ordenes_trabajo ot
                LEFT JOIN bodegas bp ON ot.id_bodega_principal = bp.id_bodega
                LEFT JOIN bodegas bs ON ot.id_bodega_secundaria = bs.id_bodega
                LEFT JOIN estado_ordenes eo ON ot.id_estado_orden = eo.id_estado_orden   
                $filtro_bodega
                ORDER BY ot.id_orden DESC";
        $result = mysqli_query($conexion, $sql);
        $ordenes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ordenes[] = $row;
        }
        return $ordenes;
    }

    public function obtener($id) {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT * FROM ordenes_trabajo WHERE id_orden = $id";
        $result = mysqli_query($conexion, $sql);
        return mysqli_fetch_assoc($result);
    }

    public function guardar($data) {
        $c = new conectar();
        $conexion = $c->conexion();
        if (!empty($data['id_orden'])) {
            // Editar

            // 1. Consultar la orden actual para comparar la bodega principal
            $id_orden = intval($data['id_orden']);
            $sqlActual = "SELECT id_bodega_principal FROM ordenes_trabajo WHERE id_orden = $id_orden";
            $resActual = mysqli_query($conexion, $sqlActual);
            $rowActual = mysqli_fetch_assoc($resActual);

            $identificadorUpdate = ""; // Por defecto, no cambia

            if ($rowActual && $rowActual['id_bodega_principal'] != $data['id_bodega_principal']) {
                // La bodega principal cambió: generar nuevo identificador
                $identificadorUpdate = $this->generarIdentificadorPorBodega($data['id_bodega_principal']);
            }

            // Armar query de actualización
            $sql = "UPDATE ordenes_trabajo SET
                costo='{$data['costo']}',
                fecha_inicial='{$data['fecha_inicial']}',
                fecha_final='{$data['fecha_final']}',
                id_bodega_principal='{$data['id_bodega_principal']}',
                id_bodega_secundaria='{$data['id_bodega_secundaria']}',
                observacion='{$data['observacion']}',
                id_estado_orden='{$data['id_estado_orden']}'";
            // Si hay nuevo identificador, lo actualizamos
            if ($identificadorUpdate != "") {
                $sql .= ", identificador='$identificadorUpdate'";
            }
            $sql .= " WHERE id_orden={$data['id_orden']}";
            mysqli_query($conexion, $sql);
            return ['mensaje' => 'Orden editada correctamente'];
        } else {
            // Insertar
            $id_bodega = $data['id_bodega_principal'];

            // 1. Obtener identificador (prefijo) actual de la bodega
            $sqlBodega = "SELECT identificador FROM bodegas WHERE id_bodega = $id_bodega";
            $resBodega = mysqli_query($conexion, $sqlBodega);
            $rowBodega = mysqli_fetch_assoc($resBodega);

            if (!$rowBodega || empty($rowBodega['identificador'])) {
                return ['mensaje' => 'No se encontró la bodega seleccionada o no tiene identificador'];
            }

            $prefijo = strtoupper(trim($rowBodega['identificador']));

            // 2. Contar el número de órdenes para esa bodega principal
            $sqlCount = "SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_bodega_principal = $id_bodega";
            $resCount = mysqli_query($conexion, $sqlCount);
            $rowCount = mysqli_fetch_assoc($resCount);

            $correlativo = intval($rowCount['total']) + 1;
            // Lógica variante: 001-099, luego sigue natural
            if ($correlativo < 100) {
                $identificador = $prefijo . str_pad($correlativo, 3, '0', STR_PAD_LEFT);
            } else {
                $identificador = $prefijo . $correlativo;
            }

            $sql = "INSERT INTO ordenes_trabajo (identificador, costo, fecha_inicial, fecha_final, id_bodega_principal, id_bodega_secundaria, observacion, id_estado_orden, estado)
                    VALUES ('$identificador', '{$data['costo']}', '{$data['fecha_inicial']}', '{$data['fecha_final']}', '{$data['id_bodega_principal']}', '{$data['id_bodega_secundaria']}', '{$data['observacion']}', '{$data['id_estado_orden']}', 1)";
            mysqli_query($conexion, $sql);
            return ['mensaje' => 'Orden agregada correctamente'];
        }
    }

    // Generar identificador único según la bodega principal (para edición y creación)
    public function generarIdentificadorPorBodega($id_bodega) {
        $c = new conectar();
        $conexion = $c->conexion();

        // 1. Obtener identificador (prefijo) actual de la bodega
        $sqlBodega = "SELECT identificador FROM bodegas WHERE id_bodega = $id_bodega";
        $resBodega = mysqli_query($conexion, $sqlBodega);
        $rowBodega = mysqli_fetch_assoc($resBodega);

        if (!$rowBodega || empty($rowBodega['identificador'])) return '';

        $prefijo = strtoupper(trim($rowBodega['identificador']));

        // 2. Contar el número de órdenes para esa bodega principal
        $sqlCount = "SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_bodega_principal = $id_bodega";
        $resCount = mysqli_query($conexion, $sqlCount);
        $rowCount = mysqli_fetch_assoc($resCount);

        $correlativo = intval($rowCount['total']) + 1;
        if ($correlativo < 100) {
            $identificador = $prefijo . str_pad($correlativo, 3, '0', STR_PAD_LEFT);
        } else {
            $identificador = $prefijo . $correlativo;
        }
        return $identificador;
    }
    
    public function bodegas() {
    $c = new conectar();
    $conexion = $c->conexion();
    // Incluir los campos necesarios para filtrar en JS
    $sql = "SELECT id_bodega, nombrebodega nombre, es_bodega_secundaria, id_bodega_principal FROM bodegas where estado = 1";
    $result = mysqli_query($conexion, $sql);
    $datos = [];
    while ($fila = mysqli_fetch_assoc($result)) {
        // Forzar a int para mayor control en JS luego
        $fila['es_bodega_secundaria'] = intval($fila['es_bodega_secundaria']);
        $fila['id_bodega_principal'] = $fila['id_bodega_principal'] !== null ? intval($fila['id_bodega_principal']) : null;
        $datos[] = $fila;
    }
    return $datos;
}

    public function estados() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_estado_orden, estado FROM estado_ordenes";
        $result = mysqli_query($conexion, $sql);
        $datos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $datos[] = $fila;
        }
        return $datos;
    }
}
?>