<?php 
	class EjecucionObra {
		// Genera movimiento SALIDA/DESCARGO y contabiliza los detalles
		public function contabilizarYGenerarMovimiento($id_ejecucion_obra, $usuario) {
		$c = new conectar();
		$conexion = $c->conexion();
		// 1. Obtener detalles no contabilizados
		$sql_det = "SELECT ed.*, a.precio, a.nombre as articulo_nombre, eo.id_orden, eo.fecha_solicitud, eo.observacion, eo.id_ejecucion_obra, eo.id_estado_ejecucion_obra, ot.id_bodega_principal
					FROM ejecucion_obra_detalle_materiales_utilizar ed
					JOIN articulos a ON a.id_producto = ed.id_producto
					JOIN ejecucion_obra eo ON eo.id_ejecucion_obra = ed.id_ejecucion_obra
					JOIN ordenes_trabajo ot ON eo.id_orden = ot.id_orden
					WHERE ed.id_ejecucion_obra = $id_ejecucion_obra AND ed.Contabilizado = 0 AND ed.Estado = 1";
		$res_det = mysqli_query($conexion, $sql_det);
		$detalles = [];
		while($row = mysqli_fetch_assoc($res_det)) {
			$detalles[] = $row;
		}
		if (count($detalles) == 0) {
			return ['success' => false, 'mensaje' => 'No hay detalles para contabilizar.'];
		}
		// 2. Validar stock
		$articulos_faltantes = [];
		foreach($detalles as $art) {
			$id_producto = $art['id_producto'];
			$id_bodega = $art['id_bodega_lugar'];
			$cantidad = $art['cantidad'];
			$stock = $this->obtenerStockDisponible($id_producto, $id_bodega);
			if ($stock < $cantidad) {
				$articulos_faltantes[] = $art['articulo_nombre'];
			}
		}
		if (count($articulos_faltantes) > 0) {
			return ['success' => false, 'mensaje' => 'No hay stock suficiente para: ' . implode(', ', $articulos_faltantes)];
		}
		// 3. Obtener id_usuario
		$id_usuario = null;
		$sqlUser = "SELECT id_usuario FROM usuarios WHERE email = '".mysqli_real_escape_string($conexion, $usuario)."' LIMIT 1";
		$resUser = mysqli_query($conexion, $sqlUser);
		if ($rowUser = mysqli_fetch_assoc($resUser)) {
			$id_usuario = intval($rowUser['id_usuario']);
		}
		if (!$id_usuario) {
			return ['success' => false, 'mensaje' => 'No se pudo asociar el usuario al movimiento.'];
		}
		// 4. Agrupar detalles por bodega y generar un movimiento por cada bodega
		$detalles_por_bodega = [];
		foreach ($detalles as $art) {
			$detalles_por_bodega[$art['id_bodega_lugar']][] = $art;
		}
		$ids_movimientos_generados = [];
		foreach ($detalles_por_bodega as $id_bodega_lugar => $detalles_bodega) {
			$id_tipo_movimiento = 2; // SALIDA
			$id_sub_tipo_movimiento = 3; // DESCARGO
			$id_bodega_principal = $id_bodega_lugar;
			$no_documento = 'EJEC-'.$id_ejecucion_obra.'-BOD-'.$id_bodega_lugar;
			$fechaDocumento = date('Y-m-d');
			$observacion = 'MOVIMIENTO AUTOMATICO DE EJECUCION DE OBRA #'.$id_ejecucion_obra.' BODEGA '.$id_bodega_lugar;
			$sql_mov = "INSERT INTO movimientos (id_tipo_movimiento, id_sub_tipo_movimiento, id_bodega, no_documento, fechaDocumento, observacion, estado, guardarDefinitivo, id_usuario) VALUES ($id_tipo_movimiento, $id_sub_tipo_movimiento, $id_bodega_principal, '$no_documento', '$fechaDocumento', '$observacion', 1, 1, $id_usuario)";
			mysqli_query($conexion, $sql_mov);
			$id_movimiento = mysqli_insert_id($conexion);
			$ids_movimientos_generados[] = $id_movimiento;
			// Insertar detalles solo de esta bodega
			foreach($detalles_bodega as $art) {
				$id_producto = $art['id_producto'];
				$cantidad = $art['cantidad'];
				$precio = floatval($art['precio']);
				$subtotal = $cantidad * $precio;
				$total = $subtotal;
				$sql_mov_det = "INSERT INTO movimientos_detalle (id_movimiento, id_articulo, id_bodega_lugar, precio, cantidad, lote, bonificacion, subtotal, decuento, iva, total, iva_porc, desc_porc, estado) VALUES ($id_movimiento, $id_producto, $id_bodega_principal, $precio, $cantidad, NULL, NULL, $subtotal, 0, 0, $total, 0, 0, 1)";
				mysqli_query($conexion, $sql_mov_det);
			}
			// Actualizar totales del movimiento
			$sql_sum = "SELECT SUM(subtotal) AS subtotal_mto, SUM(total) AS total_mto FROM movimientos_detalle WHERE id_movimiento = $id_movimiento";
			$res_sum = mysqli_query($conexion, $sql_sum);
			$row_sum = mysqli_fetch_assoc($res_sum);
			$subtotal_mto = $row_sum ? floatval($row_sum['subtotal_mto']) : 0;
			$total_mto = $row_sum ? floatval($row_sum['total_mto']) : 0;
			$sql_update_mov = "UPDATE movimientos SET subtotal_mto = $subtotal_mto, total_mto = $total_mto WHERE id_movimiento = $id_movimiento";
			mysqli_query($conexion, $sql_update_mov);
			// Insertar en stock_productos
			$fecha = date('Y-m-d');
			$sql2 = "INSERT into stock_productos (id_movimiento, id_tipo_movimiento, id_sub_tipo_movimiento, no_documento, id_bodega_principal, id_bodega_secundaria, tipo_movimiento, sub_tipo_movimiento, id_movimiento_detalle, id_producto, producto, lote, precio, cantidad, subtotal, decuento, iva, total, stock, id_stock_credito, id_usuario, fecha_captura) SELECT DISTINCT det.id_movimiento, mov.id_tipo_movimiento, mov.id_sub_tipo_movimiento, mov.no_documento, mov.id_bodega idBodegaPrincipal, det.id_bodega_lugar IdBodegaSecundaria, tm.tipo TipoMovimiento, stm.sub_tipo SubTipoMovimiento, det.id_movimiento_detalle, art.id_producto, art.nombre, det.lote, det.precio, det.cantidad, det.subtotal, det.decuento, det.iva, det.total, det.cantidad stock, 0 id_stock_credito, mov.id_usuario, '$fecha' fecha_captura from movimientos_detalle as det inner join movimientos mov on det.id_movimiento = mov.id_movimiento inner join tipo_movimiento tm on mov.id_tipo_movimiento = tm.id_tipo_movimiento inner join sub_tipo_movimiento stm on mov.id_sub_tipo_movimiento = stm.id_sub_tipo_movimiento inner join articulos art on det.id_articulo= art.id_producto inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega where det.id_movimiento='$id_movimiento'";
			mysqli_query($conexion,$sql2);
			// Actualizar stock de articulos
			$sql10 = "CALL actualizar_cantidad_articulos_por_movimiento($id_movimiento)";
			mysqli_query($conexion, $sql10);
		}
		// 6. Insertar en stock_productos
		$fecha = date('Y-m-d');
		$sql2 = "INSERT into stock_productos (id_movimiento, id_tipo_movimiento, id_sub_tipo_movimiento, no_documento, id_bodega_principal, id_bodega_secundaria, tipo_movimiento, sub_tipo_movimiento, id_movimiento_detalle, id_producto, producto, lote, precio, cantidad, subtotal, decuento, iva, total, stock, id_stock_credito, id_usuario, fecha_captura) SELECT DISTINCT det.id_movimiento, mov.id_tipo_movimiento, mov.id_sub_tipo_movimiento, mov.no_documento, mov.id_bodega idBodegaPrincipal, det.id_bodega_lugar IdBodegaSecundaria, tm.tipo TipoMovimiento, stm.sub_tipo SubTipoMovimiento, det.id_movimiento_detalle, art.id_producto, art.nombre, det.lote, det.precio, det.cantidad, det.subtotal, det.decuento, det.iva, det.total, det.cantidad stock, 0 id_stock_credito, mov.id_usuario, '$fecha' fecha_captura from movimientos_detalle as det inner join movimientos mov on det.id_movimiento = mov.id_movimiento inner join tipo_movimiento tm on mov.id_tipo_movimiento = tm.id_tipo_movimiento inner join sub_tipo_movimiento stm on mov.id_sub_tipo_movimiento = stm.id_sub_tipo_movimiento inner join articulos art on det.id_articulo= art.id_producto inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega where det.id_movimiento='$id_movimiento'";
		$resultadoStock =  mysqli_query($conexion,$sql2);
		// 7. Actualizar totales del movimiento
		$sql_sum = "SELECT SUM(subtotal) AS subtotal_mto, SUM(total) AS total_mto FROM movimientos_detalle WHERE id_movimiento = $id_movimiento";
		$res_sum = mysqli_query($conexion, $sql_sum);
		$row_sum = mysqli_fetch_assoc($res_sum);
		$subtotal_mto = $row_sum ? floatval($row_sum['subtotal_mto']) : 0;
		$total_mto = $row_sum ? floatval($row_sum['total_mto']) : 0;
		$sql_update_mov = "UPDATE movimientos SET subtotal_mto = $subtotal_mto, total_mto = $total_mto WHERE id_movimiento = $id_movimiento";
		mysqli_query($conexion, $sql_update_mov);
		// 8. Actualizar stock de articulos
		$sql10 = "CALL actualizar_cantidad_articulos_por_movimiento($id_movimiento)";
		mysqli_query($conexion, $sql10);
		// 9. Marcar detalles como contabilizados
		$sql_cont = "UPDATE ejecucion_obra_detalle_materiales_utilizar SET Contabilizado = 1 WHERE id_ejecucion_obra = $id_ejecucion_obra AND Estado = 1";
		mysqli_query($conexion, $sql_cont);
		return ['success' => true, 'mensaje' => 'Movimiento generado y detalles contabilizados.'];
	}
	       // Obtener stock disponible por producto y bodega (igual a movimientos.php)
	       private function obtenerStockDisponible($idProducto, $idBodega) {
		       $c = new conectar();
		       $conexion = $c->conexion();
		       $sql = "SELECT cantidad FROM stock_productos_bodega WHERE id_producto = '$idProducto' AND id_bodega_principal = '$idBodega' AND estado = 1";
		       // Eliminar cualquier echo, print o var_dump aquí
		       $result = mysqli_query($conexion, $sql);
		       $row = mysqli_fetch_assoc($result);
		       return $row ? floatval($row['cantidad']) : 0;
	       }
	// --- CRUD principal ---
	public function listar() {
		$c = new conectar();
		$conexion = $c->conexion();
		$where = '';
		if (isset($_GET['id_orden']) && is_numeric($_GET['id_orden'])) {
			$id_orden = intval($_GET['id_orden']);
			$where = " WHERE eo.id_orden = $id_orden ";
		}
		$sql = "SELECT eo.*, ot.identificador AS orden_identificador, ot.observacion AS orden_descripcion, eeo.estado AS estado_nombre
				FROM ejecucion_obra eo
				JOIN ordenes_trabajo ot ON eo.id_orden = ot.id_orden
				JOIN estado_ejecucion_obra eeo ON eo.id_estado_ejecucion_obra = eeo.id_estado_ejecucion_obra
				$where
				ORDER BY eo.id_ejecucion_obra DESC";
		$result = mysqli_query($conexion, $sql);
		$datos = [];
		while ($fila = mysqli_fetch_assoc($result)) {
			$datos[] = $fila;
		}
		return $datos;
	}

	// --- CRUD de detalle ---
	public function listarDetalle($id_ejecucion_obra) {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT ed.*, a.nombre as articulo_nombre, b.nombreBodega FROM ejecucion_obra_detalle_materiales_utilizar ed JOIN articulos a ON ed.id_producto = a.id_producto JOIN bodegas b ON ed.id_bodega_lugar = b.id_bodega WHERE ed.id_ejecucion_obra = $id_ejecucion_obra";
		$result = mysqli_query($conexion, $sql);
		$datos = [];
		while ($fila = mysqli_fetch_assoc($result)) {
			$datos[] = $fila;
		}
		return $datos;
	}

	public function guardarDetalle($data) {
		$c = new conectar();
		$conexion = $c->conexion();
		// Validar stock antes de guardar (solo para salida de materiales)
		$id_producto = $data['id_producto'];
		$id_bodega_lugar = $data['id_bodega_lugar'] ?? null;
		$cantidad = $data['cantidad'];
		if ($id_bodega_lugar) {
			$stockDisponible = $this->obtenerStockDisponible($id_producto, $id_bodega_lugar);
			if ($cantidad > $stockDisponible) {
				return [
					'success' => false,
					'mensaje' => 'Stock insuficiente en la bodega seleccionada. Disponible: ' . $stockDisponible
				];
			}
		}
		if (!empty($data['id_ejecucion_obra_detalle_materiales_utilizar'])) {
			$sql = "UPDATE ejecucion_obra_detalle_materiales_utilizar SET id_producto='{$data['id_producto']}', cantidad='{$data['cantidad']}', id_bodega_lugar='{$data['id_bodega_lugar']}' WHERE id_ejecucion_obra_detalle_materiales_utilizar={$data['id_ejecucion_obra_detalle_materiales_utilizar']}";
			$ok = mysqli_query($conexion, $sql);
			return [
				'success' => $ok,
				'id_ejecucion_obra_detalle_materiales_utilizar' => $data['id_ejecucion_obra_detalle_materiales_utilizar'],
				'mensaje' => $ok ? 'Detalle actualizado correctamente' : 'Error al actualizar detalle'
			];
		} else {
			$sql = "INSERT INTO ejecucion_obra_detalle_materiales_utilizar (id_ejecucion_obra, id_producto, cantidad, id_bodega_lugar, Estado) VALUES ('{$data['id_ejecucion_obra']}', '{$data['id_producto']}', '{$data['cantidad']}', '{$data['id_bodega_lugar']}', 1)";
			$ok = mysqli_query($conexion, $sql);
			$id = mysqli_insert_id($conexion);
			return [
				'success' => $ok,
				'id_ejecucion_obra_detalle_materiales_utilizar' => $id,
				'mensaje' => $ok ? 'Detalle guardado correctamente' : 'Error al guardar detalle'
			];
		}
	}

	public function eliminarDetalle($id) {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "DELETE FROM ejecucion_obra_detalle_materiales_utilizar WHERE id_ejecucion_obra_detalle_materiales_utilizar = $id";
		$ok = mysqli_query($conexion, $sql);
		return [
			'success' => $ok,
			'mensaje' => $ok ? 'Detalle eliminado correctamente' : 'Error al eliminar detalle'
		];
	}

	public function obtener($id) {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT * FROM ejecucion_obra WHERE id_ejecucion_obra = $id";
		$result = mysqli_query($conexion, $sql);
		return mysqli_fetch_assoc($result);
	}

	public function guardar($data) {
		$c = new conectar();
		$conexion = $c->conexion();
		if (!empty($data['id_ejecucion_obra'])) {
				$sql = "UPDATE ejecucion_obra SET
					id_orden='{$data['id_orden']}',
					fecha_solicitud='{$data['fecha_solicitud']}',
					feriado={$data['feriado']},
					observacion='{$data['observacion']}'";
			if (isset($data['id_estado_ejecucion_obra'])) {
				$sql .= ", id_estado_ejecucion_obra='{$data['id_estado_ejecucion_obra']}'";
			}
			$sql .= " WHERE id_ejecucion_obra={$data['id_ejecucion_obra']}";
			mysqli_query($conexion, $sql);
			return $data['id_ejecucion_obra'];
		} else {
				$sql = "INSERT INTO ejecucion_obra (
					id_orden, fecha_solicitud, feriado, observacion, id_estado_ejecucion_obra
				) VALUES (
					'{$data['id_orden']}', '{$data['fecha_solicitud']}', {$data['feriado']}, '{$data['observacion']}', 1
				)";
            mysqli_query($conexion, $sql);
			return mysqli_insert_id($conexion);
		}
	}

	public function estados() {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT id_estado_ejecucion_obra, estado FROM estado_ejecucion_obra WHERE activo = 1";
		$result = mysqli_query($conexion, $sql);
		$datos = [];
		while ($fila = mysqli_fetch_assoc($result)) {
			$datos[] = $fila;
		}
		return $datos;
	}

	public function ordenes() {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT id_orden, identificador FROM ordenes_trabajo WHERE estado = 1";
		$result = mysqli_query($conexion, $sql);
		$datos = [];
		while ($fila = mysqli_fetch_assoc($result)) {
			$datos[] = $fila;
		}
		return $datos;
	}

	public function articulos() {
		$c = new conectar();
		$conexion = $c->conexion();
		$sql = "SELECT id_producto, nombre FROM articulos WHERE estado = 1";
		$result = mysqli_query($conexion, $sql);
		$datos = [];
		while ($fila = mysqli_fetch_assoc($result)) {
			$datos[] = $fila;
		}
		return $datos;
	}
}
