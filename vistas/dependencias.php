<?php
$PAIS = "ni"; // "ec" para Ecuador, "ni" para Nicaragua

if ($PAIS == "ec") {
    date_default_timezone_set('America/Guayaquil');
} else if ($PAIS == "ni") {
    date_default_timezone_set('America/Managua');
} 
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="stylesheet" type="text/css" href="../librerias/alertifyjs/css/alertify.css">
<link rel="stylesheet" type="text/css" href="../librerias/alertifyjs/css/themes/default.css">
<link rel="stylesheet" type="text/css" href="../librerias/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="../librerias/select2/css/select2.css">
<link rel="stylesheet" type="text/css" href="../css/menu.css">
<link rel="stylesheet" type="text/css" href="../css/custom.css">

<script src="../librerias/jquery-3.2.1.min.js"></script>
<script src="../librerias/alertifyjs/alertify.js"></script>
<script src="../librerias/bootstrap/js/bootstrap.js"></script>
<script src="../librerias/select2/js/select2.js"></script>
<script src="../js/funciones.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTable CSS  -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

<!-- DataTable JS -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<!-- Para Exportar a PDF y Excel JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 

<!-- JS de Select2 ya cargado localmente arriba -->
<!-- Exportar a excel -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

 