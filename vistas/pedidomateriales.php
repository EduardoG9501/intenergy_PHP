<?php 
ini_set('session.gc_maxlifetime', 14400);
session_set_cookie_params(14400);
session_start();
if(isset($_SESSION['usuario'])){ 
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "PEDIDO DE MATERIALES");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
<!DOCTYPE html>
<html>
<head>
	<title>OPERACIONES -> PEDIDO DE MATERIALES</title>
	<?php require_once "menu.php"; ?> 
	<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
	<div class="container">  
	<button id="btnAgregar" class="btn btn-primary">
      <i class="glyphicon glyphicon-plus"></i> NUEVO
    </button> 
    <button id="btnExportExcel" class="btn btn-success">
       <i class="glyphicon glyphicon-download"></i> Excel
    </button>
    <button id="btnExportPDF" class="btn btn-danger">
         <i class="glyphicon glyphicon-download"></i> PDF
    </button>
		<!-- Logo oculto para PDF -->
		<img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
		<hr>    
		<table id="gridPedidos" class="table table-striped nowrap" style="width:100%">
		<caption><hash class="hash-custom">Pedidos de Materiales</hash></caption>  
		<thead> 
			 <tr>
					<th>ID</th>
					<th style="width: 50px;">Orden Trabajo</th>
					<th>Fecha Solicitud</th>
					<th>Observación</th>
					<th>Estado</th>
					<th style="width: 150px;">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<!-- Los pedidos se cargan aquí por JS -->
			</tbody>
		</table>
		<!-- Formulario Modal -->
		<div id="formModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<form id="formPedido" class="modal-content">
				<div class="modal-header">
					<h3 id="formModalLabel">NUEVO PEDIDO DE MATERIAL</h3>
					<button id="closex" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="id_pedido_material" name="id_pedido_material">
					<div class="row">
						<div class="col-sm-6">
							<label class="label-custom">ORDEN DE TRABAJO:</label> 
							<select name="id_orden" id="id_orden" style="width: 270px;" required></select>
							<label class="label-custom">FECHA SOLICITUD:</label>
							<input class="form-control input-sm" type="date" id="fecha_solicitud" name="fecha_solicitud" required>
						</div>
						<div class="col-sm-6">  
							<label class="label-custom">OBSERVACIÓN:</label>
							<textarea style="width: 100%;" id="observacion" name="observacion"></textarea>  
							<div id="div_estado_pedido" style="display:none;">
                            <label class="label-custom">ESTADO DEL PEDIDO:</label>
                                <select class="form-control input-sm" id="id_estado_pedido_material" name="id_estado_pedido_material"></select>
                            </div>
						</div>
					</div>
				</div>
				<div class="modal-footer form-acciones" style="text-align: center;"> 
					<button type="button" id="btnCancelar" class="btn btn-danger">CANCELAR</button>
					<button type="submit" id="btnGuardar" class="btn btn-success">GUARDAR</button>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
<style>
.modal-header .close { margin-top: -50px !important; }
.modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
.modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center;}
#gridPedidos tbody tr:hover { background-color: #e0f7fa; cursor: pointer; transition: background 0.2s;}
label { display: flex; flex-direction: column; font-size: 14px;}
input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
</style>
<script type="text/javascript"> 
document.addEventListener('DOMContentLoaded', () => {
	cargarPedidos();
	cargarOrdenes();

	const btnAgregar = document.getElementById('btnAgregar');
	const formModal = document.getElementById('formModal');
	const formPedido = document.getElementById('formPedido');
	const btnCancelar = document.getElementById('btnCancelar');
	const btnclosex = document.getElementById('closex');

	btnAgregar.onclick = () => mostrarFormulario();

	btnCancelar.onclick = () => {
		formModal.style.display = 'none';
		formPedido.reset();
		document.getElementById('id_pedido_material').value = '';
	};
	btnclosex.onclick = () => {
		formModal.style.display = 'none';
		formPedido.reset();
	};
	formPedido.onsubmit = function(e) {
		e.preventDefault();
		guardarPedido();
	};

	// Export handlers
	if (window.jQuery) {
		$('#btnExportExcel').on('click', function(){
			exportarPedidosAExcel();
		});
		$('#btnExportPDF').on('click', function(){
			var resultados = obtenerDatosDeTablaPedidos('gridPedidos');
			exportarPedidosAPDF(resultados);
		});
	} else {
		document.getElementById('btnExportExcel').addEventListener('click', exportarPedidosAExcel);
		document.getElementById('btnExportPDF').addEventListener('click', function(){
			var resultados = obtenerDatosDeTablaPedidos('gridPedidos');
			exportarPedidosAPDF(resultados);
		});
	}
});

