<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
require_once "../clases/Conexion.php";
if(isset($_SESSION['usuario'])){
	$id_ejecucion_obra = $_GET['id_ejecucion_obra'];
	$id_estado_ejecucion_obra = $_GET['id_estado_ejecucion_obra'];
	$idorden = isset($_GET['idorden']) ? $_GET['idorden'] : null;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Ejecución de Obra - MATERIAL A UTILIZAR</title>
	<?php require_once "menu.php"; ?>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
	<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
</head>
<body>
<div class="container">
	<button id="btnRegresar" class="btn btn-warning">&#8592; Regresar</button>
	<label class="label-custom">ID Ejecución: <?php echo  $id_ejecucion_obra;  ?> </label>  
	<label class="label-custom">ESTADO Ejecución: <?php if ($id_estado_ejecucion_obra === "1") { echo "EN PROCESO";} elseif ($id_estado_ejecucion_obra === "2") echo "FINALIZADA" ; ?> </label>  
	<button type="button" class="btn btn-primary" id="exportDetalleExcel"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
	<button type="button" class="btn btn-danger" id="imprimirDetallePDF"><i class="fa fa-file-pdf"></i> Imprimir PDF</button>
	<!-- Logo oculto para PDF -->
	<img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
	<hr>

		<div class="container p-3 my-2 bg-light" style="border: 3px solid #b0daf5; padding: 5px; background:#f7fcff; border-radius: 11px; margin-bottom: 10px;">
			<form id="formDetalle" enctype="multipart/form-data" class="form-horizontal" autocomplete="off" style="margin:0;">
				<input type="hidden" name="id_ejecucion_obra_detalle_materiales_utilizar" id="id_ejecucion_obra_detalle_materiales_utilizar">
				<input type="hidden" name="id_ejecucion_obra" value="<?= $id_ejecucion_obra ?>">
				   <div class="row">
					   <div class="col-sm-5">
						   <label>ARTÍCULO</label>
						<select class="form-control input-sm select2" id="id_producto" name="id_producto">
							<option value="A">Selecciona artículo</option>
						</select>
						   <label>UBICACIÓN</label>
						   <select style="color: #b95c00;" class="form-control input-sm select2" id="id_bodega_lugar" name="id_bodega_lugar">
							<option value="A">Selecciona la ubicación</option>
						</select>
					   </div>
					   <div class="col-sm-2">
						<label>CANTIDAD</label>
						<input style="color: #1a36e0;font-size: small;" type="number" class="form-control input-sm" id="cantidad" name="cantidad">
					</div>
					<div class="col-sm-5">
						   <label style="margin-top: 5px;">ACCIONES:</label>
						   <div>
							   <?php if ($id_estado_ejecucion_obra === "1"){ ?>
								   <button title="Agregar artículo" type="submit" id="btnGuardarDetalle" class="btn btn-info"><i class="glyphicon glyphicon-plus-sign"></i> AGREGAR</button>
							   <?php } ?>
							   <button title="Guardar todo" id="btnGuardarGeneral" class="btn btn-success" style="margin-left:8px;"><i class="glyphicon glyphicon-ok-sign"></i> GUARDAR</button>
							   <button type="button" id="btnCancelarDetalle" class="btn btn-danger" style="display:none; margin-left:8px;">CANCELAR</button>
						   </div>
					</div>
				</div>
			</form>
		</div>

	<hr>
	<div style="margin-top: 30px; margin-bottom: 10px;">
		<span style="font-size: 18px; font-weight: bold; color: #337ab7; border-bottom: 2px solid #337ab7; padding-bottom: 2px;">LISTA DE ARTÍCULOS:</span>
	</div>
	<table id="gridDetalle" class="display">
		<thead>
			<tr>
				<th>ID</th>
				<th>ARTÍCULO</th>
				<th>UBICACIÓN</th>
				<th>CANTIDAD</th>
				<th>ACCIÓN</th>
			</tr>
		</thead>
		<tbody>
			<!-- Se carga por JS -->
		</tbody>
	</table>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
<style>
table { width: 100%; border-collapse: collapse; margin-bottom: 30px;}
th, td { border: 1px solid #ccc; padding: 8px; text-align: center;}
th { background: #f5f5f5;}
#gridDetalle tbody tr:hover { background-color: #e0f7fa; cursor: pointer; transition: background 0.2s;}
label { display: flex; flex-direction: column; font-size: 14px;}
input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
</style>
<script type="text/javascript">
var idOrdenFiltro = <?php echo $idorden ? ('"' . $idorden . '"') : 'null'; ?>;
let detallesActuales = [];
let contabilizado = false;
// Verifica si ya está contabilizado para bloquear edición
function verificarContabilizado() {
	const id = <?= $id_ejecucion_obra ?>;
	fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=listar_detalle&id_ejecucion_obra=${id}`)
		.then(resp => resp.json())
		.then(data => {
			contabilizado = data.some(det => det.Contabilizado == 1);
			if (contabilizado) {
				document.getElementById('formDetalle').querySelectorAll('input, select, button').forEach(el => {
					if (el.id !== 'btnGuardarGeneral') el.disabled = true;
				});
			}
		});
}
document.addEventListener('DOMContentLoaded', () => {
	cargarArticulos();
	cargarBodegas();
	cargarDetalle();
	verificarContabilizado();
	// Cargar bodegas en el combo de ubicación
	function cargarBodegas() {
		fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=bodegas')
			.then(resp => resp.json())
			.then(data => {
				const sel = document.getElementById('id_bodega_lugar');
				sel.innerHTML = '<option value="A">Selecciona la ubicación</option>';
				data.forEach(b => {
					sel.innerHTML += `<option value="${b.id_bodega}">${b.nombreBodega}</option>`;
				});
				if (window.jQuery) {
					$('#id_bodega_lugar').select2();
				}
			});
	}
	if (window.jQuery) {
		$('#exportDetalleExcel').on('click', function(){
			exportarDetalleEjecucionAExcelConFormato();
		});
		$('#imprimirDetallePDF').on('click', function(){
			var resultados = obtenerDatosDeTablaDetalle('gridDetalle');
			exportarTablaDetalleAPDF(resultados);
		});
	} else {
		document.getElementById('exportDetalleExcel').addEventListener('click', exportarDetalleEjecucionAExcelConFormato);
		document.getElementById('imprimirDetallePDF').addEventListener('click', function(){
			var resultados = obtenerDatosDeTablaDetalle('gridDetalle');
			exportarTablaDetalleAPDF(resultados);
		});
	}
	
	document.getElementById('btnGuardarGeneral').onclick = function(){
		if (contabilizado) {
			Swal.fire({
				icon: 'info',
				title: 'Ya contabilizado',
				text: 'Los detalles ya han sido contabilizados y no se puede volver a guardar.'
			});
			return;
		}
		Swal.fire({
			title: '¿Está seguro?',
			text: 'Se generará el movimiento de salida y se contabilizarán los detalles. No podrá editar después.',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Sí, contabilizar',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php', {
					method: 'POST',
					body: new URLSearchParams({
						action: 'contabilizar',
						id_ejecucion_obra: <?= $id_ejecucion_obra ?>
					})
				})
				.then(resp => resp.json())
				.then(r => {
					if (r.success) {
						Swal.fire({ icon: 'success', title: 'Contabilizado', text: r.mensaje });
						contabilizado = true;
						document.getElementById('formDetalle').querySelectorAll('input, select, button').forEach(el => {
							if (el.id !== 'btnGuardarGeneral') el.disabled = true;
						});
					} else {
						Swal.fire({ icon: 'error', title: 'Error', text: r.mensaje });
					}
				});
			}
		});
	};
		 
	const formDetalle = document.getElementById('formDetalle');
	const btnCancelarDetalle = document.getElementById('btnCancelarDetalle');
	const btnRegresar = document.getElementById('btnRegresar');
	btnRegresar.onclick = () => {
		if (idOrdenFiltro) {
			window.location.href = `ejecucionobra.php?idorden=${idOrdenFiltro}`;
		} else {
			window.location.href = "ejecucionobra.php";
		}
	};
	formDetalle.onsubmit = function(e) {
				var cantidad = document.getElementById('cantidad').value;
				if (cantidad === "" || isNaN(cantidad) || parseFloat(cantidad) <= 0) {
					Swal.fire({
						title: 'Cantidad inválida',
						text: 'Debes ingresar una cantidad mayor a cero.',
						icon: 'warning',
						confirmButtonColor: '#7c3aed',
						confirmButtonText: 'OK'
					});
					return false;
				}
		e.preventDefault();
		var id_producto = document.getElementById('id_producto').value;
		var id_bodega_lugar = document.getElementById('id_bodega_lugar').value;
		if (id_producto === "A" || id_producto === "" || id_producto === null) {
			Swal.fire({
				title: 'Selecciona un artículo',
				text: 'Debes seleccionar un artículo antes de guardar.',
				icon: 'warning',
				confirmButtonColor: '#7c3aed',
				confirmButtonText: 'OK'
			});
			return false;
		}
		if (id_bodega_lugar === "A" || id_bodega_lugar === "" || id_bodega_lugar === null) {
			Swal.fire({
				title: 'Selecciona la ubicación',
				text: 'Debes seleccionar una ubicación antes de guardar.',
				icon: 'warning',
				confirmButtonColor: '#7c3aed',
				confirmButtonText: 'OK'
			});
			return false;
		}
		validarArticuloDuplicadoAntesDeGuardar(function() {
			validarStockAntesDeGuardar(function(stockOk) {
				if (stockOk) {
					guardarDetalle();
				}
			});
		});
	};

	// Validar stock antes de guardar (AJAX a backend)
	function validarStockAntesDeGuardar(callback) {
		var id_producto = document.getElementById('id_producto').value;
		var id_bodega_lugar = document.getElementById('id_bodega_lugar').value;
		var cantidad = parseFloat(document.getElementById('cantidad').value);
		fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=validar_stock&id_producto=${id_producto}&id_bodega=${id_bodega_lugar}&cantidad=${cantidad}`)
		.then(resp => resp.json())
		.then(data => {
			var stock = parseFloat(data.stock);
			if (!data.success || isNaN(stock) || cantidad > stock) {
				Swal.fire({
					icon: 'warning',
					title: 'STOCK INSUFICIENTE',
					text: 'No tiene suficiente stock en el artículo para realizar salidas.',
					confirmButtonColor: '#7c3aed',
					confirmButtonText: 'OK'
				});
				callback(false);
			} else {
				callback(true);
			}
		})
		.catch(() => {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: 'No se pudo validar el stock. Intente de nuevo.'
			});
			callback(false);
		});
	}

	function validarArticuloDuplicadoAntesDeGuardar(callback) {
		var id_producto = document.getElementById('id_producto').value;
		var id_detalle = document.getElementById('id_ejecucion_obra_detalle_materiales_utilizar').value;
		// Validación de duplicados SOLO si es nuevo
		if (!id_detalle) {
			if (window.detallesActualesIds && window.detallesActualesIds.includes(id_producto)) {
				Swal.fire({
					title: 'Artículo ya agregado',
					text: 'No se puede agregar dos veces el mismo artículo en la misma ejecución.',
					icon: 'warning'
				});
				return;
			}
		}
		callback();
	}
	btnCancelarDetalle.onclick = function() {
		limpiarFormulario();
	};
});
function cargarArticulos() {
	fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=articulos')
	.then(resp => resp.json())
	.then(data => {
		const sel = document.getElementById('id_producto');
		sel.innerHTML = '<option value="A">Selecciona artículo</option>';
		data.forEach(a => {
			sel.innerHTML += `<option value="${a.id_producto}">${a.nombre}</option>`;
		});
		if (window.jQuery) {
			$('#id_producto').select2();
		}
	});
}
function cargarDetalle() {
	const id = <?= $id_ejecucion_obra ?>;
	fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=listar_detalle&id_ejecucion_obra=${id}`)
	.then(resp => resp.json())
	.then(data => {
		pintarGridDetalle(data);
		window.detallesActualesIds = data.map(det => det.id_producto ? det.id_producto.toString() : "");
	});
}
function guardarDetalle() {
	const form = document.getElementById('formDetalle');
	const datos = new FormData(form);
	datos.append('action', 'guardar_detalle');
	fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php', {
		method: 'POST',
		body: datos
	})
	.then(resp => resp.text())
	.then(text => { 
		let r = {};
		try {
			r = JSON.parse(text);
		} catch (e) {
			Swal.fire({
				title: 'Error',
				text: 'La respuesta del servidor no es válida. Verifica el backend.',
				icon: 'error'
			});
			return;
		}
		cargarDetalle();
		limpiarFormulario();
	});
}
function editarDetalle(detalle) {
	document.getElementById('id_ejecucion_obra_detalle_materiales_utilizar').value = detalle.id_ejecucion_obra_detalle_materiales_utilizar;
	document.getElementById('id_producto').value = detalle.id_producto;
	document.getElementById('cantidad').value = detalle.cantidad;
	document.getElementById('id_bodega_lugar').value = detalle.id_bodega_lugar;
	document.getElementById('btnGuardarDetalle').textContent = "GUARDAR CAMBIOS";
	document.getElementById('btnCancelarDetalle').style.display = "";
	const sel = document.getElementById('id_producto');
	if (window.jQuery && $(sel).data('select2')) {
		$(sel).val(detalle.id_producto.toString()).trigger('change');
	}
	const selBodega = document.getElementById('id_bodega_lugar');
	if (window.jQuery && $(selBodega).data('select2')) {
		$(selBodega).val(detalle.id_bodega_lugar.toString()).trigger('change');
	}
}
function limpiarFormulario() {
	document.getElementById('formDetalle').reset();
	document.getElementById('id_ejecucion_obra_detalle_materiales_utilizar').value = '';
	document.getElementById('btnGuardarDetalle').textContent = "AGREGAR";
	document.getElementById('btnCancelarDetalle').style.display = "none";
	const sel = document.getElementById('id_producto');
	if (sel && sel.options.length > 0) {
		sel.selectedIndex = 0;
		if (window.jQuery && $(sel).data('select2')) {
			$(sel).val(sel.options[0].value).trigger('change');
		}
	}
	const selBodega = document.getElementById('id_bodega_lugar');
	if (selBodega && selBodega.options.length > 0) {
		selBodega.selectedIndex = 0;
		if (window.jQuery && $(selBodega).data('select2')) {
			$(selBodega).val(selBodega.options[0].value).trigger('change');
		}
	}
}
function eliminarDetalle(id) {
	Swal.fire({
		title: "¿Eliminar artículo?",
		text: "Esta acción no se puede revertir.",
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Sí, eliminar",
		cancelButtonText: "Cancelar"
	}).then((result) => {
		if (result.isConfirmed) {
			formDetalle.querySelectorAll('input, select, button').forEach(el => el.disabled = true);
			fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php', {
				method: 'POST',
				body: new URLSearchParams({
					action: 'eliminar_detalle',
					id_ejecucion_obra_detalle_materiales_utilizar: id
				})
			})
			.then(resp => resp.json())
			.then(r => {
				cargarDetalle();
				setTimeout(() => {
					fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=listar_detalle&id_ejecucion_obra=${<?= $id_ejecucion_obra ?>}`)
					.then(resp => resp.json())
					.then(data => {
						window.detallesActualesIds = data.map(det => det.id_producto ? det.id_producto.toString() : "");
					});
					limpiarFormulario();
					formDetalle.querySelectorAll('input, select, button').forEach(el => el.disabled = false);
				}, 400);
			});
		}
	});
}
function pintarGridDetalle(detalles) {
	 detallesActuales = detalles.map(det => det.articulo_nombre);
	 const $grid = $('#gridDetalle');
	 if ($.fn.DataTable.isDataTable($grid)) {
		 $grid.DataTable().destroy();
	 }
	 const tbody = document.querySelector('#gridDetalle tbody');
	 tbody.innerHTML = '';
	 detalles.forEach(det => {
		 const tr = document.createElement('tr');
		 let acciones = '';
		 if (window.id_estado_ejecucion_obra === "1") {
			 acciones = `
				 <button class="btn btn-warning" onclick='editarDetalle(${JSON.stringify(det)})'><i class="glyphicon glyphicon-edit"></i></button>
				 <button class="btn btn-danger" onclick='eliminarDetalle(${det.id_ejecucion_obra_detalle_materiales_utilizar})'><i class="glyphicon glyphicon-trash"></i></button>
			 `;
		 } else {
			 acciones = `
				 <button disabled class="btn btn-warning" onclick='editarDetalle(${JSON.stringify(det)})'><i class="glyphicon glyphicon-edit"></i></button>
				 <button disabled class="btn btn-danger" onclick='eliminarDetalle(${det.id_ejecucion_obra_detalle_materiales_utilizar})'><i class="glyphicon glyphicon-trash"></i></button>
			 `;
		 }
		 tr.innerHTML = `
			 <td>${det.id_ejecucion_obra_detalle_materiales_utilizar}</td>
			 <td>${det.articulo_nombre}</td>
			 <td>${det.nombreBodega ? det.nombreBodega : (det.bodega_nombre ? det.bodega_nombre : (det.ubicacion_nombre ? det.ubicacion_nombre : det.id_bodega_lugar))}</td>
			 <td>${det.cantidad}</td>
			 <td>${acciones}</td>
		 `;
		 tbody.appendChild(tr);
	 });
	 $('#gridDetalle').DataTable({
		 responsive: true,
		 destroy: true,
		 order: [[0, 'desc']]
	 });
 }
