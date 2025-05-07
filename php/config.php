<?php
// config.php - Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Cambiar a tu usuario de MySQL
define('DB_PASS', '');         // Cambiar a tu contraseña de MySQL
define('DB_NAME', 'pokedex');

// Función para conectar a la base de datos
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Configurar caracteres UTF-8
    $conn->set_charset("utf8");
    
    return $conn;
}

// Función para cerrar la conexión
function closeDB($conn) {
    $conn->close();
}

// Función para ejecutar una consulta y devolver los resultados
function query($sql, $conn = null) {
    $closeConn = false;
    
    if ($conn === null) {
        $conn = connectDB();
        $closeConn = true;
    }
    
    $result = $conn->query($sql);
    
    if ($closeConn) {
        closeDB($conn);
    }
    
    return $result;
}

// Función para ejecutar una consulta preparada
function preparedQuery($sql, $types, $params, $conn = null) {
    $closeConn = false;
    
    if ($conn === null) {
        $conn = connectDB();
        $closeConn = true;
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($closeConn) {
        closeDB($conn);
    }
    
    return $result;
}