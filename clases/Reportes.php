<?php
class Reporte {

    // Listar bodegas activas para combos de Lugar y Ubicación
    public function listarBodegas() {
        $c = new conectar();
        $conexion = $c->conexion();
        $bodegas = [];
        $sql = "SELECT id_bodega, nombreBodega FROM bodegas ORDER BY nombreBodega";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $bodegas[] = $row;
        return $bodegas;
    }

    // Listar reporte de órdenes de trabajo según filtros
    public function listarReporteOrdenes($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        if (!empty($filtros['fecha_inicial'])) {
            $w[] = "o.fecha_inicial >= '".mysqli_real_escape_string($conexion, $filtros['fecha_inicial'])."'";
        }
        if (!empty($filtros['fecha_final'])) {
            $w[] = "o.fecha_final <= '".mysqli_real_escape_string($conexion, $filtros['fecha_final'])."'";
        }
        if (!empty($filtros['lugar'])) {
            $w[] = "o.id_bodega_principal = '".intval($filtros['lugar'])."'";
        }
        if (!empty($filtros['ubicacion'])) {
            $w[] = "o.id_bodega_secundaria = '".intval($filtros['ubicacion'])."'";
        }
        if (!empty($filtros['estado'])) {
            $w[] = "o.id_estado_orden = '".mysqli_real_escape_string($conexion, $filtros['estado'])."'";
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

      $sql = "SELECT o.id_orden, o.costo, o.fecha_inicial, o.fecha_final, 
                bp.nombreBodega AS lugar, bs.nombreBodega AS ubicacion, o.observacion, est.estado
                FROM ordenes_trabajo o
                LEFT JOIN bodegas bp ON o.id_bodega_principal = bp.id_bodega
                LEFT JOIN bodegas bs ON o.id_bodega_secundaria = bs.id_bodega
                LEFT JOIN estado_ordenes est ON o.id_estado_orden = est.id_estado_orden
                $where
                ORDER BY o.id_orden DESC";
        $result = mysqli_query($conexion, $sql);
        $ordenes = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $ordenes[] = $fila;
        }
        return $ordenes;
    }
    // Listar estados de solicitud de materiales
    public function listarEstadosSolicitudMateriales() {
        $c = new conectar();
        $conexion = $c->conexion();
        $estados = [];
        $sql = "SELECT id_estado_solicitud, estado FROM estado_solicitud_materiales ORDER BY id_estado_solicitud asc";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $estados[] = $row;
        return $estados;
    }
    // Listar estados del artículo en la solicitud
    public function listarEstadosSolicitudMaterialesArticulos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $estados = [];
        $sql = "SELECT id_estado_solicitud_articulos, estado FROM estado_solicitud_materiales_articulos ORDER BY id_estado_solicitud_articulos asc";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $estados[] = $row;
        return $estados;
    }

    // Listar artículos activos para el combo
    public function listarArticulos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $articulos = [];
        $sql = "SELECT id_producto, nombre FROM articulos WHERE estado=1 ORDER BY nombre";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $articulos[] = $row;
        return $articulos;
    }

    // Listar reporte de solicitudes de materiales según filtros
    public function listarReporteSolicitudesMateriales($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        if (!empty($filtros['fecha_desde'])) {
            $w[] = "sm.fecha_solicitud >= '".mysqli_real_escape_string($conexion, $filtros['fecha_desde'])."'";
        }
        if (!empty($filtros['fecha_hasta'])) {
            $w[] = "sm.fecha_solicitud <= '".mysqli_real_escape_string($conexion, $filtros['fecha_hasta'])."'";
        }
        if (!empty($filtros['id_bodega_principal'])) {
            $w[] = "sm.id_bodega_principal = '".intval($filtros['id_bodega_principal'])."'";
        }
        if (!empty($filtros['id_bodega_secundaria'])) {
            $w[] = "sm.id_bodega_secundaria = '".intval($filtros['id_bodega_secundaria'])."'";
        }
        if (!empty($filtros['id_estado'])) {
            $w[] = "sm.id_estado_solicitud = '".intval($filtros['id_estado'])."'";
        }
        if (!empty($filtros['id_articulo'])) {
            $w[] = "d.id_producto = '".intval($filtros['id_articulo'])."'";
        }
        if (!empty($filtros['id_estado_articulo'])) {
            $w[] = "d.id_detalle_articulo = '".intval($filtros['id_estado_articulo'])."'";
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "SELECT 
            sm.id_solicitud_material,
            sm.fecha_solicitud,
            bp.nombreBodega AS bodega_principal,
            bs.nombreBodega AS bodega_secundaria,
            a.nombre AS articulo,
            d.cantidad,
            esm.estado AS estado_solicitud,
            esa.estado AS estado_solicitud_articulo
        FROM solicitud_materiales sm
        LEFT JOIN bodegas bp ON sm.id_bodega_principal = bp.id_bodega
        LEFT JOIN bodegas bs ON sm.id_bodega_secundaria = bs.id_bodega
        LEFT JOIN solicitud_materiales_detalle d ON d.id_solicitud_material = sm.id_solicitud_material
        INNER JOIN articulos a ON d.id_producto = a.id_producto
        LEFT JOIN estado_solicitud_materiales_articulos esa ON d.id_detalle_articulo = esa.id_estado_solicitud_articulos
        LEFT JOIN estado_solicitud_materiales esm ON sm.id_estado_solicitud = esm.id_estado_solicitud
        $where
        ORDER BY sm.id_solicitud_material DESC";
        $result = mysqli_query($conexion, $sql);
        $solicitudes = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $solicitudes[] = $fila;
        }
        return $solicitudes;
    }
    // Listar empleados internos activos
    public function listarEmpleadosInternos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $empleados = [];
        $sql = "SELECT id_empleados,  nombres_apellidos nombre FROM empleados  ORDER BY nombres_apellidos";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $empleados[] = $row;
        return $empleados;
    }

    // Listar empleados externos activos
    public function listarEmpleadosExternos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $empleados = [];
        $sql = "SELECT  id_empleados id_empleado_externo,  nombres_apellidos nombre FROM empleados ORDER BY nombres_apellidos";
        $rs = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_assoc($rs)) $empleados[] = $row;
        return $empleados;
    } 
    // Consulta principal de informes diarios avanzado
    public function listarReporteInformesDiariosAvanzado($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        if (!empty($filtros['fecha_desde'])) $w[] = "it.fecha >= '".mysqli_real_escape_string($conexion, $filtros['fecha_desde'])."'";
        if (!empty($filtros['fecha_hasta'])) $w[] = "it.fecha <= '".mysqli_real_escape_string($conexion, $filtros['fecha_hasta'])."'";
        if (!empty($filtros['id_bodega_principal'])) $w[] = "it.id_bodega_principal = '".intval($filtros['id_bodega_principal'])."'";
        if (!empty($filtros['id_bodega_secundaria'])) $w[] = "it.id_bodega_secundaria = '".intval($filtros['id_bodega_secundaria'])."'";

        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";
        $resultados = [];

        // filtro por tipo
        if ($filtros['tipo_informe'] == 'interno') {
            $extra = "";
            if (!empty($filtros['id_empleado'])) $extra = "AND ite.id_empleado = '".intval($filtros['id_empleado'])."'";
            $sql = "SELECT 
                        it.id_informe,
                        it.fecha,
                        bp.nombreBodega AS bodega_principal,
                        bs.nombreBodega AS bodega_secundaria,
                        e.nombres_apellidos AS empleado,
                        it.observacion,
                        ot.identificador
                    FROM informe_trabajo it
                    LEFT JOIN bodegas bp ON it.id_bodega_principal = bp.id_bodega
                    LEFT JOIN bodegas bs ON it.id_bodega_secundaria = bs.id_bodega
                    LEFT JOIN informe_trabajo_empleado ite ON ite.id_informe = it.id_informe
                    INNER JOIN empleados e ON ite.id_empleado = e.id_empleados
                    INNER JOIN ordenes_trabajo ot on it.id_orden = ot.id_orden
                    $where $extra
                    ORDER BY it.fecha DESC, it.id_informe DESC";
            $rs = mysqli_query($conexion, $sql);
            while ($row = mysqli_fetch_assoc($rs)) $resultados[] = $row;
        } elseif ($filtros['tipo_informe'] == 'externo') {
            $extra = "";
            if (!empty($filtros['id_empleado_externo'])) $extra = "AND itee.id_empleado_externo = '".intval($filtros['id_empleado_externo'])."'";
            $sql = "SELECT 
                        it.id_informe,
                        it.fecha,
                        bp.nombreBodega AS bodega_principal,
                        bs.nombreBodega AS bodega_secundaria,
                        ee.nombres_apellidos AS empleado_externo,
                        it.observacion,
                        ot.identificador
                    FROM informe_trabajo it
                    LEFT JOIN bodegas bp ON it.id_bodega_principal = bp.id_bodega
                    LEFT JOIN bodegas bs ON it.id_bodega_secundaria = bs.id_bodega
                    LEFT JOIN informe_trabajo_empleado_externo itee ON itee.id_informe = it.id_informe
                    INNER JOIN empleados ee ON itee.id_empleado_externo = ee.id_empleados
                    INNER JOIN ordenes_trabajo ot on it.id_orden = ot.id_orden
                    $where $extra
                    ORDER BY it.fecha DESC, it.id_informe DESC";
            $rs = mysqli_query($conexion, $sql);
            while ($row = mysqli_fetch_assoc($rs)) $resultados[] = $row;
        } elseif ($filtros['tipo_informe'] == 'articulo') {
            $extra = "";
            if (!empty($filtros['id_articulo'])) $extra = "AND ita.id_producto = '".intval($filtros['id_articulo'])."'";
            $sql = "SELECT 
                        it.id_informe,
                        it.fecha,
                        bp.nombreBodega AS bodega_principal,
                        bs.nombreBodega AS bodega_secundaria,
                        a.nombre AS articulo,
                        ita.cantidad,
                        it.observacion,
                        ot.identificador
                    FROM informe_trabajo it
                    LEFT JOIN bodegas bp ON it.id_bodega_principal = bp.id_bodega
                    LEFT JOIN bodegas bs ON it.id_bodega_secundaria = bs.id_bodega
                    LEFT JOIN informe_trabajo_articulo ita ON ita.id_informe = it.id_informe
                    INNER JOIN articulos a ON ita.id_producto = a.id_producto
                    INNER JOIN ordenes_trabajo ot on it.id_orden = ot.id_orden
                    $where $extra
                    ORDER BY it.fecha DESC, it.id_informe DESC";
            $rs = mysqli_query($conexion, $sql);
            while ($row = mysqli_fetch_assoc($rs)) $resultados[] = $row;
        }
        return $resultados;
    }
      // Listar reporte de horas de trabajo según filtros
    public function listarReporteHorasTrabajo($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        if (!empty($filtros['fecha_desde'])) {
            $w[] = "ht.fecha >= '".mysqli_real_escape_string($conexion, $filtros['fecha_desde'])."'";
        }
        if (!empty($filtros['fecha_hasta'])) {
            $w[] = "ht.fecha <= '".mysqli_real_escape_string($conexion, $filtros['fecha_hasta'])."'";
        }
        if (!empty($filtros['id_bodega_principal'])) {
            $w[] = "ht.id_bodega_principal = '".intval($filtros['id_bodega_principal'])."'";
        }
        if (!empty($filtros['id_bodega_secundaria'])) {
            $w[] = "ht.id_bodega_secundaria = '".intval($filtros['id_bodega_secundaria'])."'";
        }
        if (!empty($filtros['id_responsable_obra'])) {
            $w[] = "ht.id_responsable_obra = '".intval($filtros['id_responsable_obra'])."'";
        }
        if (!empty($filtros['id_empleado'])) {
            $w[] = "dh.id_empleado = '".intval($filtros['id_empleado'])."'";
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "SELECT 
            dh.id_horas_trabajo_empleado,
            ht.fecha,
            bp.nombreBodega AS bodega_principal,
            bs.nombreBodega AS bodega_secundaria,
            e.nombres_apellidos AS responsable,
            emp.nombres_apellidos AS empleado,
            dh.numero_horas,
            dh.costo_por_hora,
            dh.total,
            CASE WHEN dh.activo = 1 THEN 'Activo' ELSE 'Eliminado' END AS estado
        FROM horas_trabajo ht
        LEFT JOIN bodegas bp ON ht.id_bodega_principal = bp.id_bodega
        LEFT JOIN bodegas bs ON ht.id_bodega_secundaria = bs.id_bodega
        LEFT JOIN empleados e ON ht.id_responsable_obra = e.id_empleados
        LEFT JOIN horas_trabajo_empleado dh ON ht.id_horas_trabajo = dh.id_horas_trabajo
        LEFT JOIN empleados emp ON dh.id_empleado = emp.id_empleados
        $where
        ORDER BY dh.id_horas_trabajo_empleado DESC";

        $result = mysqli_query($conexion, $sql);
        $horas = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $horas[] = $fila;
        }
        return $horas;
    }
     // Listar disponibilidad de artículos por bodega según filtros
    public function listarDisponibilidadBodega($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = ["spb.estado = 1"];
        if (!empty($filtros['id_bodega'])) {
            $w[] = "spb.id_bodega_principal = '".intval($filtros['id_bodega'])."'";
        }
        if (!empty($filtros['id_producto'])) {
            $w[] = "spb.id_producto = '".intval($filtros['id_producto'])."'";
        }
        $where = count($w) ? "WHERE spb.cantidad >0 and ".implode(" AND ", $w) : "";

        $sql = "SELECT
                    spb.id_producto,
                    a.nombre AS nombre_articulo,
                    spb.id_bodega_principal AS id_bodega,
                    b.nombreBodega,
                    round(spb.cantidad,2) cantidad
                FROM stock_productos_bodega spb
                INNER JOIN articulos a ON a.id_producto = spb.id_producto
                INNER JOIN bodegas b ON b.id_bodega = spb.id_bodega_principal
                $where
                ORDER BY b.nombreBodega, a.nombre";
        $result = mysqli_query($conexion, $sql);
        $disponibilidad = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $disponibilidad[] = $row;
        }
        return $disponibilidad;
    }
     // Listar reporte general de informes diarios agrupado por informe
    public function listarReporteGeneralInformesDiarios($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        if (!empty($filtros['fecha_desde'])) $w[] = "it.fecha >= '".mysqli_real_escape_string($conexion, $filtros['fecha_desde'])."'";
        if (!empty($filtros['fecha_hasta'])) $w[] = "it.fecha <= '".mysqli_real_escape_string($conexion, $filtros['fecha_hasta'])."'";
        if (!empty($filtros['id_bodega_principal'])) $w[] = "it.id_bodega_principal = '".intval($filtros['id_bodega_principal'])."'";
        if (!empty($filtros['id_bodega_secundaria'])) $w[] = "it.id_bodega_secundaria = '".intval($filtros['id_bodega_secundaria'])."'";
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "
            SELECT 
                it.id_informe,
                ot.identificador,
                it.fecha,
                bp.nombreBodega AS bodega_principal,
                bs.nombreBodega AS bodega_secundaria,
                it.lugar,
                it.observacion
            FROM informe_trabajo it
            LEFT JOIN bodegas bp ON it.id_bodega_principal = bp.id_bodega
            LEFT JOIN bodegas bs ON it.id_bodega_secundaria = bs.id_bodega
            LEFT JOIN ordenes_trabajo ot ON it.id_orden = ot.id_orden
            $where
            ORDER BY it.fecha DESC, it.id_informe DESC
        ";
        $rs = mysqli_query($conexion, $sql);
        $resultados = [];
        while ($row = mysqli_fetch_assoc($rs)) {
            // Artículos
            $articulos = [];
            $rs2 = mysqli_query($conexion,
                "SELECT a.nombre, ia.cantidad, ia.lote
                 FROM informe_trabajo_articulo ia
                 INNER JOIN articulos a ON ia.id_producto = a.id_producto
                 WHERE ia.id_informe = {$row['id_informe']}"
            );
            while($a = mysqli_fetch_assoc($rs2)) $articulos[] = $a;

            // Empleados internos
            $empleados_internos = [];
            $rs3 = mysqli_query($conexion,
                "SELECT ei.nombres_apellidos
                 FROM informe_trabajo_empleado ite
                 INNER JOIN empleados ei ON ite.id_empleado = ei.id_empleados
                 WHERE ite.id_informe = {$row['id_informe']}"
            );
            while($e = mysqli_fetch_assoc($rs3)) $empleados_internos[] = $e['nombres_apellidos'];

            // Empleados externos
            $empleados_externos = [];
            $rs4 = mysqli_query($conexion,
                "SELECT ee.nombres_apellidos
                 FROM informe_trabajo_empleado_externo itee
                 INNER JOIN empleados ee ON itee.id_empleado_externo = ee.id_empleados
                 WHERE itee.id_informe = {$row['id_informe']}"
            );
            while($e = mysqli_fetch_assoc($rs4)) $empleados_externos[] = $e['nombres_apellidos'];

            // Imágenes
            $imagenes = [];
            $rs5 = mysqli_query($conexion,
                "SELECT ruta FROM informe_trabajo_imagen WHERE id_informe = {$row['id_informe']}"
            );
            while($img = mysqli_fetch_assoc($rs5)) $imagenes[] = $img['ruta'];

            $row['articulos'] = $articulos;
            $row['empleados_internos'] = $empleados_internos;
            $row['empleados_externos'] = $empleados_externos;
            $row['imagenes'] = $imagenes;
            $resultados[] = $row;
        }
        return $resultados;
    }
}
?>