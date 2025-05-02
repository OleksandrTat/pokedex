<?php
/**
 * Archivo de conexión a la base de datos.
 * Contiene las credenciales y establece la conexión MySQLi.
 */

// Configuración de la base de datos
$servername = "localhost";  // Servidor donde está alojada la BD
$username = "root";         // Usuario de MySQL (modificar según entorno)
$password = "";             // Contraseña del usuario (modificar según entorno)
$dbname = "pokemon";     // Nombre de la base de datos

// Crear conexión usando MySQLi (orientado a objetos)
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Si hay error, detener ejecución y mostrar mensaje
    die("Error de conexión: " . $conn->connect_error);
}

// Opcional: Configurar el charset a utf8 para caracteres especiales
$conn->set_charset("utf8mb4");
?>