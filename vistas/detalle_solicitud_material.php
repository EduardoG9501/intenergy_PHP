<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
// Al inicio, después del session_start():
$id_bodega = $_SESSION['bodega_seleccionada'];
$nombre_bodega = "";
$c = new conectar();
$conexion = $c->conexion();
$sql = "SELECT nombreBodega FROM bodegas WHERE id_bodega = '$id_bodega' LIMIT 1";
$res = mysqli_query($conexion, $sql);
if ($row = mysqli_fetch_assoc($res)) {
    $nombre_bodega = $row['nombreBodega'];
}

if(isset($_SESSION['usuario'])){
    $id_solicitud_material = $_GET['id_solicitud_material'];
    $id_estado_solicitud = $_GET['id_estado_solicitud'];
?>
<!DOCTYPE html>
<html>
<head>  
    <title>Detalle de Solicitud de Material</title>
    <?php require_once "menu.php"; ?>
    <!-- Librerías para DataTable y jsPDF/autotable, ExcelJS, FileSaver -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <!-- ExcelJS y FileSaver para exportar Excel con formato -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
</head>
<body>
<div class="container">
    <button id="btnRegresar" class="btn btn-warning">&#8592; Regresar</button>
    <label class="label-custom">ID Solicitud: <?php echo  $id_solicitud_material;  ?> </label>  
    <label class="label-custom">; ESTADO Solicitud: <?php if ($id_estado_solicitud === "1") { echo "GRABADO";} elseif ($id_estado_solicitud === "2") echo "RECIBIDO" ;  elseif ($id_estado_solicitud === "3") echo "ENTREGADO" ;  elseif ($id_estado_solicitud === "4") echo "RECHAZADO" ; elseif ($id_estado_solicitud === "5") echo "ANULADO";  ?> </label>  
    <button type="button" class="btn btn-primary" id="exportDetalleExcel"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
    <button type="button" class="btn btn-danger" id="imprimirDetallePDF"><i class="fa fa-file-pdf"></i> Imprimir PDF</button>
    <hr>
    <!-- Logo oculto para PDF -->
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" /> 
    <!-- Formulario para agregar/editar artículo -->
    <form id="formDetalle" style="display: flex; gap: 15px; align-items: flex-end;    background:#deeff9;">
        <input type="hidden" name="id_solicitud_material_detalle" id="id_solicitud_material_detalle">
        <input type="hidden" name="id_solicitud_material" value="<?= $id_solicitud_material ?>">
        <label class="label-custom">ARTICULO: </label>  
        <select name="id_producto" id="id_producto" style="width: 300px;" required></select>
        <label class="label-custom">CANTIDAD:</label>
        <input style="width: 100px;" type="number" step="0.01" name="cantidad" id="cantidad" required>
        <div id="div_estado_articulo" style="display:none;">
          <label class="label-custom">ESTADO:</label> 
          <select name="id_detalle_articulo" id="id_detalle_articulo"></select> 
        </div> 
        <?php  if ($id_estado_solicitud === "1"   ||  $id_estado_solicitud === "2"  ) { ?> 
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
                <th>ESTADO</th>
                <th>ACCION</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se carga por JS -->
        </tbody>
    </table>
</div>

<!-- Modal Stock Insuficiente (SweetAlert2, integración visual exacta a tu sistema) -->
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
var nombreBodegaActiva = <?php echo json_encode($nombre_bodega); ?>;
// Función para obtener la imagen/logo en base64
function getBase64Image(imgElement) {
    const canvas = document.createElement("canvas");
    canvas.width = imgElement.width;
    canvas.height = imgElement.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
    return canvas.toDataURL("image/png");
}

// Llama cargarArticulos en el ready
$(document).ready(function() {
    cargarArticulos();

    // Inicializa DataTable si no lo tienes ya
    $('#gridDetalle').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });

    // Exportar a Excel con formato profesional (cabecera azul, totales)
    $('#exportDetalleExcel').click(function(){
        exportarDetalleMaterialesAExcelConFormato();
    });

    // Imprimir PDF
    $('#imprimirDetallePDF').on('click', function(){
        var resultados = obtenerDatosDeTablaDetalle('gridDetalle');
        exportarTablaDetalleAPDF(resultados);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    cargarDetalle();
    cargarEstadosDetalleArticulo();

    const formDetalle = document.getElementById('formDetalle');
    const btnCancelarDetalle = document.getElementById('btnCancelarDetalle');
    const btnRegresar = document.getElementById('btnRegresar');

    btnRegresar.onclick = () => {
        window.location.href = "solicitudmaterial.php";
    };

    // --- VALIDACIÓN DE STOCK ANTES DE GUARDAR DETALLE ---
    formDetalle.onsubmit = function(e) {
        e.preventDefault();
        validarStockAntesDeGuardar(guardarDetalle);
    };

    btnCancelarDetalle.onclick = function() {
        limpiarFormulario();
    };
});

// --- NUEVO: Validar stock antes de guardar detalle (AJAX a proceso usando bodega sesión) ---
function showStockInsuficienteModal() {
    Swal.fire({
        title: "STOCK INSUFICIENTE",
        text: "No tiene suficiente stock en la bodega " + nombreBodegaActiva + " para el artículo que desea solicitar material.",
        icon: "warning"
    });
}

function validarStockAntesDeGuardar(callback) {
    var id_producto = document.getElementById('id_producto').value;
    var cantidad = parseFloat(document.getElementById('cantidad').value);

    if (!id_producto || isNaN(cantidad) || cantidad <= 0) {
        Swal.fire("Ingrese un artículo y cantidad válida", "", "info");
        return;
    }

    fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=get_stock_producto_bodega&id_producto=' + encodeURIComponent(id_producto))
    .then(resp => resp.json())
    .then(data => {
        if (parseFloat(data.stock) < cantidad) {
            showStockInsuficienteModal();
        } else {
            callback();
        }
    });
}

// --- NUEVO: Exportar a Excel con formato profesional (cabecera azul, totales) ---
function exportarDetalleMaterialesAExcelConFormato() {
    var table = document.getElementById('gridDetalle');
    // Obtener filas visibles del DataTable, solo las primeras 4 columnas (sin ACCION)
    var dt = $('#gridDetalle').DataTable();
    var data = dt.rows({search:'applied'}).data().toArray();
    // Encabezados
    let header = ['ID', 'ARTICULO', 'CANTIDAD', 'ESTADO'];
    // Cuerpo
    var bodyRows = [];
    data.forEach(row => {
        bodyRows.push([row[0], row[1], row[2], row[3]]);
    });
    // Total en columna CANTIDAD (columna 2, índice 2)
    let totalCantidad = 0;
    bodyRows.forEach(row => {
        totalCantidad += parseFloat(row[2]) || 0;
    });
    let footRow = ["", "TOTAL:", totalCantidad.toFixed(2), ""];

    // ExcelJS
    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('Detalle Materiales');

    // Header azul
    let headerRow = worksheet.addRow(header);
    headerRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
    });

    // Datos
    bodyRows.forEach(row => worksheet.addRow(row));

    // Footer azul con totales
    var totalRow = worksheet.addRow(footRow);
    totalRow.eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: "FF2986C5" } };
        cell.alignment = { horizontal: "right", vertical: "middle" };
        cell.border = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
    });
    totalRow.getCell(2).alignment = { horizontal: "left", vertical: "middle" }; // "TOTAL:"

    worksheet.columns.forEach(function(column) {
        let maxLength = 10;
        column.eachCell({ includeEmpty: true }, function(cell) {
            let cellValue = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, cellValue.length + 2);
        });
        column.width = maxLength;
    });

    workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Detalle_Materiales.xlsx");
    });
}