function cargarPedidos() {
	fetch('../procesos/pedido_materiales/proceso_pedido_material.php?action=listar')
		.then(resp => resp.json())
		.then(data => {
			pintarGrid(data);
		});
}
function cargarOrdenes() {
    return fetch(`../procesos/pedido_materiales/proceso_pedido_material.php?action=ordenes`)
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
function cargarEstadosPedido(valorActual = null) {
    fetch('../procesos/pedido_materiales/proceso_pedido_material.php?action=estados')
        .then(resp => resp.json())
        .then(estados => {
            const estadoSel = document.getElementById('id_estado_pedido_material');
            estadoSel.innerHTML = '';
            estados.forEach(e => {
                estadoSel.innerHTML += `<option value="${e.id_estado_pedido_material}" ${valorActual == e.id_estado_pedido_material ? 'selected' : ''}>${e.estado}</option>`;
            }); 
        });
}
function mostrarFormulario(pedido = null) {
    const formModal = document.getElementById('formModal');
    const formModalLabel = document.getElementById('formModalLabel');
    const divEstado = document.getElementById('div_estado_pedido');
    formModal.style.display = 'flex';
    if (pedido && pedido.id_orden) {
        formModalLabel.textContent = 'Editar Pedido de Material';
        Object.keys(pedido).forEach(k => {
            if (document.getElementById(k) && k !== 'id_orden') {
                document.getElementById(k).value = pedido[k];
            }
        });
        cargarOrdenes().then(() => {
            document.getElementById('id_orden').value = pedido.id_orden;
            $('#id_orden').trigger('change');
        });
        divEstado.style.display = 'block';
        cargarEstadosPedido(pedido.id_estado_pedido_material);
    } else {
        formModalLabel.textContent = 'Agregar Pedido de Material';
        document.getElementById('formPedido').reset();
        divEstado.style.display = 'none';
        cargarOrdenes();
        // Fecha por defecto
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const fechaHoy = `${yyyy}-${mm}-${dd}`;
        document.getElementById('fecha_solicitud').value = fechaHoy;
        document.getElementById('id_pedido_material').value = '';
    }
}
function editarPedido(id_pedido_material) {
	fetch(`../procesos/pedido_materiales/proceso_pedido_material.php?action=obtener&id_pedido_material=${id_pedido_material}`)
		.then(resp => resp.json())
		.then(pedido => mostrarFormulario(pedido));
}
function guardarPedido() {
	const formPedido = document.getElementById('formPedido');
	const datos = new FormData(formPedido);
	datos.append('action', 'guardar');
	fetch('../procesos/pedido_materiales/proceso_pedido_material.php', {
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
			cargarPedidos();
		}
	});
}
window.onclick = function(event) {
	const formModal = document.getElementById('formModal');
	if (event.target == formModal) {
		formModal.style.display = "none";
		document.getElementById('formPedido').reset();
	}
}
function pintarGrid(pedidos) { 
   const $grid = $('#gridPedidos');
    if ($.fn.DataTable.isDataTable($grid)) {
        $grid.DataTable().destroy();
    } 
    const tbody = document.querySelector('#gridPedidos tbody');
    tbody.innerHTML = '';
	pedidos.forEach(pedido => {
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td>${pedido.id_pedido_material}</td>
			<td>${pedido.orden_identificador ? pedido.orden_identificador: ''}</td>
			<td>${pedido.fecha_solicitud}</td>
			<td>${pedido.observacion || ''}</td>
			<td>${pedido.estado_nombre}</td>
			<td>
				<button class="btn btn-warning" onclick="editarPedido(${pedido.id_pedido_material})"><i class="glyphicon glyphicon-edit"></i></button>
				<a class="btn btn-primary" href="detalle_pedido_material.php?id_pedido_material=${pedido.id_pedido_material}&id_estado_pedido_material=${pedido.id_estado_pedido_material}">Detalle</a>
			</td>
		`;
		tbody.appendChild(tr);
	});
    if ($.fn.DataTable.isDataTable('#gridPedidos')) {
        $('#gridPedidos').DataTable().destroy();
    }
    $('#gridPedidos').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
}

// --- Export helpers for pedidos list ---
function getBase64Image(imgElement) {
	const canvas = document.createElement("canvas");
	canvas.width = imgElement.width;
	canvas.height = imgElement.height;
	const ctx = canvas.getContext("2d");
	ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
	return canvas.toDataURL("image/png");
}

function obtenerDatosDeTablaPedidos(tablaId) {
	const resultados = [];
	const table = $('#' + tablaId).DataTable();
	const allData = table.rows({search:'applied'}).data().toArray();
	allData.forEach(row => {
		resultados.push({
			campo1: row[0], // ID
			campo2: row[1], // Orden
			campo3: row[2], // Fecha
			campo4: row[3], // Observacion
			campo5: row[4]  // Estado
		});
	});
	return resultados;
}

function exportarPedidosAExcel() {
	var dt = $('#gridPedidos').DataTable();
	var data = dt.rows({search:'applied'}).data().toArray();
	let header = ['ID','ORDEN TRABAJO','FECHA SOLICITUD','OBSERVACIÓN','ESTADO'];
	var bodyRows = [];
	data.forEach(row => { bodyRows.push([row[0], row[1], row[2], row[3], row[4]]); });

	var workbook = new ExcelJS.Workbook();
	var worksheet = workbook.addWorksheet('Pedidos');
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
		saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Pedidos_Materiales.xlsx");
	});
}

function exportarPedidosAPDF(resultados) {
	const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });
	var imgElement = document.getElementById("logoEmpresa");
	if (imgElement && imgElement.complete) {
		var logoBase64 = getBase64Image(imgElement);
		doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
	}
	doc.setFontSize(20);
	doc.setTextColor(40, 55, 71);
	const pageWidth = doc.internal.pageSize.getWidth();
	doc.text("PEDIDOS DE MATERIALES", pageWidth / 2, 40, { align: "center" });
	const columns = [
		{ header: 'ID', dataKey: 'campo1' },
		{ header: 'ORDEN', dataKey: 'campo2' },
		{ header: 'FECHA', dataKey: 'campo3' },
		{ header: 'OBSERVACIÓN', dataKey: 'campo4' },
		{ header: 'ESTADO', dataKey: 'campo5' }
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
	doc.text(`Generado el: ${fecha} | Usuario: <?php echo $_SESSION['usuario']; ?>`, 40, doc.internal.pageSize.getHeight() - 30);
	doc.save('Pedidos_Materiales.pdf');
}
</script>
<?php 
}else{
	header("location:../index.php");
}
?>
