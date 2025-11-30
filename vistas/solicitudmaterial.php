<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "SOLICITUD DE MATERIAL");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?> 
<!DOCTYPE html>
<html>
<head>
	<title>OPERACIONES -> SOLICITUD DE MATERIALES</title>
	<?php require_once "menu.php"; ?> 
     <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
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
		<hr>    
		<table id="gridSolicitudes" class="table table-striped nowrap" style="width:100%">
		<caption><hash class="hash-custom">Solicitudes de Materiales</hash></caption>  
		<thead> 
			 <tr>
					<th>ID</th>
					<th style="width: 50px;">Orden Trabajo</th>
					<th>Fecha Solicitud</th>
					<th>Lugar</th>
					<th>Ubicaciòn</th>
					<th style="width: 50px;">Responsable Obra</th>
					<th>Estado</th>
					<th style="width: 50px;">Entrega</th>
					<th style="width: 50px;">Recibe</th>
					<th style="width: 150px;">Acciones</th>
				</tr>
			</thead>
			<tbody>
				<!-- Las solicitudes se cargan aquí por JS -->
			</tbody>
		</table>
		
		<!-- Formulario Modal -->
		<div id="formModal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<form id="formSolicitud" class="modal-content">
				<div class="modal-header">
					<h3 id="formModalLabel">NUEVA SOLICITUD DE MATERIAL</h3>
					<button id="closex" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="id_solicitud_material" name="id_solicitud_material">
					<div class="row">
						<div class="col-sm-6">
							<label class="label-custom">ORDEN DE TRABAJO:</label> 
							<select name="id_orden" id="id_orden" style="width: 270px;" required></select>
							<label class="label-custom">FECHA SOLICITUD:</label>
							<input class="form-control input-sm" type="date" id="fecha_solicitud" name="fecha_solicitud" required>
							<label class="label-custom">LUGAR::</label>
							<select id="id_bodega_principal" name="id_bodega_principal"  style="width: 270px;" required></select>
							<label class="label-custom">UBICACIÒN:</label>
							<select id="id_bodega_secundaria" name="id_bodega_secundaria"  style="width: 270px;" required></select>
						    <div id="div_estado_solicitud" style="display:none;">
                            <label class="label-custom">ESTADO DE LA SOLICITUD:</label>
                                <select class="form-control input-sm" id="id_estado_solicitud" name="id_estado_solicitud"></select>
                            </div>
						</div>
						<div class="col-sm-6">  
							<label class="label-custom">RESPONSABLE OBRA:</label>
							<select id="id_responsable_obra" name="id_responsable_obra"  style="width: 270px;" required></select>
							<label class="label-custom">ENTREGA ARTÍCULOS:</label>
							<select id="id_entrega_articulos" name="id_entrega_articulos"  style="width: 270px;" required></select>
							<label class="label-custom">RECIBE ARTÍCULOS:</label>
							<select id="id_recibe_articulos" name="id_recibe_articulos"  style="width: 270px;" required></select>
							<label class="label-custom">OBSERVACIÓN:</label>
							<textarea style="width: 100%;" id="observacion" name="observacion"></textarea>  
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
<!-- CSS igual que orden de trabajo -->
<style>
.modal-header .close { margin-top: -50px !important; }
.modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
.modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center;}
#gridSolicitudes tbody tr:hover { background-color: #e0f7fa; cursor: pointer; transition: background 0.2s;}
label { display: flex; flex-direction: column; font-size: 14px;}
input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
</style>
<script type="text/javascript"> 
let bodegasCache = [];

document.addEventListener('DOMContentLoaded', () => {
	cargarSolicitudes();
	cargarOrdenes();
	cargarBodegas();
	// Quita el cargarEmpleados() global aquí, será llamado dentro de mostrarFormulario

	const btnAgregar = document.getElementById('btnAgregar');
	const formModal = document.getElementById('formModal');
	const formSolicitud = document.getElementById('formSolicitud');
	const btnCancelar = document.getElementById('btnCancelar');
	const btnclosex = document.getElementById('closex');

	btnAgregar.onclick = () => mostrarFormulario();

	btnCancelar.onclick = () => {
		formModal.style.display = 'none';
		formSolicitud.reset();
		document.getElementById('id_solicitud_material').value = '';
	};
	btnclosex.onclick = () => {
		formModal.style.display = 'none';
		formSolicitud.reset();
	};
	formSolicitud.onsubmit = function(e) {
		e.preventDefault();
		guardarSolicitud();
	};
});