// --- Extrae los datos del grid (sin ACCION) ---
function obtenerDatosDeTablaDetalle(tablaId) {
    const resultados = [];
    const table = $('#' + tablaId).DataTable();
    const allData = table.rows({search:'applied'}).data().toArray();
    allData.forEach(row => {
        resultados.push({
            campo1: row[0], // ID
            campo2: row[1], // Articulo
            campo3: row[2], // Cantidad
            campo4: row[3], // Estado
        });
    });
    return resultados;
}

// --- Exporta el grid a PDF usando jsPDF y autotable ---
function exportarTablaDetalleAPDF(resultados) {
    const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });

    // Logo
    var imgElement = document.getElementById("logoEmpresa");
    var logoBase64 = "";
    if (imgElement && imgElement.complete) {
        logoBase64 = getBase64Image(imgElement);
        doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
    }

    // Títulos y encabezados
    doc.setFontSize(20);
    doc.setTextColor(40, 55, 71);
    const pageWidth = doc.internal.pageSize.getWidth();
    doc.text("DETALLE DE SOLICITUD DE MATERIALES", pageWidth / 2, 40, { align: "center" });

    doc.setFontSize(14);
    doc.setTextColor(44, 62, 80);
    doc.text("ID Solicitud: <?php echo $id_solicitud_material; ?>", pageWidth / 2, 62, { align: "center" });

    doc.setFontSize(12);
    doc.setTextColor(44, 62, 80);
    doc.text("Estado Solicitud: <?php
        if ($id_estado_solicitud === '1') { echo 'GRABADO'; }
        elseif ($id_estado_solicitud === '2') echo 'RECIBIDO';
        elseif ($id_estado_solicitud === '3') echo 'ENTREGADO';
        elseif ($id_estado_solicitud === '4') echo 'RECHAZADO';
        elseif ($id_estado_solicitud === '5') echo 'ANULADO';
    ?>", pageWidth / 2, 80, { align: "center" });

    // --- Calcular total de la columna CANTIDAD ---
    let totalCantidad = 0;
    resultados.forEach(r => {
        totalCantidad += parseFloat(r.campo3) || 0;
    });
    // --- Fila de totales ---
    const totalRow = {
        campo1: "",
        campo2: "TOTAL:",
        campo3: totalCantidad.toFixed(2),
        campo4: ""
    };

    // Tabla
    const columns = [
        { header: 'ID', dataKey: 'campo1' },
        { header: 'ARTICULO', dataKey: 'campo2' },
        { header: 'CANTIDAD', dataKey: 'campo3' },
        { header: 'ESTADO', dataKey: 'campo4' }
    ];

    doc.autoTable({
        columns: columns,
        body: resultados,
        foot: [totalRow],
        startY: 110,
        styles: {
            fontSize: 10,
            cellPadding: 5,
            fillColor: [230, 240, 255]
        },
        headStyles: {
           fillColor: [41, 134, 197],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        footStyles: {
            fillColor: [41, 134, 197],
            textColor: [255, 255, 255],
            fontStyle: 'bold'
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        margin: { left: 40, right: 40 }
    });

    // Pie de página
    const fecha = new Date().toLocaleString();
    doc.setFontSize(10);
    doc.setTextColor(127, 140, 141);
    doc.text(`Generado el: ${fecha} | Usuario: <?php echo $_SESSION['usuario']; ?>`, 40, doc.internal.pageSize.getHeight() - 30);
    doc.save('Detalle_Materiales.pdf');
}

// --- El resto de funciones (sin cambios) ---
function cargarArticulos() {
     fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=articulos')
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
    const id = <?= $id_solicitud_material ?>;
    fetch(`../procesos/ordenestrabajo/proceso_solicitud_material.php?action=listar_detalle&id_solicitud_material=${id}`)
    .then(resp => resp.json())
    .then(data => {
        pintarGridDetalle(data);
    });
}
function guardarDetalle() {
    const form = document.getElementById('formDetalle');
    const datos = new FormData(form);
    datos.append('action', 'guardar_detalle');
    fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php', {
        method: 'POST',
        body: datos
    })
    .then(resp => resp.json())
    .then(r => {
        Swal.fire({
            title: r.mensaje,
            icon: r.success ? "success" : "error",
            draggable: true,
        });
        cargarDetalle();
        limpiarFormulario();
    });
}
function editarDetalle(detalle) {
    document.getElementById('id_solicitud_material_detalle').value = detalle.id_solicitud_material_detalle;
    document.getElementById('id_producto').value = detalle.id_producto;
    document.getElementById('cantidad').value = detalle.cantidad;
    document.getElementById('btnGuardarDetalle').textContent = "GUARDAR CAMBIOS";
    document.getElementById('btnCancelarDetalle').style.display = "";

    document.getElementById('div_estado_articulo').style.display = "";
    cargarEstadosDetalleArticulo(detalle.id_detalle_articulo);;
}
function limpiarFormulario() {
    document.getElementById('formDetalle').reset();
    document.getElementById('id_solicitud_material_detalle').value = '';
    document.getElementById('btnGuardarDetalle').textContent = "AGREGAR";
    document.getElementById('btnCancelarDetalle').style.display = "none";
    document.getElementById('div_estado_articulo').style.display = "none";
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
            fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'eliminar_detalle',
                    id_solicitud_material_detalle: id
                })
            })
            .then(resp => resp.json())
            .then(r => {
                if (!r.success )
                {
                    Swal.fire({
                    title: r.mensaje,
                    icon: r.success ? "success" : "error",
                    draggable: true,
                   });
                }  
                cargarDetalle();
                limpiarFormulario();
            });
        }
    });
}
function pintarGridDetalle(detalles) { 
    const $grid = $('#gridDetalle');
    if ($.fn.DataTable.isDataTable($grid)) {
        $grid.DataTable().destroy();
    } 
    const tbody = document.querySelector('#gridDetalle tbody');
    tbody.innerHTML = '';
    detalles.forEach(det => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${det.id_solicitud_material_detalle}</td>
            <td>${det.nombre_articulo}</td>
            <td>${det.cantidad}</td>
            <td>${det.estado_detalle}</td>
            <?php  if ($id_estado_solicitud === "1"   ||  $id_estado_solicitud === "2"  ) { ?> 
            <td>
                <button class="btn btn-warning" onclick='editarDetalle(${JSON.stringify(det)})'><i class="glyphicon glyphicon-edit"></i></button>
                <button class="btn btn-danger" onclick='eliminarDetalle(${det.id_solicitud_material_detalle})'><i class="glyphicon glyphicon-trash"></i></button>
            </td>
            <?php  } else {  ?> 
             <td>
                <button disabled class="btn btn-warning" onclick='editarDetalle(${JSON.stringify(det)})'><i class="glyphicon glyphicon-edit"></i></button>
                <button disabled class="btn btn-danger" onclick='eliminarDetalle(${det.id_solicitud_material_detalle})'><i class="glyphicon glyphicon-trash"></i></button>
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
function cargarEstadosDetalleArticulo(valorActual = null) {
    fetch('../procesos/ordenestrabajo/proceso_solicitud_material.php?action=estados_articulo')
        .then(resp => resp.json())
        .then(estados => {
            const estadoSel = document.getElementById('id_detalle_articulo');
            estadoSel.innerHTML = '';
            estados.forEach(e => {
                estadoSel.innerHTML += `<option value="${e.id_estado_solicitud_articulos}" ${valorActual == e.id_estado_solicitud_articulos ? 'selected' : ''}>${e.estado}</option>`;
            });
        });
}
</script> 
<?php 
}else{
    header("location:../index.php");
}

?>