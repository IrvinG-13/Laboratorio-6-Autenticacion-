<?PHP
final class ValidacionLogin{ 
    
	Private $id;
	Private $usuario;
	Private $contrasena; 
	Private $hastGenerado;
	Private $loginExitoso;
	Private $ip;
	Private $pdo;
	

	Public function __construct($usuario,$contrasena, $ipRemoto, $pdo){ 
	
		//$this->usuario  = trim($usuario); 
		//$nombreLimpio = SanitizarEntrada::limpiarCadena($nombre); 

		$this->usuario  = SanitizarEntrada::limpiarCadena($usuario); 
		$this->contrasena  = SanitizarEntrada::limpiarCadena($contrasena); 
		$this->ip  = $ipRemoto;

		$this->pdo = $pdo;

		
	} //introduceDatos

 	// Simulación de autenticación (puedes reemplazar con base de datos)

	 Private function generarHash(){

			$options = [
				// Increase the bcrypt cost from 12 to 13.
				'cost' => 13,
			];
		
			
			//$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			$this->hastGenerado =  password_hash($this->contrasena, PASSWORD_BCRYPT, $options);
			
	}// no quisiera que se generará el password en otra parte

	public function logger(){

		$usuariologueado = $this->pdo->log($this->usuario);

		if ($usuariologueado) {
				$this->id =  $usuariologueado->id;
				$this->hastGenerado =  $usuariologueado->HashMagic;
				return true;

		} else {
			   //throw new Exception("Usuario no encontrado");
			   return false;
		}
	} 
	
 	public function autenticar(){
		
			if (password_verify($this->contrasena, $this->hastGenerado)) {
				echo 'Password is valid!';
				$this->loginExitoso  = 1;
					
			} else {
				echo 'Invalid password.';
				$this->loginExitoso  = 0;
			}

	}//función Autentica


	public function getIntentoLogin(){
		return $this->loginExitoso;
	}
	

	public function getUsuario(){
		return $this->usuario;

	}
	
	public function getContrasena(){
		return $this->contrasena;
		
	}

	public function getHashGenerado(){
		return $this->hastGenerado;
		
	}
	

	public function registrarIntentos(){ 
    
	
	
	
			$cols= "Usuario,
					ipRemoto,
					deteccion_anomalia";
						
			
			$val = "'$this->usuario',   
					'$this->ip',
					'$this->loginExitoso'";
		
			//$db->insert("intentos_login",$cols,$val);


			$data = array(
			"Usuario" => "$this->usuario",
			"ipRemoto" => "$this->ip",
			"deteccion_anomalia" => $this->loginExitoso
			);
			$this->pdo->insertSeguro("intentos_login",$data);
	} 


	// // Cerrar la conexión
		// $stmt = null;
		// $pdo = null;

} //fin ValidacionLogin

/* foreach($result as $key => $value) {
	$resp[$key]=$value;
	}//fin del foreach
	*/
?>		