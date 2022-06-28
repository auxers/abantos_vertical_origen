<? 
//vemos si el usuario y contraseña es váildo 
if ($_POST["usuario"]=="Abantos Vertical" && $_POST["contrasena"]=="Abantos2014"){ 
   	//usuario y contraseña válidos 
   	//defino una sesion y guardo datos 
   	session_start(); 
    $_SESSION["autentificado"]= "SI"; 
   	header ("Location: aplicacion.php");	
}else { 
   	//si no existe le mando otra vez a la portada 
   	header("Location: index.php?errorusuario=si"); 
} 
?>