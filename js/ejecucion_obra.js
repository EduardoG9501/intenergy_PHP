// JS para Ejecución de Obra
// Basado en la lógica de pedido de materiales

document.addEventListener('DOMContentLoaded', () => {
	if (window._idOrdenTrabajo) {
		cargarEjecucionesPorOrden(window._idOrdenTrabajo);
		cargarIdentificadorOrden(window._idOrdenTrabajo);
	} else {
		cargarEjecuciones();
	}
	cargarOrdenes();

	const btnAgregar = document.getElementById('btnAgregar');
	const formModal = document.getElementById('formModal');
	const formEjecucion = document.getElementById('formEjecucionObra');
	const btnCancelar = document.getElementById('btnCancelar');
	const btnclosex = document.getElementById('closex');

	btnAgregar.onclick = () => mostrarFormulario();
	btnCancelar.onclick = () => {
		formModal.style.display = 'none';
		formEjecucion.reset();
		document.getElementById('id_ejecucion_obra').value = '';
	};
	btnclosex.onclick = () => {
		formModal.style.display = 'none';
		formEjecucion.reset();
	};
	formEjecucion.onsubmit = function(e) {
		e.preventDefault();
		guardarEjecucion();
	};

	if (window.jQuery) {
		$('#btnExportExcel').on('click', function(){
			exportarEjecucionesAExcel();
		});
		$('#btnExportPDF').on('click', function(){
			var resultados = obtenerDatosDeTablaEjecucion('gridEjecucionObra');
			exportarEjecucionesAPDF(resultados);
		});
	} else {
		document.getElementById('btnExportExcel').addEventListener('click', exportarEjecucionesAExcel);
		document.getElementById('btnExportPDF').addEventListener('click', function(){
			var resultados = obtenerDatosDeTablaEjecucion('gridEjecucionObra');
			exportarEjecucionesAPDF(resultados);
		});
	}
});

