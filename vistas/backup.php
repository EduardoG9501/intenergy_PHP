<?php
ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
 session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
 session_start();
if(isset($_SESSION['usuario'])){

/*HABILITAR ACCESO AL MENU*/ 	
require_once "../clases/Conexion.php"; 
require_once '../clases/Usuarios.php';
$usuarioObj = new usuarios();
$acceso = $usuarioObj->usuarioTieneAcceso($_SESSION['usuario'], "BACKUP");
if (!$acceso) {
    header("location:../index.php");
    exit();
}

    // --- BLOQUE PARA DESCARGAR EL BACKUP SI SE SOLICITA ---
    if(isset($_GET['file'])){
        $ruta_relativa = $_GET['file'];
        $file = __DIR__ . '/../' . $ruta_relativa; // Ajusta según tu estructura

        if(file_exists($file)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }else{
            echo "Archivo no encontrado.";
            exit;
        }
    }

    // --- RESTO DE TU CÓDIGO DE LA VISTA --- 
    $c = new conectar();
    $conn = $c->conexion();

    $result = $conn->query("SELECT * FROM backup ORDER BY id DESC");
    // ... (continúa la vista como antes)
?>
<!DOCTYPE html>
<html>
<head>
    <title>Backup Base de Datos</title>
    <?php require_once "menu.php"; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
</head>
<body>
<div class="container">
    <h3>Backup de la Base de Datos</h3>
    <form id="backupForm" method="POST" action="../procesos/backup/realizar_backup.php">
        <button type="submit" class="btn btn-primary">Realizar copia de seguridad</button>
    </form>
    <div id="mensajeBackup" style="margin-top:20px;"></div>
    <hr>
    <h4>Backups Realizados</h4>
    <table id="gridBackup" class="table table-striped table-bordered"> 
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha Creación</th>
                <th>Ruta del archivo</th>
                <th>Descargar</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['fecha_crea']; ?></td>
                    <td><?php echo $row['ruta']; ?></td>
                    <td>
                        <a class="btn btn-success btn-xs" href="backup.php?file=<?php echo urlencode($row['ruta']); ?>" target="_blank">Descargar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No hay backups realizados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div> 
<script>
 $(document).ready(function() {
    $('#gridBackup').DataTable({
        responsive: true,
        destroy: true,
        order: [[0, 'desc']]
    });
});
$('#backupForm').on('submit', function(e){
    e.preventDefault();
    $('#mensajeBackup').html('');
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        dataType: 'json',
        success: function(resp){
            if(resp.success){
                $('#mensajeBackup').html('<div class="alert alert-success">' + resp.msg + '</div>');
                setTimeout(function(){ location.reload(); }, 2000);
            }else{
                $('#mensajeBackup').html('<div class="alert alert-danger">' + resp.msg + '</div>');
            }
        },
        error: function(){
            $('#mensajeBackup').html('<div class="alert alert-danger">Error de comunicación.</div>');
        }
    });
});
</script>
</body>
</html>
<?php
    $conn->close();
}else{
    header("location:index.php");
}
?>