function cargarSolicitudes() {
	fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=listar')
		.then(resp => resp.json())
		.then(data => {
			pintarGrid(data);
		});
}
function cargarOrdenes(idsolicitud) {
    return fetch(`../procesos/ordenestrabajo/proceso_solicitud_material.php?action=listarorden&id_solicitud_material=${idsolicitud}`)
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

function cargarBodegas() {
    fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=bodegas')
        .then(resp => resp.json())
        .then(bodegas => {
            bodegasCache = bodegas; // Guardamos para reutilizar
            const principal = document.getElementById('id_bodega_principal');
            const secundaria = document.getElementById('id_bodega_secundaria');

            // Solo principales
            principal.innerHTML = '';
            bodegas.filter(b => !b.es_bodega_secundaria).forEach(b => {
                principal.innerHTML += `<option value="${b.id_bodega}">${b.nombre}</option>`;
            });
            $('#id_bodega_principal').select2();

            // Limpiar secundaria inicialmente
            secundaria.innerHTML = '';
            $('#id_bodega_secundaria').select2();

            // Cuando cambie principal, cargar la principal + secundarias hijas
            principal.onchange = function() {
                let idPrincipal = principal.value;
                secundaria.innerHTML = '';

                // 1. Agrega la bodega principal seleccionada como opción
                const bodegaPrincipalObj = bodegas.find(b => b.id_bodega == idPrincipal);
                if (bodegaPrincipalObj) {
                    secundaria.innerHTML += `<option value="${bodegaPrincipalObj.id_bodega}">${bodegaPrincipalObj.nombre} (Principal)</option>`;
                }

                // 2. Agrega las bodegas secundarias hijas
                bodegas.filter(b =>
                    b.es_bodega_secundaria === 1 &&
                    b.id_bodega_principal == idPrincipal
                ).forEach(b => {
                    secundaria.innerHTML += `<option value="${b.id_bodega}">${b.nombre}</option>`;
                });

                // Selecciona la principal como default
                secundaria.value = idPrincipal;
                $('#id_bodega_secundaria').val(idPrincipal).trigger('change');
            };

            // Disparar el evento al cargar para sincronizar
            if (principal.options.length > 0) {
                principal.value = principal.options[0].value;
                principal.onchange();
            }
        });
}

// CAMBIO: cargarEmpleados ahora acepta callback
function cargarEmpleados(callback) {
	fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=empleados')
		.then(resp => resp.json())
		.then(empleados => {
			const respObra = document.getElementById('id_responsable_obra');
			const entrega = document.getElementById('id_entrega_articulos');
			const recibe = document.getElementById('id_recibe_articulos');
			respObra.innerHTML = '';
			entrega.innerHTML = '';
			recibe.innerHTML = '';
			empleados.forEach(e => {
				const opt = `<option value="${e.id_empleados}">${e.nombre}</option>`;
				respObra.innerHTML += opt;
				entrega.innerHTML += opt;
				recibe.innerHTML += opt;
			});
			$('#id_responsable_obra').select2();
			$('#id_entrega_articulos').select2();
			$('#id_recibe_articulos').select2();
			if (typeof callback === 'function') callback();
		});
}

function cargarEstadosSolicitudMaterial(valorActual = null) {
    fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=estados')
        .then(resp => resp.json())
        .then(estados => {
            const estadoSel = document.getElementById('id_estado_solicitud');
            estadoSel.innerHTML = '';
            estados.forEach(e => {
                estadoSel.innerHTML += `<option value="${e.id_estado_solicitud}" ${valorActual == e.id_estado_solicitud ? 'selected' : ''}>${e.estado}</option>`;
            }); 
        });
}

// CAMBIO PRINCIPAL: setea selects empleados luego de cargar opciones
function mostrarFormulario(solicitud = null) {
    const formModal = document.getElementById('formModal');
    const formModalLabel = document.getElementById('formModalLabel');
    const divEstado = document.getElementById('div_estado_solicitud');
    formModal.style.display = 'flex';

    let selectBodega = document.getElementById('id_bodega_principal');
    let selectSecundaria = document.getElementById('id_bodega_secundaria');

    // Siempre limpia los combos de empleados
    $('#id_responsable_obra').val(null).trigger('change');
    $('#id_entrega_articulos').val(null).trigger('change');
    $('#id_recibe_articulos').val(null).trigger('change');

    // Cargar empleados y setear valores seleccionados después de cargar
    cargarEmpleados(function() {
        if (solicitud) {
            if (solicitud.id_responsable_obra) {
                $('#id_responsable_obra').val(solicitud.id_responsable_obra).trigger('change');
            }
            if (solicitud.id_entrega_articulos) {
                $('#id_entrega_articulos').val(solicitud.id_entrega_articulos).trigger('change');
            }
            if (solicitud.id_recibe_articulos) {
                $('#id_recibe_articulos').val(solicitud.id_recibe_articulos).trigger('change');
            }
        }
    });

    if (solicitud && solicitud.id_orden) {
        formModalLabel.textContent = 'Editar Solicitud de Material';

        // Primero setea los valores simples
        Object.keys(solicitud).forEach(k => {
            if (document.getElementById(k) && k !== 'id_orden') {
                document.getElementById(k).value = solicitud[k];
            }
        });

        // Espera a que el combo se cargue, luego selecciona la orden:
        cargarOrdenes(solicitud.id_orden).then(() => {
            document.getElementById('id_orden').value = solicitud.id_orden;
            $('#id_orden').trigger('change'); // si usas select2
        });

        // Mostrar el campo de estado y cargar opciones
        divEstado.style.display = 'block';
        cargarEstadosSolicitudMaterial(solicitud.id_estado_solicitud);

        // Esperar a que se carguen bodegas para setear los combos correctamente
        setTimeout(function() {
            if (selectBodega && solicitud.id_bodega_principal) {
                selectBodega.value = solicitud.id_bodega_principal;
                $('#id_bodega_principal').trigger('change');
            }
            setTimeout(function() {
                if (selectSecundaria && solicitud.id_bodega_secundaria) {
                    selectSecundaria.value = solicitud.id_bodega_secundaria;
                    $('#id_bodega_secundaria').val(solicitud.id_bodega_secundaria).trigger('change');
                }
            }, 200);
        }, 200);

        if (solicitud.id_estado_solicitud == 3 || solicitud.id_estado_solicitud == 5 || solicitud.id_estado_solicitud == 4) {
            bloquearFormularioSolicitudMaterial(true);
        } else {
            bloquearFormularioSolicitudMaterial(false);
        } 
    } else {
        formModalLabel.textContent = 'Agregar Solicitud de Material';
        document.getElementById('formSolicitud').reset();
        divEstado.style.display = 'none';
        bloquearFormularioSolicitudMaterial(false);
        cargarOrdenes(0);

        // Fecha por defecto
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const fechaHoy = `${yyyy}-${mm}-${dd}`;
        document.getElementById('fecha_solicitud').value = fechaHoy;

        // LIMPIAR EL ID para crear nueva solicitud:
        document.getElementById('id_solicitud_material').value = '';

        // Después de cargar bodegas (si ya cargó), sincroniza los selects
        setTimeout(function() {
            let selectBodega = document.getElementById('id_bodega_principal');
            let selectSecundaria = document.getElementById('id_bodega_secundaria');
            if (selectBodega && selectBodega.options.length > 0) {
                selectBodega.selectedIndex = 0;
                $('#id_bodega_principal').trigger('change');
                // Selecciona también la secundaria igual a la principal
                let principalValue = selectBodega.value;
                if (selectSecundaria) {
                    selectSecundaria.value = principalValue;
                    $('#id_bodega_secundaria').val(principalValue).trigger('change');
                }
            }
        }, 200);
    }
}

function editarSolicitud(id_solicitud_material) {
	fetch(`../procesos/ordenestrabajo/proceso_solicitud_material.php?action=obtener&id_solicitud_material=${id_solicitud_material}`)
		.then(resp => resp.json())
		.then(solicitud => mostrarFormulario(solicitud));
}
function guardarSolicitud() {
	const formSolicitud = document.getElementById('formSolicitud');
	const datos = new FormData(formSolicitud);
	datos.append('action', 'guardar');
	fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php', {
		method: 'POST',
		body: datos
	})
	.then(resp => resp.json())
	.then(r => {
		if(r.success === false) {
			Swal.fire({
				title: r.mensaje,
				icon: "warning",
				draggable: true,
			});
			return; // No cerrar modal ni actualizar
		}
		Swal.fire({
			title: r.mensaje,
			icon: "success",
			draggable: true,
		}); 
		document.getElementById('formModal').style.display = 'none';
		cargarOrdenes(0);
		document.getElementById('id_solicitud_material').value = '';
		cargarSolicitudes();
	});
}

