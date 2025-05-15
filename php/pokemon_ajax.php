<?php
// Include database configuration and functions
require_once 'config.php';
require_once 'pokemon_functions.php';

// Get the requested action and current pokemon id
$action = isset($_GET['action']) ? $_GET['action'] : '';
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 25; // Default to Pikachu (#25)

$response = [];

switch($action) {
    case 'next':
        // Get the next pokemon id
        $nextId = getAdjacentPokemonId($conn, $currentId, 'next');
        $response = ['redirect' => 'index.php?id=' . $nextId];
        break;
        
    case 'prev':
        // Get the previous pokemon id
        $prevId = getAdjacentPokemonId($conn, $currentId, 'prev');
        $response = ['redirect' => 'index.php?id=' . $prevId];
        break;
        
    case 'random':
        // Get total count and generate random id
        $totalCount = getTotalPokemonCount($conn);
        $randomOffset = rand(0, $totalCount - 1);
        
        // Get the pokemon id at this random position
        $sql = "SELECT pokedex_number FROM pokemon ORDER BY pokedex_number ASC LIMIT 1 OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $randomOffset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response = ['redirect' => 'index.php?id=' . $row['pokedex_number']];
        } else {
            $response = ['error' => 'No pokemon found'];
        }
        break;
        
    default:
        $response = ['error' => 'Invalid action'];
}

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>