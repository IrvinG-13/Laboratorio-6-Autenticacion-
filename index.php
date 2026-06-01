<?PHP
session_start();  
include ("clases/mysql.inc.php");	
$db = new mod_db();



include("clases/SanitizarEntrada.php");
include("comunes/loginfunciones.php");
include("clases/objLoginAdmin.php");

	
$tolog=false;
 
 // $topanel=false;
 if (isset($_POST["tolog"]))
 	
 	$tolog = $_POST["tolog"];
 
 
 
 
  //$tolog es el nombre de un hidden del form de login, si no llegara a funcionar en hosting, se debe obtener de $_POST
    if(isset($tolog)&&($tolog=="true")&& ($_SERVER['REQUEST_METHOD'] === 'POST') ){
		
		//echo "<pre>";
		//var_dump($_SERVER);
		//echo"</pre>";
             
			$Usuario = $_POST['usuario'];
			$ClaveKey = $_POST['contrasena'];
			//echo "3l usuario es: ".$Usuario."<br>";
			//echo "3l ClaveKey es: ".$ClaveKey."<br>";

			echo "La dirección IP es ".$_SERVER['REMOTE_ADDR'];
			$ipRemoto = $_SERVER['REMOTE_ADDR'];

			$Logearme = new ValidacionLogin($Usuario, $ClaveKey,$ipRemoto, $db);
			
		
			if ($Logearme->logger()){
					$Logearme->autenticar();
				if ($Logearme->getIntentoLogin()){
					//echo "Se ha loggeado el usuario satisfactoriamente <br>";
					//Comenzar a Crear las SESIONES
					$_SESSION['autenticado']= "SI";
					$_SESSION['Usuario']= $Logearme->getUsuario();
					//Redireccionar a la página principal.....
					
					
					$Logearme->registrarIntentos();
					 $tolog=false;
					 redireccionar("formularios/PanelControl.php");
					// Si es exitoso puedo guardar en la base de datos el intento 
					//  y desde que ip
					// Sino lo es también debo guardar el IP
				}else {
					
					$Logearme->registrarIntentos();
					$_SESSION["emsg"] =1;
					//echo "ocurrió un error ";
					 redireccionar("login.php");		
				}
			}else {
				//echo "hola como estas logger <br>";
				$_SESSION["emsg"] =1;
				redireccionar("login.php");
			}

			
	    
    } else {
		//echo "hola como estas<br>";
		redireccionar("login.php");
	}
	
 
?>