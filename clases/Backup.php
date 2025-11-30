<?php 
require_once "../../clases/Mysqldump.php"; // nombre exacto del archivo

class Backup {
    private $backup_dir_rel = "backup/";
    private $backup_dir;

    public function __construct() {
        $this->backup_dir = __DIR__ . "/../backup/";
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }

    public function crearBackup() {
        //Desarrollo
        //$host="localhost";
		//$user="root";
		//$pass="";
	    //$db="intenerg_sgi";

        //Produccion
         $host="localhost";
		 $user="intenerg_intenerg";
		 $pass="f4RJOM0vC+C8";
		 $db="intenerg_ventas";

        $fecha = date('Ymd_His');
        $file_name = "backup_{$db}_{$fecha}.sql";
        $ruta_relativa = $this->backup_dir_rel . $file_name; // solo ruta relativa
        $backup_file = $this->backup_dir . $file_name;

        try {
            // Instancia correcta con namespace
            $dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host=$host;dbname=$db", $user, $pass);
            $dump->start($backup_file);

            // Conexión para registrar el backup en la BD
            $mysqli = new mysqli($host, $user, $pass, $db);
            if ($mysqli->connect_error) {
                throw new Exception("Error de conexión a la base de datos: " . $mysqli->connect_error);
            }

            $stmt = $mysqli->prepare("INSERT INTO backup (fecha_crea, ruta) VALUES (?, ?)");
            $fecha_crea = date('Y-m-d H:i:s');
            $stmt->bind_param("ss", $fecha_crea, $ruta_relativa);
            $stmt->execute();
            $stmt->close();
            $mysqli->close();
            
            return $file_name;
        } catch (Exception $e) {
            file_put_contents($this->backup_dir . 'backup_error.log', date("[Y-m-d H:i:s] ") . $e->getMessage() . "\n", FILE_APPEND);
            return false;
        }
    }
}
?>