window.onclick = function(event) {
	const formModal = document.getElementById('formModal');
	if (event.target == formModal) {
		formModal.style.display = "none";
		document.getElementById('formSolicitud').reset();
	}
}
function pintarGrid(solicitudes) { 
   const $grid = $('#gridSolicitudes');
    // Destruye el DataTable ANTES de modificar el contenido
    if ($.fn.DataTable.isDataTable($grid)) {
        $grid.DataTable().destroy();
    } 
    const tbody = document.querySelector('#gridSolicitudes tbody');
    tbody.innerHTML = ''; // Limpia

	solicitudes.forEach(solicitud => {
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td>${solicitud.id_solicitud_material}</td>
			<td>${solicitud.orden_identificador ? solicitud.orden_identificador: ''}</td>
			<td>${solicitud.fecha_solicitud}</td>
			<td>${solicitud.bodega_principal}</td>
			<td>${solicitud.bodega_secundaria}</td>
			<td>${solicitud.responsable_obra}</td>
			<td>${solicitud.estado_nombre}</td>
			<td>${solicitud.entrega_articulos}</td>
			<td>${solicitud.recibe_articulos}</td> 
			<td>
				<button class="btn btn-warning" onclick="editarSolicitud(${solicitud.id_solicitud_material})"><i class="glyphicon glyphicon-edit"></i></button>
				<a class="btn btn-primary" href="detalle_solicitud_material.php?id_solicitud_material=${solicitud.id_solicitud_material}&id_estado_solicitud=${solicitud.id_estado_solicitud}">Detalle</a>
			</td>
		`;
		tbody.appendChild(tr);
	});
	 // DataTable responsive
    if ($.fn.DataTable.isDataTable('#gridSolicitudes')) {
        $('#gridSolicitudes').DataTable().destroy();
    }
    $('#gridSolicitudes').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']] // Ordena por la primera columna (ID) ascendente
    });
}
function bloquearFormularioSolicitudMaterial(bloquear) {
    // Todos los campos del form
    const formSolicitud = document.getElementById('formSolicitud');
    Array.from(formSolicitud.elements).forEach(el => {
        // No bloquea el botón cancelar ni el cierre
        if(el.id !== "btnCancelar" && el.id !== "closex" && el.type !== "button"){
            el.disabled = bloquear;
        }
    });
    // Botón guardar
    document.getElementById('btnGuardar').style.display = bloquear ? 'none' : '';
}

// --------- EXPORTACIÓN A EXCEL Y PDF ---------
$(document).on('click', '#btnExportExcel', function() {
    var table = document.getElementById('gridSolicitudes');
    var rows = Array.from(table.querySelectorAll('tbody tr'));
    var headerCells = Array.from(table.querySelectorAll('thead tr th'));
    var header = headerCells.slice(0,9).map(cell => cell.innerText);

    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('SolicitudesMateriales');

    let headerRow = worksheet.addRow(header);
    headerRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: "FF046b8b" }
        };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.border = {
            top: {style:'thin'}, left: {style:'thin'},
            bottom: {style:'thin'}, right: {style:'thin'}
        };
    });

    rows.forEach(row => {
        let dataRow = Array.from(row.cells).slice(0,9).map(cell => cell.innerText);
        worksheet.addRow(dataRow);
    });

    worksheet.columns.forEach(function(column, i) {
        let maxLength = 10;
        column.eachCell({ includeEmpty: true }, function(cell) {
            let cellValue = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, cellValue.length + 2);
        });
        column.width = maxLength;
    });

    workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "SolicitudesMateriales.xlsx");
    });
});

$(document).on('click', '#btnExportPDF', function() {
    var table = document.getElementById('gridSolicitudes');
    var headerCells = Array.from(table.querySelectorAll('thead tr th'));
    var header = headerCells.slice(0,9).map(cell => cell.innerText);
    var bodyRows = [];
    $('#gridSolicitudes tbody tr').each(function() {
        var cols = [];
        $(this).find('td').each(function(i) {
            if (i < 9) cols.push($(this).text().trim());
        });
        if(cols.length) bodyRows.push(cols);
    });

    var doc = new window.jspdf.jsPDF();
    doc.text("Solicitudes de Materiales", 14, 18);
    doc.autoTable({
        head: [header],
        body: bodyRows,
        startY: 22,
        styles: { fontSize: 10 },
        headStyles: {
            fillColor: [4, 107, 139],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        }
    });
    doc.save("SolicitudesMateriales.pdf");
});
</script>
<?php 
}else{
	header("location:../index.php");
}
?>