<?php
/**
 * Archivo para manejar las búsquedas de Pokémon.
 * Recibe parámetros por GET y muestra resultados.
 */

// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar si se envió número o nombre por GET
$numero_dex = isset($_GET['numero_dex']) ? trim($_GET['numero_dex']) : null;
$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : null;

// Validar que al menos un parámetro esté presente
if (empty($numero_dex) && empty($nombre)) {
    die("Error: Debes proporcionar un número o nombre de Pokémon.");
}

// Preparar consulta SQL base con JOIN para tipos
$sql = "
    SELECT 
        p.*, 
        t1.nombre AS tipo_primario,
        t2.nombre AS tipo_secundario
    FROM pokemon p
    LEFT JOIN tipos t1 ON p.tipo_primario_id = t1.id
    LEFT JOIN tipos t2 ON p.tipo_secundario_id = t2.id
    WHERE 
";

// Añadir condición según parámetro recibido
if ($numero_dex) {
    $sql .= "p.numero_dex = ?";  // Búsqueda por número
    $tipo_parametro = "i";       // 'i' indica que es un entero
    $parametro = $numero_dex;
} else {
    $sql .= "p.nombre = ?";      // Búsqueda por nombre
    $tipo_parametro = "s";       // 's' indica que es string
    $parametro = $nombre;
}

// Preparar y ejecutar consulta
$stmt = $conn->prepare($sql);
$stmt->bind_param($tipo_parametro, $parametro);  // Previene inyecciones SQL
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontraron resultados
if ($result->num_rows === 0) {
    die("Pokémon no encontrado en la Pokédex.");
}

// Obtener datos del Pokémon
$pokemon = $result->fetch_assoc();

// -- Obtener habilidades del Pokémon --
$sql_habilidades = "
    SELECT h.nombre 
    FROM pokemon_habilidades ph
    JOIN habilidades h ON ph.habilidad_id = h.id
    WHERE ph.pokemon_id = ?
";
$stmt_h = $conn->prepare($sql_habilidades);
$stmt_h->bind_param("i", $pokemon['id']);
$stmt_h->execute();
$habilidades = $stmt_h->get_result()->fetch_all(MYSQLI_ASSOC);

// Cerrar conexiones
$stmt->close();
$stmt_h->close();
$conn->close();

// Construir URL de la imagen (usando el número de la Pokédex)
$imagen_url = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$pokemon['numero_dex']}.png";
?>

<!-- ========== HTML PARA MOSTRAR RESULTADOS ========== -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de búsqueda</title>
    <!-- Incluir mismos estilos que index.html -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="pokedex-screen">
        <div class="pokemon-image">
            <img src="<?= $imagen_url ?>" alt="<?= $pokemon['nombre'] ?>">
        </div>
        <div class="pokemon-info">
            <p><strong>Nombre:</strong> <?= $pokemon['nombre'] ?></p>
            <p><strong>Tipo:</strong> 
                <?= $pokemon['tipo_primario'] ?>
                <?= $pokemon['tipo_secundario'] ? "/ ".$pokemon['tipo_secundario'] : '' ?>
            </p>
            <p><strong>Altura:</strong> <?= $pokemon['altura'] ?> m</p>
            <p><strong>Peso:</strong> <?= $pokemon['peso'] ?> kg</p>
            <p><strong>Habilidades:</strong> 
                <?= implode(', ', array_column($habilidades, 'nombre')) ?>
            </p>
        </div>
    </div>
</body>
</html>