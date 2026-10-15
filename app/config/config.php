<?php
// Database params in constant
const db_Servidor = 'localhost:3307';
const db_Basedato = 'NorthWind';
const db_Usuario = 'root';
const db_Contra = 'Yugrant@13';

// Absolute address
define('appRoot', dirname(__FILE__, 2));

// Retrieves the current protocol (http/https), host and port dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Project subdirectory
$appFolder = '/mvcPHP';

// Relative address
define('urlRoot', $protocol . $host . $appFolder);

// Global site name
const siteName = 'Ejemplo de MVC con PHP';

// Define the application environment ('development' or 'production')
const ENVIRONMENT = 'production';

if (ENVIRONMENT === 'production') {
    // Suppress the display of errors and warnings on the screen
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');

    // Optional: Log errors to an internal log file instead of displaying them
    ini_set('log_errors', '1');
    ini_set('error_log', appRoot . '/logs/php_errors.log');
} else {
    // Development environment (Shows all errors for quick debugging)
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
