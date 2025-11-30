<?php   
require_once "../../clases/Backup.php";
 
$backup = new backup();
$file_name = $backup->crearBackup();

if($file_name){
    echo json_encode(['success' => true, 'msg' => 'Copia de seguridad realizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Error al crear la copia de seguridad.']);
} 
?>