<?php
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
require_once "../clases/Conexion.php";
if(isset($_SESSION['usuario'])){
	$id_pedido_material = $_GET['id_pedido_material'];
	$id_estado_pedido_material = $_GET['id_estado_pedido_material'];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Detalle de Pedido de Material</title>
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
	<label class="label-custom">ID Pedido: <?php echo  $id_pedido_material;  ?> </label>  
	<label class="label-custom">; ESTADO Pedido: <?php if ($id_estado_pedido_material === "1") { echo "APROBADO";} elseif ($id_estado_pedido_material === "2") echo "ANULADO" ; ?> </label>  
	<button type="button" class="btn btn-primary" id="exportDetalleExcel"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
	<button type="button" class="btn btn-danger" id="imprimirDetallePDF"><i class="fa fa-file-pdf"></i> Imprimir PDF</button>
	<!-- Logo oculto para PDF -->
	<img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
	<hr>
	<form id="formDetalle" style="display: flex; gap: 15px; align-items: flex-end; background:#deeff9;">
		<input type="hidden" name="id_pedido_material_detalle" id="id_pedido_material_detalle">
		<input type="hidden" name="id_pedido_material" value="<?= $id_pedido_material ?>">
		<label class="label-custom">ARTICULO: </label>  
		<select name="id_producto" id="id_producto" style="width: 300px;" required></select>
		<label class="label-custom">CANTIDAD:</label>
		<input style="width: 100px;" type="number" step="0.01" name="cantidad" id="cantidad" required>
		<?php  if ($id_estado_pedido_material === "1") { ?> 
			<button type="submit" id="btnGuardarDetalle" class="btn btn-success">AGREGAR</button>
			<button type="button" id="btnCancelarDetalle" class="btn btn-danger" style="display:none">CANCELAR</button>
		 <?php  } else { ?> 
			<button disabled type="submit" id="btnGuardarDetalle" class="btn btn-success">AGREGAR</button>
			<button disabled type="button" id="btnCancelarDetalle" class="btn btn-danger" style="display:none">CANCELAR</button>
		 <?php  }  ?> 
	</form>
	<hr>
	<table id="gridDetalle" class="display">
		<thead>
			<tr>
				<th>ID</th>
				<th>ARTICULO</th>
				<th>CANTIDAD</th>
				<th>ACCION</th>
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
// Variable global para mantener el estado real de los artículos en el pedido
let detallesActuales = [];

document.addEventListener('DOMContentLoaded', () => {
	cargarArticulos();
	cargarDetalle();

	// Export handlers
	if (window.jQuery) {
		$('#exportDetalleExcel').on('click', function(){
			exportarDetallePedidoAExcelConFormato();
		});
		$('#imprimirDetallePDF').on('click', function(){
			var resultados = obtenerDatosDeTablaDetalle('gridDetalle');
			exportarTablaDetalleAPDF(resultados);
		});
	} else {
		document.getElementById('exportDetalleExcel').addEventListener('click', exportarDetallePedidoAExcelConFormato);
		document.getElementById('imprimirDetallePDF').addEventListener('click', function(){
			var resultados = obtenerDatosDeTablaDetalle('gridDetalle');
			exportarTablaDetalleAPDF(resultados);
		});
	}
	const formDetalle = document.getElementById('formDetalle');
	const btnCancelarDetalle = document.getElementById('btnCancelarDetalle');
	const btnRegresar = document.getElementById('btnRegresar');
	btnRegresar.onclick = () => {
		window.location.href = "pedidomateriales.php";
	};
	formDetalle.onsubmit = function(e) {
		e.preventDefault();
		validarStockAntesDeGuardar(function() {
			guardarDetalle();
		});
	};
	btnCancelarDetalle.onclick = function() {
		limpiarFormulario();
	};
});
function cargarArticulos() {
	 fetch('../procesos/pedido_materiales/proceso_pedido_material.php?action=articulos')
	.then(resp => resp.json())
	.then(data => {
		const sel = document.getElementById('id_producto');
		sel.innerHTML = '';
		data.forEach(a => {
			sel.innerHTML += `<option value="${a.id_producto}">${a.nombre}</option>`;
		});
		$('#id_producto').select2();
	});
}
function cargarDetalle() {
	const id = <?= $id_pedido_material ?>;
	fetch(`../procesos/pedido_materiales/proceso_pedido_material.php?action=listar_detalle&id_pedido_material=${id}`)
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
    fetch('../procesos/pedido_materiales/proceso_pedido_material.php', {
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
    document.getElementById('id_pedido_material_detalle').value = detalle.id_pedido_material_detalle;
    document.getElementById('id_producto').value = detalle.id_producto;
    document.getElementById('cantidad').value = detalle.cantidad;
    document.getElementById('btnGuardarDetalle').textContent = "GUARDAR CAMBIOS";
    document.getElementById('btnCancelarDetalle').style.display = "";
    // Solución: fuerza la selección visual en select2
    const sel = document.getElementById('id_producto');
    if (window.jQuery && $(sel).data('select2')) {
        $(sel).val(detalle.id_producto.toString()).trigger('change');
    }
}
function limpiarFormulario() {
    document.getElementById('formDetalle').reset();
    document.getElementById('id_pedido_material_detalle').value = '';
    document.getElementById('btnGuardarDetalle').textContent = "AGREGAR";
    document.getElementById('btnCancelarDetalle').style.display = "none";
    // Solución: fuerza el combo a la primera opción y refresca select2
    const sel = document.getElementById('id_producto');
    if (sel && sel.options.length > 0) {
        sel.selectedIndex = 0;
        if (window.jQuery && $(sel).data('select2')) {
            $(sel).val(sel.options[0].value).trigger('change');
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
            // Deshabilitar el form mientras actualiza
            formDetalle.querySelectorAll('input, select, button').forEach(el => el.disabled = true);
            fetch('../procesos/pedido_materiales/proceso_pedido_material.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'eliminar_detalle',
                    id_pedido_material_detalle: id
                })
            })
            .then(resp => resp.json())
            .then(r => {
                // Refresca el grid y actualiza el array de ids después de eliminar
                cargarDetalle();
                setTimeout(() => {
                    // Vuelve a cargar los ids actuales después de refrescar el grid
                    fetch(`../procesos/pedido_materiales/proceso_pedido_material.php?action=listar_detalle&id_pedido_material=${<?= $id_pedido_material ?>}`)
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
    // Elimina la actualización aquí para evitar desincronización
    // window.detallesActualesIds = detalles.map(det => det.id_producto ? det.id_producto.toString() : "");
    detallesActuales = detalles.map(det => det.articulo_nombre);
    const $grid = $('#gridDetalle');
    if ($.fn.DataTable.isDataTable($grid)) {
        $grid.DataTable().destroy();
    } 
    const tbody = document.querySelector('#gridDetalle tbody');
    tbody.innerHTML = '';
    detalles.forEach(det => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${det.id_pedido_material_detalle}</td>
            <td>${det.articulo_nombre}</td>
            <td>${det.cantidad}</td>
            <?php  if ($id_estado_pedido_material === "1") { ?> 
            <td>
                <button class="btn btn-warning" onclick='editarDetalle(${JSON.stringify(det)})'><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick='eliminarDetalle(${det.id_pedido_material_detalle})'><i class="glyphicon glyphicon-trash"></i></button>
            </td>
            <?php  } else {  ?> 
             <td>
                <button disabled class="btn btn-warning" onclick='editarDetalle(${JSON.stringify(det)})'><i class="glyphicon glyphicon-edit"></i></button>
                <button disabled class="btn btn-danger" onclick='eliminarDetalle(${det.id_pedido_material_detalle})'><i class="glyphicon glyphicon-trash"></i></button>
            </td>
              <?php  } ?> 
        `;
        tbody.appendChild(tr);
    });
    $('#gridDetalle').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
}
</script>
<script>
// --- Helpers for export ---
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

function exportarDetallePedidoAExcelConFormato() {
	var dt = $('#gridDetalle').DataTable();
	var data = dt.rows({search:'applied'}).data().toArray();
	let header = ['ID', 'ARTICULO', 'CANTIDAD'];
	var bodyRows = [];
	data.forEach(row => { bodyRows.push([row[0], row[1], row[2]]); });
	let totalCantidad = 0;
	bodyRows.forEach(r => { totalCantidad += parseFloat(r[2]) || 0; });
	let footRow = ["", "TOTAL:", totalCantidad.toFixed(2)];

	var workbook = new ExcelJS.Workbook();
	var worksheet = workbook.addWorksheet('Detalle Pedido');
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
		saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Detalle_Pedido.xlsx");
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
	doc.text("DETALLE DE PEDIDO DE MATERIALES", pageWidth / 2, 40, { align: "center" });
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
	doc.save('Detalle_Pedido.pdf');
}
</script>
<?php 
}else{
	header("location:../index.php");
}
?>
<script type="text/javascript">
function validarStockAntesDeGuardar(callback) {
    var id_producto = document.getElementById('id_producto').value;
    var cantidad = parseFloat(document.getElementById('cantidad').value);
    var id_detalle = document.getElementById('id_pedido_material_detalle').value;
    // Validación de duplicados SOLO si es nuevo
    if (!id_detalle) {
        if (window.detallesActualesIds && window.detallesActualesIds.includes(id_producto)) {
            Swal.fire({
                title: 'Artículo ya agregado',
                text: 'No se puede agregar dos veces el mismo artículo en el mismo pedido.',
                icon: 'warning'
            });
            return;
        }
    }
    if (!id_producto || isNaN(cantidad) || cantidad <= 0) {
        Swal.fire("Ingrese un artículo y cantidad válida", "", "info");
        return;
    }
    fetch('../procesos/pedido_materiales/proceso_pedido_material.php?action=get_stock_producto_bodega&id_producto=' + encodeURIComponent(id_producto))
    .then(resp => resp.json())
    .then(data => {
        if (parseFloat(data.stock) < cantidad) {
            Swal.fire({
                title: "STOCK INSUFICIENTE",
                text: "No tiene suficiente stock para el artículo que desea solicitar.",
                icon: "warning"
            });
        } else {
            callback();
        }
    });
}
</script>
