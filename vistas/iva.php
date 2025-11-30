<?php 
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){ 
/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "IVA");
if (!$acceso) {
    header("location:../index.php");
    exit();
} 
 ?> 
<!DOCTYPE html>
<html>
<head>
    <title>Editar IVA</title>
   <?php require_once "menu.php"; ?>
</head>
<body>
<div class="container">
    <h3>Configurar de IVA</h3>
    <table id="gridIVA" class="display">
        <thead>
            <tr>
                <th>ID</th>
                <th>Porcentaje</th>
                <th>CODIGO SRI</th>
                <th>Código Porc. IVA</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Se carga por JS -->
        </tbody>
    </table>

    <!-- Modal editar IVA -->
    <div id="formModalIVA" class="modal" tabindex="-1" role="dialog" aria-labelledby="modalIVALabel">
        <form id="formIVA" class="modal-content">
            <div class="modal-header">
                <h3 id="modalIVALabel">Editar IVA</h3>
                <button id="closexIVA" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_iva" id="id_iva">
                <label class="label-custom">Porcentaje:</label>
                <input class="form-control input-sm" type="number" step="0.01" name="monto" id="monto" required>
                <label class="label-custom">CODIGO SRI:</label>
                <input class="form-control input-sm" type="text" name="CODIGO_SRI" id="CODIGO_SRI" required>
                <label class="label-custom">Código Porc. IVA:</label>
                <input class="form-control input-sm" type="text" name="Codigo_Porc_Iva" id="Codigo_Porc_Iva" required>
            </div>
            <div class="modal-footer" style="text-align:center;">
                <button type="button" id="btnCancelarIVA" class="btn btn-danger">CANCELAR</button>
                <button type="submit" id="btnGuardarIVA" class="btn btn-success">GUARDAR</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
<style>
.modal-header .close { margin-top: -50px !important; }
.modal-header { color: #fff !important; border-bottom: 1px solid #4e8307 !important; background: #046b8b; padding: 10px !important;}
table { width: 100%; border-collapse: collapse; margin-bottom: 30px;}
th, td { border: 1px solid #ccc; padding: 8px; text-align: center;}
th { background: #f5f5f5;}
.modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); justify-content: center; align-items: center;}
#gridIVA tbody tr:hover { background-color: #e0f7fa; cursor: pointer; transition: background 0.2s;}
label { display: flex; flex-direction: column; font-size: 14px;}
input, select, textarea { padding: 6px; font-size: 15px; border: 1px solid #bbb; border-radius: 5px;}
label { display: inline-block; max-width: 100%; margin-bottom: 5px; font-weight: bold;}
</style>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', () => {
    cargarIVA();

    const formModalIVA = document.getElementById('formModalIVA');
    const formIVA = document.getElementById('formIVA');
    const btnCancelarIVA = document.getElementById('btnCancelarIVA');
    const btnclosexIVA = document.getElementById('closexIVA');

    btnCancelarIVA.onclick = () => cerrarModalIVA();
    btnclosexIVA.onclick = () => cerrarModalIVA();

    formIVA.onsubmit = function(e) {
        e.preventDefault();
        guardarIVA();
    };
});

function cargarIVA() {
    fetch('../procesos/iva/proceso_iva.php?action=listar')
    .then(resp => resp.json())
    .then(data => {
        pintarGridIVA(data);
    });
}
function mostrarFormularioIVA(iva = null) {
    const formModalIVA = document.getElementById('formModalIVA');
    const modalIVALabel = document.getElementById('modalIVALabel');
    formModalIVA.style.display = 'flex';
    if (iva) {
        modalIVALabel.textContent = 'Editar IVA';
        document.getElementById('id_iva').value = iva.id_iva;
        document.getElementById('monto').value = iva.monto;
        document.getElementById('CODIGO_SRI').value = iva.CODIGO_SRI;
        document.getElementById('Codigo_Porc_Iva').value = iva.Codigo_Porc_Iva;
    }
}
function cerrarModalIVA() {
    document.getElementById('formModalIVA').style.display = "none";
    document.getElementById('formIVA').reset();
}
function guardarIVA() {
    const form = document.getElementById('formIVA');
    const datos = new FormData(form);
    datos.append('action', 'editar');
    fetch('../procesos/iva/proceso_iva.php', {
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
        cargarIVA();
        cerrarModalIVA();
    });
}
window.onclick = function(event) {
    const formModalIVA = document.getElementById('formModalIVA');
    if (event.target == formModalIVA) {
        cerrarModalIVA();
    }
}
function pintarGridIVA(items) {
  
     const $grid = $('#gridIVA');
    // Destruye el DataTable ANTES de modificar el contenido
    if ($.fn.DataTable.isDataTable($grid)) {
        $grid.DataTable().destroy();
    } 
    const tbody = document.querySelector('#gridIVA tbody');
    tbody.innerHTML = ''; // Limpia
 
    items.forEach(iva => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${iva.id_iva}</td>
            <td>${iva.monto}</td>
            <td>${iva.CODIGO_SRI}</td>
            <td>${iva.Codigo_Porc_Iva}</td>
            <td>
                <button class="btn btn-warning" title="Editar" onclick='mostrarFormularioIVA(${JSON.stringify(iva)})'><i class="glyphicon glyphicon-edit"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    // DataTable
    if ($.fn.DataTable.isDataTable('#gridIVA')) {
        $('#gridIVA').DataTable().destroy();
    }
    $('#gridIVA').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
}
</script>
<?php 
}else{
    header("location:../index.php");
}
?>