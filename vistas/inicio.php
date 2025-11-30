<?php 
	ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
    session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
    session_start();
	if(isset($_SESSION['usuario'])){
		
 ?> 
<!DOCTYPE html>
<html>
<head>
	<title>inicio</title>
	<?php require_once "menu.php"; ?>
</head>
<body> 
</body>
</html>
<?php 
	}else{
		header("location:../index.php");
	}
 ?>