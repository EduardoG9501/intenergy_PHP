<?php 

	class conectar{
		private $servidor="localhost";
		private $usuario="intenerg_intenerg";
		private $password="f4RJOM0vC+C8";
		private $bd="intenerg_ventas";

		public function conexion(){
			$conexion=mysqli_connect($this->servidor,
									 $this->usuario,
									 $this->password,
									 $this->bd);
			return $conexion;
		}
	}


 ?>