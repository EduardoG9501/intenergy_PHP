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
                   ot.fecha_inicial,
                   p.nombre as proyecto_nombre,
                   o.nombre as obra_nombre,
                   bp.nombreBodega as bodega_principal,
                   eo.estado as estado_nombre
            FROM ordenes_trabajo ot
            LEFT JOIN proyecto p ON ot.id_proyecto = p.id
            LEFT JOIN obra o ON ot.id_obra = o.id
            LEFT JOIN bodegas bp ON ot.id_bodega_principal = bp.id_bodega
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
        // Asignar valores por defecto si no existen en $data
        $data['costo'] = isset($data['costo']) ? $data['costo'] : 0;
        // La fecha de inicio SIEMPRE debe tomar el valor enviado en el campo 'fecha' del formulario
        $data['fecha_inicial'] = isset($data['fecha']) ? $data['fecha'] : NULL;
        $data['fecha_final'] = isset($data['fecha_final']) ? $data['fecha_final'] : NULL;
        $data['id_bodega_secundaria'] = isset($data['id_bodega_secundaria']) ? $data['id_bodega_secundaria'] : NULL;
        $data['observacion'] = isset($data['observacion']) ? $data['observacion'] : '';
        if (!empty($data['id_orden'])) {
            // Editar
            $id_orden = intval($data['id_orden']);
            $sqlActual = "SELECT id_bodega_principal FROM ordenes_trabajo WHERE id_orden = $id_orden";
            $resActual = mysqli_query($conexion, $sqlActual);
            $rowActual = mysqli_fetch_assoc($resActual);
            $identificadorUpdate = "";
            if ($rowActual && $rowActual['id_bodega_principal'] != $data['id_bodega_principal']) {
                $identificadorUpdate = $this->generarIdentificadorPorBodega($data['id_bodega_principal']);
            }
            $sql = "UPDATE ordenes_trabajo SET
                id_proyecto='{$data['id_proyecto']}',
                id_obra='{$data['id_obra']}',
                fecha_inicial='{$data['fecha']}',
                id_bodega_principal='{$data['id_bodega_principal']}',
                id_estado_orden='{$data['id_estado_orden']}',
                observacion='" . mysqli_real_escape_string($conexion, $data['observacion']) . "'";
            if ($identificadorUpdate != "") {
                $sql .= ", identificador='$identificadorUpdate'";
            }
            // Asegurarse de actualizar el estado si se envía
            if (isset($data['estado'])) {
                $sql .= ", estado='{$data['estado']}'";
            }
            $sql .= " WHERE id_orden=" . intval($data['id_orden']);
            mysqli_query($conexion, $sql);
            // Obtener identificador actualizado
            $sqlGet = "SELECT identificador FROM ordenes_trabajo WHERE id_orden = $id_orden";
            $resGet = mysqli_query($conexion, $sqlGet);
            $rowGet = mysqli_fetch_assoc($resGet);
            return [
                'mensaje' => 'Orden editada correctamente',
                'id_orden' => $id_orden,
                'identificador' => $rowGet ? $rowGet['identificador'] : ''
            ];
        } else {
            // Insertar
            $id_bodega = $data['id_bodega_principal'];
            $sqlBodega = "SELECT identificador FROM bodegas WHERE id_bodega = $id_bodega";
            $resBodega = mysqli_query($conexion, $sqlBodega);
            $rowBodega = mysqli_fetch_assoc($resBodega);
            if (!$rowBodega || empty($rowBodega['identificador'])) {
                return ['mensaje' => 'No se encontró la bodega seleccionada o no tiene identificador'];
            }
            $prefijo = strtoupper(trim($rowBodega['identificador']));
            $sqlCount = "SELECT COUNT(*) as total FROM ordenes_trabajo WHERE id_bodega_principal = $id_bodega";
            $resCount = mysqli_query($conexion, $sqlCount);
            $rowCount = mysqli_fetch_assoc($resCount);
            $correlativo = intval($rowCount['total']) + 1;
            if ($correlativo < 100) {
                $identificador = $prefijo . str_pad($correlativo, 3, '0', STR_PAD_LEFT);
            } else {
                $identificador = $prefijo . $correlativo;
            }
            $sql = "INSERT INTO ordenes_trabajo (identificador, id_proyecto, id_obra, fecha_inicial, id_bodega_principal, id_estado_orden, estado, observacion)
                VALUES ('$identificador', '{$data['id_proyecto']}', '{$data['id_obra']}', '{$data['fecha']}', '{$data['id_bodega_principal']}', '{$data['id_estado_orden']}', 1, '" . mysqli_real_escape_string($conexion, $data['observacion']) . "')";
            mysqli_query($conexion, $sql);
            $id_orden = mysqli_insert_id($conexion);
            return [
                'mensaje' => 'Orden agregada correctamente',
                'id_orden' => $id_orden,
                'identificador' => $identificador,
                'success' => true
            ];
        }
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