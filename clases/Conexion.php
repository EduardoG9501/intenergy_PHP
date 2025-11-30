<?php 

	class conectar{
		private $servidor="localhost";
		private $usuario="root";
		private $password="";
		private $bd="intenerg_sgi";

		public function conexion(){
			$conexion=mysqli_connect($this->servidor,
									 $this->usuario,
									 $this->password,
									 $this->bd);
			return $conexion;
		}
		public function getServidor() { 
			return $this->servidor; 
		}
        public function getUsuario()  { 
			return $this->usuario; 
		}
        public function getPassword() { 
			return $this->password; 
		}
        public function getBd(){ 
			return $this->bd; 
		}
	} 
 ?>