function cargarEjecucionesPorOrden(id_orden) {
	fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=listar&id_orden=${id_orden}`)
		.then(resp => resp.json())
		.then(data => {
			pintarGridEjecucion(data);
		});
}
function cargarIdentificadorOrden(id_orden) {
	fetch(`../procesos/ordenestrabajo/orden_trabajo.php?action=obtener&id_orden=${id_orden}`)
		.then(resp => resp.json())
		.then(data => {
			if (data && data.identificador && document.getElementById('identificadorOrden')) {
				document.getElementById('identificadorOrden').textContent = data.identificador;
			}
		});
}
function cargarEjecuciones() {
	fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=listar')
		.then(resp => resp.json())
		.then(data => {
			pintarGridEjecucion(data);
		});
}
function cargarOrdenes() {
	return fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=ordenes`)
		.then(resp => resp.json())
		.then(ordenes => {
			const sel = document.getElementById('id_orden');
			sel.innerHTML = '';
			ordenes.forEach(o => {  
				sel.innerHTML += `<option value="${o.id_orden}">${o.identificador}</option>`;
			});
			 $('#id_orden').select2();
		});
}
function cargarEstadosEjecucion(valorActual = null) {
	fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=estados')
		.then(resp => resp.json())
		.then(estados => {
			const estadoSel = document.getElementById('id_estado_ejecucion_obra');
			estadoSel.innerHTML = '';
			estados.forEach(e => {
				estadoSel.innerHTML += `<option value="${e.id_estado_ejecucion_obra}" ${valorActual == e.id_estado_ejecucion_obra ? 'selected' : ''}>${e.estado}</option>`;
			}); 
		});
}
function mostrarFormulario(ejecucion = null) {
	const formModal = document.getElementById('formModal');
	const formModalLabel = document.getElementById('formModalLabel');
	const divEstado = document.getElementById('div_estado_ejecucion');
	formModal.style.display = 'flex';
	const idOrdenCombo = document.getElementById('id_orden');
	if (ejecucion && ejecucion.id_orden) {
		formModalLabel.textContent = 'Editar Ejecución de Obra';
		Object.keys(ejecucion).forEach(k => {
			if (document.getElementById(k) && k !== 'id_orden') {
				document.getElementById(k).value = ejecucion[k];
			}
		});
		cargarOrdenes().then(() => {
			idOrdenCombo.value = ejecucion.id_orden;
			$('#id_orden').trigger('change');
			if (window._idOrdenTrabajo) {
				idOrdenCombo.value = window._idOrdenTrabajo;
				idOrdenCombo.disabled = true;
				$(idOrdenCombo).select2({disabled:true});
				let hiddenOrden = document.getElementById('hidden_id_orden');
				if (!hiddenOrden) {
					hiddenOrden = document.createElement('input');
					hiddenOrden.type = 'hidden';
					hiddenOrden.id = 'hidden_id_orden';
					hiddenOrden.name = 'id_orden';
					idOrdenCombo.parentNode.appendChild(hiddenOrden);
				}
				hiddenOrden.value = window._idOrdenTrabajo;
			} else {
				idOrdenCombo.disabled = false;
				$(idOrdenCombo).select2({disabled:false});
				let hiddenOrden = document.getElementById('hidden_id_orden');
				if (hiddenOrden) hiddenOrden.remove();
			}
		});
		divEstado.style.display = 'block';
		cargarEstadosEjecucion(ejecucion.id_estado_ejecucion_obra);
		// Desactivar botón GUARDAR si estado TERMINADO (id_estado_ejecucion_obra == 2)
		setTimeout(function() {
			var btnGuardar = document.getElementById('btnGuardar');
			if (String(ejecucion.id_estado_ejecucion_obra) === '2') {
				btnGuardar.disabled = true;
			} else {
				btnGuardar.disabled = false;
			}
		}, 300);
	} else {
		formModalLabel.textContent = 'Agregar Ejecución de Obra';
		document.getElementById('formEjecucionObra').reset();
		divEstado.style.display = 'none';
		cargarOrdenes().then(() => {
			if (window._idOrdenTrabajo) {
				idOrdenCombo.value = window._idOrdenTrabajo;
				idOrdenCombo.disabled = true;
				$(idOrdenCombo).select2({disabled:true});
				let hiddenOrden = document.getElementById('hidden_id_orden');
				if (!hiddenOrden) {
					hiddenOrden = document.createElement('input');
					hiddenOrden.type = 'hidden';
					hiddenOrden.id = 'hidden_id_orden';
					hiddenOrden.name = 'id_orden';
					idOrdenCombo.parentNode.appendChild(hiddenOrden);
				}
				hiddenOrden.value = window._idOrdenTrabajo;
			} else {
				idOrdenCombo.disabled = false;
				$(idOrdenCombo).select2({disabled:false});
				let hiddenOrden = document.getElementById('hidden_id_orden');
				if (hiddenOrden) hiddenOrden.remove();
			}
		});
		const hoy = new Date();
		const yyyy = hoy.getFullYear();
		const mm = String(hoy.getMonth() + 1).padStart(2, '0');
		const dd = String(hoy.getDate()).padStart(2, '0');
		const fechaHoy = `${yyyy}-${mm}-${dd}`;
		document.getElementById('fecha_solicitud').value = fechaHoy;
		document.getElementById('id_ejecucion_obra').value = '';
		document.getElementById('feriado').value = '0';
	}
}
function editarEjecucion(id_ejecucion_obra) {
	fetch(`../procesos/ejecucion_obra/proceso_ejecucion_obra.php?action=obtener&id_ejecucion_obra=${id_ejecucion_obra}`)
		.then(resp => resp.json())
		.then(ejecucion => mostrarFormulario(ejecucion));
}
function guardarEjecucion() {
	const formEjecucion = document.getElementById('formEjecucionObra');
	const datos = new FormData(formEjecucion);
	datos.append('action', 'guardar');
	fetch('../procesos/ejecucion_obra/proceso_ejecucion_obra.php', {
		method: 'POST',
		body: datos
	})
	.then(resp => resp.json())
	.then(r => {
		Swal.fire({
			title: r.mensaje,
			icon: r.success ? "success" : "warning",
			draggable: true,
		}); 
		if(r.success) {
			document.getElementById('formModal').style.display = 'none';
			if (window._idOrdenTrabajo) {
				cargarEjecucionesPorOrden(window._idOrdenTrabajo);
			} else {
				cargarEjecuciones();
			}
		}
	});
}
window.onclick = function(event) {
	const formModal = document.getElementById('formModal');
	if (event.target == formModal) {
		formModal.style.display = "none";
		document.getElementById('formEjecucionObra').reset();
	}
}
function pintarGridEjecucion(ejecuciones) { 
	const $grid = $('#gridEjecucionObra');
	if ($.fn.DataTable.isDataTable($grid)) {
		$grid.DataTable().destroy();
	} 
	const tbody = document.querySelector('#gridEjecucionObra tbody');
	tbody.innerHTML = '';
	ejecuciones.forEach(ejecucion => {
		const tr = document.createElement('tr');
		let idOrdenParam = window._idOrdenTrabajo ? `&idorden=${window._idOrdenTrabajo}` : '';
		tr.innerHTML = `
			<td>${ejecucion.id_ejecucion_obra}</td>
			<td>${ejecucion.orden_identificador ? ejecucion.orden_identificador: ''}</td>
			<td>${ejecucion.fecha_solicitud}</td>
			<td>${ejecucion.feriado == 1 ? 'Sí' : 'No'}</td>
			<td>${ejecucion.observacion || ''}</td>
			<td>${ejecucion.estado_nombre}</td>
			<td>
				<button class="btn btn-warning" onclick="editarEjecucion(${ejecucion.id_ejecucion_obra})"><i class="glyphicon glyphicon-edit"></i></button>
				   <a class="btn btn-info" href="detalle_ejecucion_obra.php?id_ejecucion_obra=${ejecucion.id_ejecucion_obra}&id_estado_ejecucion_obra=${ejecucion.id_estado_ejecucion_obra}${idOrdenParam}">MATERIALES</a>
				   <a class="btn btn-primary" href="horas_trabajo_diario.php?id_ejecucion_obra=${ejecucion.id_ejecucion_obra}&id_estado_ejecucion_obra=${ejecucion.id_estado_ejecucion_obra}${idOrdenParam}">H.TRABAJO</a>
				<a class="btn btn-success" href="informe_trabajo.php?id_ejecucion_obra=${ejecucion.id_ejecucion_obra}&identificador=${encodeURIComponent(ejecucion.orden_identificador)}">INF.DIARIO</a>
			</td>
		`;
		tbody.appendChild(tr);
	});
	if ($.fn.DataTable.isDataTable('#gridEjecucionObra')) {
		$('#gridEjecucionObra').DataTable().destroy();
	}
	$('#gridEjecucionObra').DataTable({
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
function obtenerDatosDeTablaEjecucion(tablaId) {
	const resultados = [];
	const table = $('#' + tablaId).DataTable();
	const allData = table.rows({search:'applied'}).data().toArray();
	allData.forEach(row => {
		resultados.push({
			campo1: row[0], // ID
			campo2: row[1], // Orden
			campo3: row[2], // Fecha
			campo4: row[3], // Feriado
			campo5: row[4], // Observacion
			campo6: row[5]  // Estado
		});
	});
	return resultados;
}
function exportarEjecucionesAExcel() {
	var dt = $('#gridEjecucionObra').DataTable();
	var data = dt.rows({search:'applied'}).data().toArray();
	let header = ['ID','ORDEN TRABAJO','FECHA SOLICITUD','FERIADO','OBSERVACIÓN','ESTADO'];
	var bodyRows = [];
	data.forEach(row => { bodyRows.push([row[0], row[1], row[2], row[3], row[4], row[5]]); });
	var workbook = new ExcelJS.Workbook();
	var worksheet = workbook.addWorksheet('Ejecuciones');
	let headerRow = worksheet.addRow(header);
	headerRow.eachCell(cell => {
		cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
		cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
		cell.alignment = { horizontal: "center", vertical: "middle" };
	});
	bodyRows.forEach(row => worksheet.addRow(row));
	worksheet.columns.forEach(function(column) {
		let maxLength = 10;
		column.eachCell({ includeEmpty: true }, function(cell) {
			let cellValue = cell.value ? cell.value.toString() : "";
			maxLength = Math.max(maxLength, cellValue.length + 2);
		});
		column.width = maxLength;
	});
	workbook.xlsx.writeBuffer().then(function(buffer) {
		saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Ejecucion_Obra.xlsx");
	});
}
function exportarEjecucionesAPDF(resultados) {
	const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });
	var imgElement = document.getElementById("logoEmpresa");
	if (imgElement && imgElement.complete) {
		var logoBase64 = getBase64Image(imgElement);
		doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
	}
	doc.setFontSize(20);
	doc.setTextColor(40, 55, 71);
	const pageWidth = doc.internal.pageSize.getWidth();
	doc.text("EJECUCIÓN DE OBRA", pageWidth / 2, 40, { align: "center" });
	const columns = [
		{ header: 'ID', dataKey: 'campo1' },
		{ header: 'ORDEN', dataKey: 'campo2' },
		{ header: 'FECHA', dataKey: 'campo3' },
		{ header: 'FERIADO', dataKey: 'campo4' },
		{ header: 'OBSERVACIÓN', dataKey: 'campo5' },
		{ header: 'ESTADO', dataKey: 'campo6' }
	];
	doc.autoTable({
		columns: columns,
		body: resultados,
		startY: 100,
		styles: { fontSize: 10, cellPadding: 5 },
		headStyles: { fillColor: [41, 134, 197], textColor: [255,255,255], fontStyle: 'bold' },
		margin: { left: 40, right: 40 }
	});
	const fecha = new Date().toLocaleString();
	doc.setFontSize(10);
	doc.setTextColor(127, 140, 141);
	doc.text(`Generado el: ${fecha}`, 40, doc.internal.pageSize.getHeight() - 30);
	doc.save('Ejecucion_Obra.pdf');
}
// JS base para Ejecución de Obra
// Se llenará con la lógica clonada de pedido_materiales.js
