<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "INFORME DISPONIBILIDAD POR BODEGAS");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
?>
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES → DISPONIBILIDAD POR BODEGA</title>
     <?php require_once "menu.php"; ?> 
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <style>
        .panel-filtros { background: #f5f5f5; border-radius: 6px; padding: 16px 12px; margin-bottom: 25px; box-shadow: 0 1px 3px #ddd; }
        .form-inline .form-control, .form-inline .select2-container { margin-right: 10px; }
        .table th, .table td { text-align: center; }
        .titulo-reporte { text-decoration: underline;color:#1c5d99; font-weight:bold; font-size:20px; margin-bottom:15px; }
        @media print {
            body * { visibility: hidden; }
            #tablaDisponibilidad, #tablaDisponibilidad * { visibility: visible; }
            #tablaDisponibilidad { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .select2-container { min-width: 200px; }
    </style>
</head>
<body>
<div class="container">
    <div class="titulo-reporte">REPORTE DE DISPONIBILIDAD DE ARTÍCULOS POR BODEGA</div>
    <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
    <div class="panel-filtros">
        <form id="formFiltrosDisponibilidad" class="form-inline" autocomplete="off">
            <label for="id_bodega">Bodega:</label>
            <select id="id_bodega" name="id_bodega" class="form-control" style="width:250px"><option value="">Todas</option></select>
            <label for="id_producto">Artículo:</label>
            <select id="id_producto" name="id_producto" class="form-control" style="width:250px"><option value="">Todos</option></select>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <button type="button" id="btnExportarExcel" class="btn btn-success"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
            <button type="button" id="btnExportarPDF" class="btn btn-danger"><i class="fa fa-file-pdf"></i> Imprimir PDF</button> 
        </form>
    </div>
    <table id="tablaDisponibilidad" class="table table-bordered table-striped">
        <thead>
            <tr style="background:#3c8dbc; color:white;">
                <th>ID Artículo</th>
                <th>ID Bodega</th>
                <th>Nombre Artículo</th> 
                <th>Nombre Bodega</th>
                <th>Cantidad Disponible</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se llena por JS -->
        </tbody>
    </table>
</div>

<script>
$(document).ready(function(){
    // Inicializar combos
    $('#id_bodega, #id_producto').select2({width:'250px', placeholder:'Seleccione', allowClear:true});
    cargarCombos();

    $('#formFiltrosDisponibilidad').on('submit', function(e){
        e.preventDefault();
        buscarDisponibilidad();
    });

    $('#btnExportarExcel').on('click', function(){
        exportarExcelDisponibilidad();
    });

    $('#btnExportarPDF').on('click', function(){
        exportarTablaDisponibilidadAPDF();
    });

    buscarDisponibilidad(); // Carga inicial con todos

    function cargarCombos() {
        $.post('../procesos/reportes/informe_disponible_bodega.php', {action:'bodegas'}, function(data){
            let bodegas = JSON.parse(data);
            let $bodega = $('#id_bodega');
            $bodega.empty().append('<option value="">Todas</option>');
            bodegas.forEach(b => {
                $bodega.append(`<option value="${b.id_bodega}">${b.nombreBodega}</option>`);
            });
            $bodega.val("").trigger("change");
        });
        $.post('../procesos/reportes/informe_disponible_bodega.php', {action:'articulos'}, function(data){
            let articulos = JSON.parse(data);
            let $articulo = $('#id_producto');
            $articulo.empty().append('<option value="">Todos</option>');
            articulos.forEach(a => {
                $articulo.append(`<option value="${a.id_producto}">${a.nombre}</option>`);
            });
            $articulo.val("").trigger("change");
        });
    }

    function buscarDisponibilidad() {
        let datos = {
            action: 'listar_reporte',
            id_bodega: $('#id_bodega').val(),
            id_producto: $('#id_producto').val()
        };
        $.post('../procesos/reportes/informe_disponible_bodega.php', datos, function(data){
            let rows = JSON.parse(data);
            pintarTablaDisponibilidad(rows);
        });
    }

    function pintarTablaDisponibilidad(rows){
        let $tabla = $('#tablaDisponibilidad');
        let $tbody = $tabla.find('tbody');
        $tbody.html('');
        if ( $.fn.DataTable.isDataTable($tabla) ) {
            $tabla.DataTable().clear().destroy();
        }
        if(!Array.isArray(rows) || rows.length === 0){
            // No agregues filas manualmente. DataTables mostrará el mensaje vacío.
        }else {
            rows.forEach(r => {
                let fila = `
                    <tr>
                        <td>${r.id_producto ?? ""}</td>
                        <td>${r.id_bodega ?? ""}</td>
                        <td>${r.nombre_articulo ?? ""}</td> 
                        <td>${r.nombreBodega ?? ""}</td>
                        <td>${r.cantidad ?? ""}</td>
                    </tr>`;
                $tbody.append(fila);
            });
        }
        $tabla.DataTable({
            responsive: true,
            destroy: true,
            order: [[0, 'desc']],
            language: {
                "emptyTable": "No hay datos para los filtros seleccionados."
            }
        });
    }

    // NUEVA FUNCIÓN EXCEL ESTILO KARDEX
    function exportarExcelDisponibilidad(){
        let datos = {
            action: 'listar_reporte',
            id_bodega: $('#id_bodega').val(),
            id_producto: $('#id_producto').val()
        };
        $.post('../procesos/reportes/informe_disponible_bodega.php', datos, function(data){
            let rows = JSON.parse(data);
            // Encabezado según orden de tu tabla
            let header = ['ID Artículo', 'ID Bodega', 'Nombre Artículo', 'Nombre Bodega', 'Cantidad Disponible'];
            // Cuerpo
            let bodyRows = rows.map(r => [
                r.id_producto, r.id_bodega, r.nombre_articulo, r.nombreBodega, r.cantidad
            ]);
            // Totales
            let totalCantidad = 0;
            bodyRows.forEach(row => {
                totalCantidad += parseFloat(row[4]) || 0;
            });
            let footRow = ["", "", "", "TOTAL:", totalCantidad.toFixed(2)];

            // ExcelJS - cabecera azul, totales al pie
            var workbook = new ExcelJS.Workbook();
            var worksheet = workbook.addWorksheet('Disponibilidad Bodega');
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
            totalRow.getCell(4).alignment = { horizontal: "left", vertical: "middle" };
            worksheet.columns.forEach(function(column) {
                let maxLength = 10;
                column.eachCell({ includeEmpty: true }, function(cell) {
                    let cellValue = cell.value ? cell.value.toString() : "";
                    maxLength = Math.max(maxLength, cellValue.length + 2);
                });
                column.width = maxLength;
            });
            workbook.xlsx.writeBuffer().then(function(buffer) {
                saveAs(new Blob([buffer], { type: "application/octet-stream" }), "Reporte_Disponibilidad_Bodega.xlsx");
            });
        });
    }

    // NUEVA FUNCIÓN PDF ESTILO KARDEX
    function exportarTablaDisponibilidadAPDF() {
        const imgElement = document.getElementById("logoEmpresa");
        const logoBase64 = getBase64Image(imgElement);
        const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });

        if (logoBase64) {
            doc.addImage(logoBase64, 'PNG', 40, 20, 80, 60);
        }
        doc.setFontSize(24);
        doc.setTextColor(40, 55, 71);
        const y = 40;
        const pageWidth = doc.internal.pageSize.getWidth();
        doc.text("DISPONIBILIDAD DE ARTÍCULOS POR BODEGA", pageWidth / 2, y, { align: "center" });

        // Filtros seleccionados
        doc.setFontSize(12);
        doc.setTextColor(44, 62, 80);
        let filtroY = 100;
        const bodega = $('#id_bodega option:selected').text() || 'Todas';
        const articulo = $('#id_producto option:selected').text() || 'Todos';
        let filtros = [
            `Bodega: ${bodega}`,
            `Artículo: ${articulo}`
        ];
        doc.text('Filtros aplicados:', 40, filtroY);
        filtroY += 18;
        filtros.forEach(f => {
            doc.text(f, 50, filtroY);
            filtroY += 16;
        });
        filtroY += 10;

        // Datos para la tabla (mismo orden que tu vista)
        var table = document.getElementById('tablaDisponibilidad');
        var headers = Array.from(table.querySelectorAll('thead tr th')).map(cell => cell.innerText);
        var bodyRows = [];
        Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
            var cols = Array.from(row.cells).map(cell => cell.innerText);
            bodyRows.push(cols);
        });
        // Totales
        var totalCantidad = 0;
        bodyRows.forEach(row => {
            totalCantidad += parseFloat(row[4]) || 0;
        });
        var footRow = ["", "", "", "TOTAL:", totalCantidad.toFixed(2)];

        // Armado de tabla
        doc.autoTable({
            head: [headers],
            body: bodyRows,
            foot: [footRow],
            startY: filtroY,
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

        const fecha = new Date().toLocaleString();
        doc.setFontSize(10);
        doc.setTextColor(127, 140, 141);
        doc.text(`Generado el: ${fecha} | Usuario: <?php echo $_SESSION['usuario']; ?>`, 40, doc.internal.pageSize.getHeight() - 30);

        doc.save('Reporte_Disponibilidad_Bodega.pdf');
    }

    // FUNCION GET BASE64 DEL LOGO (si no la tienes)
    function getBase64Image(imgElement) {
        const canvas = document.createElement("canvas");
        canvas.width = imgElement.width;
        canvas.height = imgElement.height;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
        return canvas.toDataURL("image/png");
    }
});
</script>
</body>
</html>
<?php
}else{
    header("location:index.php");
}
?>