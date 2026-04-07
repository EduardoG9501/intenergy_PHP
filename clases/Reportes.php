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
        if (!empty($filtros['desde'])) {
            $w[] = "o.fecha_inicial >= '".mysqli_real_escape_string($conexion, $filtros['desde'])."'";
        }
        if (!empty($filtros['hasta'])) {
            $w[] = "o.fecha_inicial <= '".mysqli_real_escape_string($conexion, $filtros['hasta'])."'";
        }
        if (!empty($filtros['lugar'])) {
            $w[] = "o.id_bodega_principal = '".intval($filtros['lugar'])."'";
        }
        if (!empty($filtros['proyecto'])) {
            $w[] = "o.id_proyecto = '".intval($filtros['proyecto'])."'";
        }
        if (!empty($filtros['obra'])) {
            $w[] = "o.id_obra = '".intval($filtros['obra'])."'";
        }
        if (!empty($filtros['estado'])) {
            $w[] = "o.id_estado_orden = '".mysqli_real_escape_string($conexion, $filtros['estado'])."'";
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "SELECT o.id_orden, o.costo, o.fecha_inicial, 
            p.nombre AS proyecto, ob.nombre AS obra, 
            bp.nombreBodega AS lugar, o.observacion, est.estado
            FROM ordenes_trabajo o
            LEFT JOIN bodegas bp ON o.id_bodega_principal = bp.id_bodega
            LEFT JOIN estado_ordenes est ON o.id_estado_orden = est.id_estado_orden
            LEFT JOIN proyecto p ON o.id_proyecto = p.id
            LEFT JOIN obra ob ON o.id_obra = ob.id
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
                    INNER JOIN ejecucion_obra eo on it.id_ejecucion_obra = eo.id_ejecucion_obra
                    INNER JOIN ordenes_trabajo ot on ot.id_orden = eo.id_orden
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
                    INNER JOIN ejecucion_obra eo on it.id_ejecucion_obra = eo.id_ejecucion_obra
                    INNER JOIN ordenes_trabajo ot on ot.id_orden = eo.id_orden
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
                    INNER JOIN ejecucion_obra eo on it.id_ejecucion_obra = eo.id_ejecucion_obra
                    INNER JOIN ordenes_trabajo ot on ot.id_orden = eo.id_orden
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
            INNER JOIN ejecucion_obra eo on it.id_ejecucion_obra = eo.id_ejecucion_obra
           INNER JOIN ordenes_trabajo ot on ot.id_orden = eo.id_orden
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
     // Listar proyectos para filtro
    public function listarProyectos() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id, nombre FROM proyecto WHERE estado = 1 ORDER BY nombre";
        $result = mysqli_query($conexion, $sql);
        $proyectos = [];
        while($row = mysqli_fetch_assoc($result)) {
            $proyectos[] = $row;
        }
        return $proyectos;
    }

    // Listar obras para filtro (opcionalmente por proyecto)
    public function listarObras($proyecto = '') {
        $c = new conectar();
        $conexion = $c->conexion();
        $where = '';
        if ($proyecto != '') {
            $where = "WHERE estado = 1 AND id IN (SELECT id_obra FROM ordenes_trabajo WHERE id_proyecto = '".intval($proyecto)."')";
        } else {
            $where = "WHERE estado = 1";
        }
        $sql = "SELECT id, nombre FROM obra $where ORDER BY nombre";
        $result = mysqli_query($conexion, $sql);
        $obras = [];
        while($row = mysqli_fetch_assoc($result)) {
            $obras[] = $row;
        }
        return $obras;
    }

    // Listar lugares (bodegas) para filtro
    public function listarLugares() {
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT id_bodega, nombreBodega FROM bodegas WHERE estado = 1 ORDER BY nombreBodega";
        $result = mysqli_query($conexion, $sql);
        $lugares = [];
        while($row = mysqli_fetch_assoc($result)) {
            $lugares[] = $row;
        }
        return $lugares;
    }

    // Reporte principal de pedido de materiales
    public function reportePedidoMateriales($desde, $hasta, $proyecto, $obra, $articulo, $lugar, $idOrden = '') {
        $c = new conectar();
        $conexion = $c->conexion();
        $where = ["pm.id_estado_pedido_material = 1"];
        $idOrden = trim($idOrden);

        if ($idOrden !== '') {
            $idOrdenEscapado = mysqli_real_escape_string($conexion, $idOrden);
            $condicionOrden = "ot.identificador = '$idOrdenEscapado'";
            if (ctype_digit($idOrden)) {
                $condicionOrden = "($condicionOrden OR ot.id_orden = " . intval($idOrden) . ")";
            }
            $where[] = $condicionOrden;
        } else {
            if ($desde != '' && $hasta != '') {
                $where[] = "pm.fecha_solicitud BETWEEN '".mysqli_real_escape_string($conexion, $desde)."' AND '".mysqli_real_escape_string($conexion, $hasta)."'";
            }
            if ($proyecto != '') {
                $where[] = "ot.id_proyecto = '".intval($proyecto)."'";
            }
            if ($obra != '') {
                $where[] = "ot.id_obra = '".intval($obra)."'";
            }
            if ($articulo != '') {
                $where[] = "pmd.id_producto = '".intval($articulo)."'";
            }
            if ($lugar != '') {
                $where[] = "ot.id_bodega_principal = '".intval($lugar)."'";
            }
        }
        $whereSql = count($where) > 0 ? ('WHERE '.implode(' AND ', $where)) : '';
        $sql = "
            SELECT
                ot.identificador as id_orden,
                pr.nombre as proyecto,
                ob.nombre as obra,
                pm.fecha_solicitud as fecha,
                bd.nombreBodega as lugar,
                ar.nombre as articulo,
                pmd.cantidad as cantidad
            FROM pedido_materiales pm
            INNER JOIN pedido_materiales_detalle pmd ON pm.id_pedido_material = pmd.id_pedido_material
            INNER JOIN ordenes_trabajo ot ON pm.id_orden = ot.id_orden
            INNER JOIN proyecto pr ON ot.id_proyecto = pr.id
            INNER JOIN obra ob ON ot.id_obra = ob.id
            INNER JOIN bodegas bd ON ot.id_bodega_principal = bd.id_bodega
            INNER JOIN articulos ar ON pmd.id_producto = ar.id_producto
            $whereSql
            ORDER BY ar.nombre ASC, pm.fecha_solicitud DESC, ot.identificador DESC
        ";
        $result = mysqli_query($conexion, $sql);
        $rows = [];
        $totalGeneral = 0;
        $lastArticulo = null;
        $subtotal = 0;
        while($row = mysqli_fetch_assoc($result)) {
            $art = $row['articulo'];
            if ($lastArticulo !== null && $art !== $lastArticulo) {
                // Fila subtotal del artículo anterior
                $rows[] = [
                    'subtotal' => true,
                    'articulo' => $lastArticulo,
                    'cantidad' => $subtotal
                ];
                $subtotal = 0;
            }
            $rows[] = [
                'id_orden' => $row['id_orden'],
                'proyecto' => $row['proyecto'],
                'obra' => $row['obra'],
                'fecha' => $row['fecha'],
                'lugar' => $row['lugar'],
                'articulo' => $row['articulo'],
                'cantidad' => $row['cantidad']
            ];
            $subtotal += floatval($row['cantidad']);
            $totalGeneral += floatval($row['cantidad']);
            $lastArticulo = $art;
        }
        if ($lastArticulo !== null) {
            $rows[] = [
                'subtotal' => true,
                'articulo' => $lastArticulo,
                'cantidad' => $subtotal
            ];
        }
        $rows[] = [
            'total_general' => true,
            'cantidad' => $totalGeneral
        ];
        return $rows;
    }
    // Reporte de Materiales Ejecución de Obra
    public function reporteMaterialesEjecucionObra($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        $idOrden = isset($filtros['id_orden']) ? trim($filtros['id_orden']) : '';
        if ($idOrden !== '') {
            $idOrdenEscapado = mysqli_real_escape_string($conexion, $idOrden);
            $condicionOrden = "o.identificador = '$idOrdenEscapado'";
            if (ctype_digit($idOrden)) {
                $condicionOrden = "($condicionOrden OR o.id_orden = " . intval($idOrden) . ")";
            }
            $w[] = $condicionOrden;
        } else {
            if (!empty($filtros['proyecto'])) {
                $w[] = "p.id = '".intval($filtros['proyecto'])."'";
            }
            if (!empty($filtros['obra'])) {
                $w[] = "ob.id = '".intval($filtros['obra'])."'";
            }
            if (!empty($filtros['fecha_inicial'])) {
                $w[] = "eo.fecha_solicitud >= '".mysqli_real_escape_string($conexion, $filtros['fecha_inicial'])."'";
            }
            if (!empty($filtros['fecha_final'])) {
                $w[] = "eo.fecha_solicitud <= '".mysqli_real_escape_string($conexion, $filtros['fecha_final'])."'";
            }
            if (!empty($filtros['bodega'])) {
                $w[] = "b.id_bodega = '".intval($filtros['bodega'])."'";
            }
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "SELECT o.identificador, p.nombre AS proyecto, ob.nombre AS obra, eo.fecha_solicitud AS fecha, b.nombreBodega AS lugar, a.nombre AS articulo, edmu.cantidad AS cantidad, edmu.Contabilizado AS contabilizado
            FROM ejecucion_obra eo
            INNER JOIN ordenes_trabajo o ON eo.id_orden = o.id_orden
            INNER JOIN ejecucion_obra_detalle_materiales_utilizar edmu ON eo.id_ejecucion_obra = edmu.id_ejecucion_obra
            INNER JOIN articulos a ON edmu.id_producto = a.id_producto
            INNER JOIN bodegas b ON edmu.id_bodega_lugar = b.id_bodega
            INNER JOIN proyecto p ON o.id_proyecto = p.id
            INNER JOIN obra ob ON o.id_obra = ob.id
            $where
            ORDER BY a.nombre ASC, eo.fecha_solicitud DESC, o.identificador DESC";
        $result = mysqli_query($conexion, $sql);
        $rows = [];
        $totalGeneral = 0;
        $lastArticulo = null;
        $subtotal = 0;
        foreach ($result as $row) {
            $row['contabilizado'] = ($row['contabilizado'] == 1) ? 'SI' : 'NO';
            $art = $row['articulo'];
            if ($lastArticulo !== null && $art !== $lastArticulo) {
                // Fila subtotal del artículo anterior
                $rows[] = [
                    'subtotal' => true,
                    'articulo' => $lastArticulo,
                    'cantidad' => $subtotal
                ];
                $subtotal = 0;
            }
            $rows[] = $row;
            $subtotal += floatval($row['cantidad']);
            $totalGeneral += floatval($row['cantidad']);
            $lastArticulo = $art;
        }
        if ($lastArticulo !== null) {
            $rows[] = [
                'subtotal' => true,
                'articulo' => $lastArticulo,
                'cantidad' => $subtotal
            ];
        }
        $rows[] = [
            'total_general' => true,
            'cantidad' => $totalGeneral
        ];
        return $rows;
    }

    // Reporte de Horas de Trabajo Ejecución de Obra
    public function reporteHorasTrabajoEjecucionObra($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        $idOrden = isset($filtros['id_orden']) ? trim($filtros['id_orden']) : '';
        if ($idOrden !== '') {
            $idOrdenEscapado = mysqli_real_escape_string($conexion, $idOrden);
            $condicionOrden = "ot.identificador = '$idOrdenEscapado'";
            if (ctype_digit($idOrden)) {
                $condicionOrden = "($condicionOrden OR ot.id_orden = " . intval($idOrden) . ")";
            }
            $w[] = $condicionOrden;
        } else {
            if (!empty($filtros['proyecto'])) {
                $w[] = "p.id = '".intval($filtros['proyecto'])."'";
            }
            if (!empty($filtros['obra'])) {
                $w[] = "ob.id = '".intval($filtros['obra'])."'";
            }
            if (!empty($filtros['fecha_inicial'])) {
                $w[] = "eo.fecha_solicitud >= '".mysqli_real_escape_string($conexion, $filtros['fecha_inicial'])."'";
            }
            if (!empty($filtros['fecha_final'])) {
                $w[] = "eo.fecha_solicitud <= '".mysqli_real_escape_string($conexion, $filtros['fecha_final'])."'";
            }
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "SELECT ot.identificador AS id_orden, p.nombre AS proyecto, ob.nombre AS obra, eo.fecha_solicitud AS fecha, b.nombreBodega AS lugar, e.nombres_apellidos AS responsable, htd.hora_entrada, htd.hora_salida, htd.cantidad_horas_normal AS h_normales, htd.cantidad_horas_extra AS h_extras, htd.cantidad_horas_extraordinaria AS h_extraordinarias
                FROM ejecucion_obra eo
                INNER JOIN ordenes_trabajo ot ON eo.id_orden = ot.id_orden
                INNER JOIN proyecto p ON ot.id_proyecto = p.id
                INNER JOIN obra ob ON ot.id_obra = ob.id
                INNER JOIN bodegas b ON ot.id_bodega_principal = b.id_bodega
                INNER JOIN horas_trabajo_diario_detalle htd ON eo.id_ejecucion_obra = htd.id_ejecucion_obra
                INNER JOIN empleados e ON htd.id_empleado = e.id_empleados
                $where
                ORDER BY eo.id_ejecucion_obra DESC";
        $result = mysqli_query($conexion, $sql);
        $horas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $horas[] = $row;
        }
        return $horas;
    }

    // Reporte de Informes Diarios Ejecución de Obra
    public function reporteInformesDiariosEjecucionObra($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();
        $w = [];
        $idOrden = isset($filtros['id_orden']) ? trim($filtros['id_orden']) : '';
        if ($idOrden !== '') {
            $idOrdenEscapado = mysqli_real_escape_string($conexion, $idOrden);
            $condicionOrden = "ot.identificador = '$idOrdenEscapado'";
            if (ctype_digit($idOrden)) {
                $condicionOrden = "($condicionOrden OR ot.id_orden = " . intval($idOrden) . ")";
            }
            $w[] = $condicionOrden;
        } else {
            if (!empty($filtros['proyecto'])) {
                $w[] = "p.id = '".intval($filtros['proyecto'])."'";
            }
            if (!empty($filtros['obra'])) {
                $w[] = "ob.id = '".intval($filtros['obra'])."'";
            }
            if (!empty($filtros['fecha_inicial'])) {
                $w[] = "eo.fecha_solicitud >= '".mysqli_real_escape_string($conexion, $filtros['fecha_inicial'])."'";
            }
            if (!empty($filtros['fecha_final'])) {
                $w[] = "eo.fecha_solicitud <= '".mysqli_real_escape_string($conexion, $filtros['fecha_final'])."'";
            }
        }
        $where = count($w) ? "WHERE ".implode(" AND ", $w) : "";

        $sql = "SELECT it.id_informe, ob.nombre AS nombre_obra, ot.identificador AS orden_trabajo, it.fecha,
            bp.nombreBodega AS lugar,
            bs.nombreBodega AS ubicacion,
            it.observacion, it.descripcion
            FROM informe_trabajo it
            INNER JOIN ejecucion_obra eo ON it.id_ejecucion_obra = eo.id_ejecucion_obra
            INNER JOIN ordenes_trabajo ot ON eo.id_orden = ot.id_orden
            INNER JOIN proyecto p ON ot.id_proyecto = p.id
            INNER JOIN obra ob ON ot.id_obra = ob.id
            LEFT JOIN bodegas bs ON it.id_bodega_secundaria = bs.id_bodega
             LEFT JOIN bodegas bp ON it.id_bodega_principal = bp.id_bodega
            $where
            ORDER BY it.id_informe DESC";
        $result = mysqli_query($conexion, $sql);
        $informes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $idInforme = intval($row['id_informe']);

            $articulos = [];
            $sqlArticulos = "SELECT a.nombre, ita.cantidad, ita.lote
                            FROM informe_trabajo_articulo ita
                            INNER JOIN articulos a ON ita.id_producto = a.id_producto
                            WHERE ita.id_informe = $idInforme";
            $resultArticulos = mysqli_query($conexion, $sqlArticulos);
            while ($a = mysqli_fetch_assoc($resultArticulos)) {
                $articulos[] = $a;
            }

            $empleados = [];
            $sqlEmpleadosInternos = "SELECT e.nombres_apellidos
                                    FROM informe_trabajo_empleado ite
                                    INNER JOIN empleados e ON ite.id_empleado = e.id_empleados
                                    WHERE ite.id_informe = $idInforme";
            $resultEmpleadosInternos = mysqli_query($conexion, $sqlEmpleadosInternos);
            while ($e = mysqli_fetch_assoc($resultEmpleadosInternos)) {
                $empleados[] = $e['nombres_apellidos'];
            }

            $sqlEmpleadosExternos = "SELECT e.nombres_apellidos
                                    FROM informe_trabajo_empleado_externo itee
                                    INNER JOIN empleados e ON itee.id_empleado_externo = e.id_empleados
                                    WHERE itee.id_informe = $idInforme";
            $resultEmpleadosExternos = mysqli_query($conexion, $sqlEmpleadosExternos);
            while ($e = mysqli_fetch_assoc($resultEmpleadosExternos)) {
                $empleados[] = $e['nombres_apellidos'];
            }

            $imagenes = [];
            $sqlImagenes = "SELECT ruta FROM informe_trabajo_imagen WHERE id_informe = $idInforme";
            $resultImagenes = mysqli_query($conexion, $sqlImagenes);
            while ($img = mysqli_fetch_assoc($resultImagenes)) {
                $imagenes[] = $img['ruta'];
            }

            $row['obra'] = $row['nombre_obra'];
            $row['articulos'] = $articulos;
            $row['empleados'] = array_values(array_unique($empleados));
            $row['imagenes'] = $imagenes;
            $informes[] = $row;
        }
        return $informes;
    }

    public function obtenerLiquidacionTrabajo($filtros) {
        $c = new conectar();
        $conexion = $c->conexion();

        $proyecto = !empty($filtros['proyecto']) ? intval($filtros['proyecto']) : 0;
        $obra = !empty($filtros['obra']) ? intval($filtros['obra']) : 0;
        $bodega = !empty($filtros['bodega']) ? intval($filtros['bodega']) : 0;
        $fechaInicial = !empty($filtros['fecha_inicial']) ? mysqli_real_escape_string($conexion, $filtros['fecha_inicial']) : '';
        $fechaFinal = !empty($filtros['fecha_final']) ? mysqli_real_escape_string($conexion, $filtros['fecha_final']) : '';

        if ($fechaInicial === '' || $fechaFinal === '') {
            return ['success' => true, 'data' => null];
        }

        $sql = "SELECT id_liquidacion_trabajo, mano_obra, importante
                FROM liquidacion_trabajo
                WHERE fecha_inicial = '$fechaInicial'
                  AND fecha_final = '$fechaFinal'
                  AND COALESCE(id_proyecto, 0) = $proyecto
                  AND COALESCE(id_obra, 0) = $obra
                  AND COALESCE(id_bodega, 0) = $bodega
                  AND estado = 1
                ORDER BY id_liquidacion_trabajo DESC
                LIMIT 1";
        $rs = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($rs);

        if (!$row) {
            return ['success' => true, 'data' => null];
        }

        $idLiquidacion = intval($row['id_liquidacion_trabajo']);
        $imagenes = $this->listarImagenesLiquidacionTrabajo($idLiquidacion);

        return [
            'success' => true,
            'data' => [
                'id_liquidacion_trabajo' => $idLiquidacion,
                'mano_obra' => $row['mano_obra'],
                'importante' => $row['importante'],
                'imagenes' => $imagenes
            ]
        ];
    }

    public function guardarLiquidacionTrabajo($filtros, $manoObra, $importante, $idUsuario) {
        $c = new conectar();
        $conexion = $c->conexion();

        $idUsuario = intval($idUsuario);
        if ($idUsuario <= 0) {
            return ['success' => false, 'msg' => 'Usuario inválido para guardar liquidación'];
        }

        $proyecto = !empty($filtros['proyecto']) ? intval($filtros['proyecto']) : 0;
        $obra = !empty($filtros['obra']) ? intval($filtros['obra']) : 0;
        $bodega = !empty($filtros['bodega']) ? intval($filtros['bodega']) : 0;
        $fechaInicial = !empty($filtros['fecha_inicial']) ? mysqli_real_escape_string($conexion, $filtros['fecha_inicial']) : '';
        $fechaFinal = !empty($filtros['fecha_final']) ? mysqli_real_escape_string($conexion, $filtros['fecha_final']) : '';
        $manoObra = mysqli_real_escape_string($conexion, $manoObra);
        $importante = mysqli_real_escape_string($conexion, $importante);

        if ($fechaInicial === '' || $fechaFinal === '') {
            return ['success' => false, 'msg' => 'Debe especificar rango de fechas'];
        }

        $sqlExiste = "SELECT id_liquidacion_trabajo
                      FROM liquidacion_trabajo
                      WHERE fecha_inicial = '$fechaInicial'
                        AND fecha_final = '$fechaFinal'
                        AND COALESCE(id_proyecto, 0) = $proyecto
                        AND COALESCE(id_obra, 0) = $obra
                        AND COALESCE(id_bodega, 0) = $bodega
                        AND estado = 1
                      ORDER BY id_liquidacion_trabajo DESC
                      LIMIT 1";
        $rsExiste = mysqli_query($conexion, $sqlExiste);
        $rowExiste = mysqli_fetch_assoc($rsExiste);

        if ($rowExiste) {
            $idLiquidacion = intval($rowExiste['id_liquidacion_trabajo']);
            $sqlUpdate = "UPDATE liquidacion_trabajo
                          SET mano_obra = '$manoObra',
                              importante = '$importante',
                              id_usuario = $idUsuario,
                              fecha_actualizacion = NOW()
                          WHERE id_liquidacion_trabajo = $idLiquidacion";
            $ok = mysqli_query($conexion, $sqlUpdate);

            return [
                'success' => $ok,
                'id_liquidacion_trabajo' => $idLiquidacion,
                'msg' => $ok ? 'Liquidación actualizada' : 'No se pudo actualizar',
                'error' => $ok ? '' : mysqli_error($conexion)
            ];
        }

        $idProyectoSql = $proyecto > 0 ? $proyecto : 'NULL';
        $idObraSql = $obra > 0 ? $obra : 'NULL';
        $idBodegaSql = $bodega > 0 ? $bodega : 'NULL';

        $sqlInsert = "INSERT INTO liquidacion_trabajo
                        (id_usuario, id_proyecto, id_obra, id_bodega, fecha_inicial, fecha_final, mano_obra, importante, estado)
                      VALUES
                                                                                                ($idUsuario, $idProyectoSql, $idObraSql, $idBodegaSql, '$fechaInicial', '$fechaFinal', '$manoObra', '$importante', 1)";
        $okInsert = mysqli_query($conexion, $sqlInsert);
        $idLiquidacion = mysqli_insert_id($conexion); 
        return [
            'success' => $okInsert,
            'id_liquidacion_trabajo' => $idLiquidacion,
                        'msg' => $okInsert ? 'Liquidación guardada' : 'No se pudo guardar',
                        'error' => $okInsert ? '' : mysqli_error($conexion)
        ];
    }

    public function listarImagenesLiquidacionTrabajo($idLiquidacionTrabajo) {
        $c = new conectar();
        $conexion = $c->conexion();
        $idLiquidacionTrabajo = intval($idLiquidacionTrabajo);

        $sql = "SELECT id_liquidacion_trabajo_imagen, id_liquidacion_trabajo, nombre, ruta, fecha_subida
                FROM liquidacion_trabajo_imagen
                WHERE id_liquidacion_trabajo = $idLiquidacionTrabajo AND estado = 1
                ORDER BY id_liquidacion_trabajo_imagen DESC";
        $rs = mysqli_query($conexion, $sql);
        $imagenes = [];
        while ($row = mysqli_fetch_assoc($rs)) {
            $imagenes[] = $row;
        }
        return $imagenes;
    }

    public function agregarImagenLiquidacionTrabajo($idLiquidacionTrabajo, $nombre, $ruta) {
        $c = new conectar();
        $conexion = $c->conexion();

        $sql = "INSERT INTO liquidacion_trabajo_imagen
                    (id_liquidacion_trabajo, nombre, ruta, fecha_subida, estado)
                VALUES (?, ?, ?, NOW(), 1)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iss", $idLiquidacionTrabajo, $nombre, $ruta);
        $ok = $stmt->execute();
        $idImagen = $conexion->insert_id;
        $stmt->close();

        return ['success' => $ok, 'id_liquidacion_trabajo_imagen' => $idImagen];
    }

    public function obtenerRutaImagenLiquidacionTrabajo($idImagen) {
        $c = new conectar();
        $conexion = $c->conexion();
        $idImagen = intval($idImagen);

        $sql = "SELECT ruta FROM liquidacion_trabajo_imagen WHERE id_liquidacion_trabajo_imagen = $idImagen LIMIT 1";
        $rs = mysqli_query($conexion, $sql);
        $row = mysqli_fetch_assoc($rs);
        return $row ? $row['ruta'] : null;
    }

    public function eliminarImagenLiquidacionTrabajo($idImagen) {
        $c = new conectar();
        $conexion = $c->conexion();
        $idImagen = intval($idImagen);

        $sql = "UPDATE liquidacion_trabajo_imagen SET estado = 0 WHERE id_liquidacion_trabajo_imagen = $idImagen";
        return mysqli_query($conexion, $sql);
    }
}
?>