<?php
	// Ajustado a Docker en el puerto 3307
	define('db_Servidor' , '127.0.0.1;port=3307');
	define('db_Basedato' , 'prueba');
	define('db_Usuario'  , 'root');
	define('db_Contra'   , 'Yugrant@13');
	
	// Ruta absoluta
	define('appRoot', dirname(dirname(__FILE__)));

	// Ruta relativa
	define('urlRoot', 'http://localhost/mvcPHP');

	// Nombre del sitio
	define('siteName', 'Ejemplo de MVC con PHP');
?>