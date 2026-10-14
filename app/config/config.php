<?php
    // Conexion al servidor virtual
    define('db_Servidor' , 'localhost');
    define('db_Basedato' , 'NorthWind');
    define('db_Usuario'  , 'dinos');
    define('db_Contra'   , 'dinos123@');
    
    // Ruta absoluta
    define('appRoot', dirname(dirname(__FILE__)));

    // Ruta relativa para la máquina virtual
    define('urlRoot', 'http://192.168.0.15/mvcPHP');
    
    // Nombre del sitio
    define('siteName', 'Ejemplo de MVC con PHP');
?>