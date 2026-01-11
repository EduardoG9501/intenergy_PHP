<?php
class EjecucionObra {
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
		$sql = "SELECT ed.*, a.nombre as articulo_nombre FROM ejecucion_obra_detalle_materiales_utilizar ed JOIN articulos a ON ed.id_producto = a.id_producto WHERE ed.id_ejecucion_obra = $id_ejecucion_obra";
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
		if (!empty($data['id_ejecucion_obra_detalle_materiales_utilizar'])) {
			$sql = "UPDATE ejecucion_obra_detalle_materiales_utilizar SET id_producto='{$data['id_producto']}', cantidad='{$data['cantidad']}' WHERE id_ejecucion_obra_detalle_materiales_utilizar={$data['id_ejecucion_obra_detalle_materiales_utilizar']}";
			$ok = mysqli_query($conexion, $sql);
			return [
				'success' => $ok,
				'id_ejecucion_obra_detalle_materiales_utilizar' => $data['id_ejecucion_obra_detalle_materiales_utilizar'],
				'mensaje' => $ok ? 'Detalle actualizado correctamente' : 'Error al actualizar detalle'
			];
		} else {
			$sql = "INSERT INTO ejecucion_obra_detalle_materiales_utilizar (id_ejecucion_obra, id_producto, cantidad, Estado) VALUES ('{$data['id_ejecucion_obra']}', '{$data['id_producto']}', '{$data['cantidad']}', 1)";
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
