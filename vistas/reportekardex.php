<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
    require_once "../clases/Conexion.php"; 
    require_once '../clases/Usuarios.php';
    $usuarioObj = new usuarios();
    $acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "ENTRADA / SALIDA (KARDEX)");
    if (!$acceso) {
        header("location:../index.php");
        exit();
    } 
?> 
<!DOCTYPE html>
<html>
<head>
    <title>REPORTES → ENTRADA / SALIDA (KARDEX)</title>
    <?php require_once "menu.php"; ?>
    <?php  
    $c= new conectar();
    $conexion=$c->conexion(); 
    ?>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- ExcelJS y FileSaver para exportar Excel con formato -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
    <style>
        .panel-filtros {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 20px 20px 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px #ddd;
            border: 2px solid #cce6ff;
        }
        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 18px 28px;
            align-items: end;
        }
        .filtro-grupo {
            display: flex;
            flex-direction: column;
            min-width: 180px;
            margin-bottom: 0;
        }
        .filtro-grupo label {
            font-weight: 500;
            margin-bottom: 4px;
        }
        .btns-flex {
            grid-column: 1 / 6;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 18px;
            margin-top: 16px;
        }
        .titulo-reporte {
            text-decoration: underline;
            color: #1c5d99;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .select2-container { min-width: 180px; }
        .table th, .table td { text-align: center; }
        .btn-success, .btn-primary, .btn-danger {
            min-width: 150px;
            font-size: 16px;
        }
        .btn-danger {
            background: #c0392b !important;
            border: 1px solid #900;
        }
        .btn-success {
            background: #136f32 !important;
            border: none;
        }
        .btn-primary {
            background: #2980b9 !important;
            border: none;
        }
        @media (max-width: 1200px) {
            .filtros-grid { grid-template-columns: repeat(2, 1fr);}
            .btns-flex { grid-column: 1 / 3; }
        }
        @media (max-width: 768px) {
            .filtros-grid { grid-template-columns: 1fr;}
            .btns-flex { grid-column: 1 / 2; flex-direction: column; gap: 12px;}
        }
        @media print {
            body * { visibility: hidden; }
            #tablaKardex, #tablaKardex * { visibility: visible; }
            #tablaKardex { position: absolute; left: 0; top: 0; width: 100%; }
        }
        .fa-file-pdf:before { color: #fffefe; }
        tr.eliminado { background: #f7d7d7 !important; color: #a33 !important; }
        tfoot tr {
            background: #2986C5 !important;
            color: #fff !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container" id="containarDatosMaestros"> 
        <div class="titulo-reporte">ENTRADA / SALIDA (KARDEX)</div>
        <img id="logoEmpresa" src="../img/InteNergy.jpg" style="display:none;" />
        <div class="panel-filtros">
            <form id="formKardexFiltros" autocomplete="off">
                <div class="filtros-grid">
                    <!-- Fecha desde / hasta -->
                    <div class="filtro-grupo">
                        <label class="label-custom" for="fecha_desde">Desde:</label>
                        <input type="date" class="form-control" name="fecha_desde" id="fecha_desde" value="<?=date('Y-m-01')?>">
                        <label class="label-custom" for="fecha_hasta" style="margin-top:10px;">Hasta:</label>
                        <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta" value="<?=date('Y-m-d')?>">
                    </div>
                    <!-- Bodega principal -->
                    <div class="filtro-grupo">
                        <label class="label-custom" for="bodega_principal">B. Lugar:</label>
                        <select name="bodega_principal" id="bodega_principal" class="form-control select2">
                            <option value="">Todos</option>
                            <?php
                            $bodegas = mysqli_query($conexion, "SELECT id_bodega, nombreBodega FROM bodegas WHERE estado=1");
                            while($row = mysqli_fetch_assoc($bodegas)){
                                echo "<option value='{$row['id_bodega']}'>{$row['nombreBodega']}</option>";
                            }
                            ?>
                        </select>
                        <label class="label-custom" for="obra" style="margin-top:10px;">Nombre de Obra:</label>
                        <select name="obra" id="obra" class="form-control select2">
                            <option value="">Todos</option>
                            <?php
                            $obras = mysqli_query($conexion, "SELECT id, nombre FROM obra WHERE estado=1 ORDER BY nombre ASC");
                            while($row = mysqli_fetch_assoc($obras)){
                                echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Proyecto + tipo/subtipo -->
                    <div class="filtro-grupo">
                        <label class="label-custom" for="proyecto">Proyecto:</label>
                        <select name="proyecto" id="proyecto" class="form-control select2">
                            <option value="">Todos</option>
                            <?php
                            $proyectos = mysqli_query($conexion, "SELECT id, nombre FROM proyecto WHERE estado=1 ORDER BY nombre ASC");
                            while($row = mysqli_fetch_assoc($proyectos)){
                                echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                        <label class="label-custom" for="tipo_movimiento">Tipo de Movimiento:</label>
                        <select name="tipo_movimiento" id="tipo_movimiento" class="form-control select2">
                            <option value="">Todos</option>
                            <?php
                            $tipos = mysqli_query($conexion, "SELECT id_tipo_movimiento, tipo FROM tipo_movimiento WHERE estado=1");
                            while($row = mysqli_fetch_assoc($tipos)){
                                echo "<option value='{$row['id_tipo_movimiento']}'>{$row['tipo']}</option>";
                            }
                            ?>
                        </select>
                       
                    </div>
                     <!-- Producto -->
                    <div class="filtro-grupo">
                         <label class="label-custom" for="sub_tipo_movimiento" style="margin-top:10px;">Sub Tipo de Movimiento:</label>
                        <select name="sub_tipo_movimiento" id="sub_tipo_movimiento" class="form-control select2">
                            <option value="">Todos</option>
                            <?php
                            $subs = mysqli_query($conexion, "SELECT id_sub_tipo_movimiento, sub_tipo FROM sub_tipo_movimiento WHERE estado=1");
                            while($row = mysqli_fetch_assoc($subs)){
                                echo "<option value='{$row['id_sub_tipo_movimiento']}'>{$row['sub_tipo']}</option>";
                            }
                            ?>
                        </select>
                        <label class="label-custom" for="producto">Producto:</label>
                        <select name="producto" id="producto" class="form-control select2">
                            <option value="">Todos</option>
                            <?php
                            $productos = mysqli_query($conexion, "SELECT id_producto, nombre FROM articulos WHERE estado=1");
                            while($row = mysqli_fetch_assoc($productos)){
                                echo "<option value='{$row['id_producto']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select> 
                    </div>
                    <!-- Botones -->
                    <div class="btns-flex">
                        <button type="submit" class="btn btn-success btn-block"><i class="fa fa-search"></i> Buscar</button>
                        <button type="button" class="btn btn-primary btn-block" id="exportExcel"><i class="fa fa-file-excel"></i> Exportar a Excel</button>
                        <button type="button" class="btn btn-danger btn-block" id="imprimirPDF"><i class="fa fa-file-pdf"></i> Imprimir PDF</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="tablaKardexLoad"></div>
    </div>
<script type="text/javascript">
$(document).ready(function(){

    $('.select2').select2({ width: 'resolve', placeholder: "Seleccione...", allowClear: true });

    $("#tipo_movimiento").change(function(){
        var idTipo = $(this).val();
        $("#sub_tipo_movimiento").html('<option value="">Cargando...</option>');
        $.get("reportes/get_subtipos_movimiento.php", { id_tipo_movimiento: idTipo }, function(data){
            $("#sub_tipo_movimiento").html(data);
            $("#sub_tipo_movimiento").val('').trigger('change');
        });
    });

    $("#proyecto").change(function(){
        var idProyecto = $(this).val();
        $("#obra").html('<option value="">Cargando...</option>');
        $.get("reportes/get_obras_proyecto.php", { id_proyecto: idProyecto }, function(data){
            $("#obra").html(data);
            $("#obra").val('').trigger('change');
        });
    });

    cargarTablaKardex();

    $("#formKardexFiltros").submit(function(e){
        e.preventDefault();
        cargarTablaKardex();
    });

    $('#exportExcel').click(function(){
        exportarExcelKardex();
    });

    $('#imprimirPDF').on('click', function(){
        var filtrosAplicados = {
            "Lugar": $('#bodega_principal option:selected').text() || 'Todas',
            "Nombre de Obra": $('#obra option:selected').text() || 'Todas',
            "Proyecto": $('#proyecto option:selected').text() || 'Todos',
            "Producto": $('#producto option:selected').text() || 'Todos',
            "Tipo de Movimiento": $('#tipo_movimiento option:selected').text() || 'Todos',
            "Sub Tipo de Movimiento": $('#sub_tipo_movimiento option:selected').text() || 'Todos'
        };
        const resultados = obtenerDatosDeTablaKardex('tablaKardex');
        exportarTablaKardexAPDF(resultados, filtrosAplicados);
    });

});

function cargarTablaKardex() {
    var filtros = $("#formKardexFiltros").serialize();
    $('#tablaKardexLoad').load("reportes/tablaKardex.php?" + filtros, function(){
        var $tabla = $('#tablaKardex');
        if ($tabla.length) {
            var dt = $tabla.DataTable({
                responsive: true,
                destroy: true,
                order: [[0, 'desc']],
                drawCallback: function(settings) {
                    calcularYMostrarTotalesKardex();
                }
            });

            $tabla.on('search.dt page.dt order.dt', function() {
                calcularYMostrarTotalesKardex();
            });

            calcularYMostrarTotalesKardex();
        }
    });
}

function calcularYMostrarTotalesKardex() {
    var table = $('#tablaKardex').DataTable();
    var totalCantidad = 0;
    table.rows({search:'applied'}).every(function(){
        var data = this.data();
        totalCantidad += parseFloat(data[8]) || 0;
    });

    if ($('#tablaKardex tfoot').length === 0) {
        $('#tablaKardex').append(`
        <tfoot>
            <tr style="background:#2986C5;color:#fff;font-weight:bold;">
                <td colspan="8" style="text-align:right;">TOTAL:</td>
                <td id="totalCantidad"></td>
                <td></td>
            </tr>
        </tfoot>`);
    }
    $('#totalCantidad').text(totalCantidad.toFixed(2));
}

// --- FUNCION EXPORTAR EXCEL CON HEADER Y FOOTER AZUL ---
function exportarExcelKardex(){
    var table = document.getElementById('tablaKardex');
    var headers = Array.from(table.querySelectorAll('thead tr th')).map(cell => cell.innerText);

    var bodyRows = [];
    Array.from(table.querySelectorAll('tbody tr')).forEach(row => {
        var cols = Array.from(row.cells).map(cell => cell.innerText);
        bodyRows.push(cols);
    });

    var totalCantidad = 0;
    var tableDT = $('#tablaKardex').DataTable();
    tableDT.rows({search:'applied'}).every(function(){
        var data = this.data();
        totalCantidad += parseFloat(data[8]) || 0;
    });

    var footRow = ["", "", "", "", "", "", "", "TOTAL:", totalCantidad.toFixed(2), ""];

    var workbook = new ExcelJS.Workbook();
    var worksheet = workbook.addWorksheet('Kardex');

    let headerRow = worksheet.addRow(headers);
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
    totalRow.getCell(8).alignment = { horizontal: "left", vertical: "middle" };

    worksheet.columns.forEach(function(column, i) {
        let maxLength = 10;
        column.eachCell({ includeEmpty: true }, function(cell) {
            let cellValue = cell.value ? cell.value.toString() : "";
            maxLength = Math.max(maxLength, cellValue.length + 2);
        });
        column.width = maxLength;
    });

    workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: "application/octet-stream" }), "ReporteKardex.xlsx");
    });
}

// --- FUNCION EXPORTAR PDF CON HEADER Y FOOTER AZUL ---
function exportarTablaKardexAPDF(resultados, filtrosAplicados) {
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
    doc.text("ENTRADA / SALIDA (KARDEX)", pageWidth / 2, y, { align: "center" });
    const x = 70;
    doc.setFontSize(16);
    doc.setTextColor(40, 55, 71);
    doc.text("DESDE: " +  $('#fecha_desde').val() + " HASTA: " + $('#fecha_hasta').val(), pageWidth / 2, x, { align: "center" });

    doc.setFontSize(12);
    doc.setTextColor(44, 62, 80);
    let filtroY = 100;
    doc.text('Filtros aplicados:', 40, filtroY);
    doc.line(40, 100 + 2, x + 60, 100 + 2);

    filtroY += 18;
    Object.entries(filtrosAplicados).forEach(([key, value]) => {
        doc.text(`${key}: ${value}`, 50, filtroY);
        filtroY += 16;
    });
    filtroY += 10;

    var totalCantidad = 0;
    resultados.forEach(r => {
        totalCantidad += parseFloat(r.campo9) || 0;
    });

    const columns = [
        { header: 'ID ORDEN', dataKey: 'campo1' },
        { header: 'PROYECTO', dataKey: 'campo2' },
        { header: 'NOMBRE DE LA OBRA', dataKey: 'campo3' },
        { header: 'LUGAR', dataKey: 'campo4' },
        { header: 'FECHA', dataKey: 'campo5' },
        { header: 'TIPO MOVIMIENTO', dataKey: 'campo6' },
        { header: 'SUB TIPO MOVIMIENTO', dataKey: 'campo7' },
        { header: 'ARTICULO', dataKey: 'campo8' },
        { header: 'CANTIDAD', dataKey: 'campo9' },
        { header: '#DOCUMENTO', dataKey: 'campo10' }
    ];

    const totalRow = {
        campo1: "", campo2: "", campo3: "", campo4: "", campo5: "", campo6: "", campo7: "TOTAL:",
        campo8: "",
        campo9: totalCantidad.toFixed(2),
        campo10: ""
    };

    doc.autoTable({
        columns: columns,
        body: resultados,
        foot: [totalRow],
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
    doc.text(`Generado el: ${fecha} | Usuario:` +  '<?php echo $_SESSION['usuario']; ?>', 40, doc.internal.pageSize.getHeight() - 30);
    doc.save('Reporte_Kardex.pdf');
}

// Función para obtener datos de la tabla Kardex
function obtenerDatosDeTablaKardex(tablaId) {
    const resultados = [];
    const table = $('#' + tablaId).DataTable();
    const allData = table.rows({search:'applied'}).data().toArray();
    allData.forEach(row => {
        resultados.push({
            campo1: row[0], // ID ORDEN
            campo2: row[1], // PROYECTO
            campo3: row[2], // NOMBRE DE LA OBRA
            campo4: row[3], // LUGAR
            campo5: row[4], // FECHA
            campo6: row[5], // TIPO MOVIMIENTO
            campo7: row[6], // SUB TIPO MOVIMIENTO
            campo8: row[7], // ARTICULO
            campo9: row[8], // CANTIDAD
            campo10: row[9] // #DOCUMENTO
        });
    });
    return resultados;
}

function getBase64Image(imgElement) {
    const canvas = document.createElement("canvas");
    canvas.width = imgElement.width;
    canvas.height = imgElement.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
    return canvas.toDataURL("image/png");
}
</script>
</body>
</html>
<?php 
}else{
    header("location:../index.php");
} 
?>