function getBase64Image(imgElement) {
	const canvas = document.createElement("canvas");
	canvas.width = imgElement.width;
	canvas.height = imgElement.height;
	const ctx = canvas.getContext("2d");
	ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
	return canvas.toDataURL("image/png");
}
function obtenerDatosDeTablaDetalle(tablaId) {
	const resultados = [];
	const table = $('#' + tablaId).DataTable();
	const allData = table.rows({search:'applied'}).data().toArray();
	allData.forEach(row => {
		resultados.push({
			campo1: row[0], // ID
			campo2: row[1], // Articulo
			campo3: row[2]  // Cantidad
		});
	});
	return resultados;
}
function exportarDetalleEjecucionAExcelConFormato() {
	var dt = $('#gridDetalle').DataTable();
	var data = dt.rows({search:'applied'}).data().toArray();
	let header = ['ID', 'ARTICULO', 'CANTIDAD'];
	var bodyRows = [];
	data.forEach(row => { bodyRows.push([row[0], row[1], row[2]]); });
	let totalCantidad = 0;
	bodyRows.forEach(r => { totalCantidad += parseFloat(r[2]) || 0; });
	let footRow = ["", "TOTAL:", totalCantidad.toFixed(2)];
	var workbook = new ExcelJS.Workbook();
	var worksheet = workbook.addWorksheet('Detalle Ejecución');
	let headerRow = worksheet.addRow(header);
	headerRow.eachCell(cell => {
		cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
		cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
		cell.alignment = { horizontal: "center", vertical: "middle" };
		cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
	});
	bodyRows.forEach(row => worksheet.addRow(row));
	var totalRow = worksheet.addRow(footRow);
	totalRow.eachCell(cell => {
		cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
		cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
		cell.alignment = { horizontal: "right", vertical: "middle" };
		cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
	});
	totalRow.getCell(2).alignment = { horizontal: "left", vertical: "middle" };
	worksheet.columns.forEach(function(column) {
		let maxLength = 10;
		column.eachCell({ includeEmpty: true }, function(cell) {
			let cellValue = cell.value ? cell.value.toString() : "";
			maxLength = Math.max(maxLength, cellValue.length + 2);
		});
		column.width = maxLength;
	});
	workbook.xlsx.writeBuffer().then(function(buffer) {
		saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Detalle_Ejecucion_Obra.xlsx");
	});
}
function exportarTablaDetalleAPDF(resultados) {
	const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });
	var imgElement = document.getElementById("logoEmpresa");
	if (imgElement && imgElement.complete) {
		var logoBase64 = getBase64Image(imgElement);
		doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
	}
	doc.setFontSize(20);
	doc.setTextColor(40, 55, 71);
	const pageWidth = doc.internal.pageSize.getWidth();
	doc.text("DETALLE DE EJECUCIÓN DE OBRA", pageWidth / 2, 40, { align: "center" });
	doc.setFontSize(12);
	let totalCantidad = 0;
	resultados.forEach(r => { totalCantidad += parseFloat(r.campo3) || 0; });
	const totalRow = { campo1: "", campo2: "TOTAL:", campo3: totalCantidad.toFixed(2) };
	const columns = [
		{ header: 'ID', dataKey: 'campo1' },
		{ header: 'ARTICULO', dataKey: 'campo2' },
		{ header: 'CANTIDAD', dataKey: 'campo3' }
	];
	doc.autoTable({
		columns: columns,
		body: resultados,
		foot: [totalRow],
		startY: 110,
		styles: { fontSize: 10, cellPadding: 5 },
		headStyles: { fillColor: [41, 134, 197], textColor: [255,255,255], fontStyle: 'bold' },
		footStyles: { fillColor: [41, 134, 197], textColor: [255,255,255], fontStyle: 'bold' },
		margin: { left: 40, right: 40 }
	});
	const fecha = new Date().toLocaleString();
	doc.setFontSize(10);
	doc.setTextColor(127, 140, 141);
	doc.text(`Generado el: ${fecha} | Usuario: <?php echo $_SESSION['usuario']; ?>`, 40, doc.internal.pageSize.getHeight() - 30);
	doc.save('Detalle_Ejecucion_Obra.pdf');
}
</script>
<?php 
}else{
	header("location:../index.php");
}
?>
