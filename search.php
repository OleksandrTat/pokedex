<?php
// Include database configuration and functions
require_once 'config.php';
require_once 'pokemon_functions.php';

// Process search request
$searchTerm = isset($_POST['term']) ? trim($_POST['term']) : '';
$response = ['success' => false, 'data' => []];

if (!empty($searchTerm)) {
    // Call the stored procedure to get the pokemon data
    $pokemonData = getPokemonById($conn, $searchTerm);
    
    if (!empty($pokemonData) && isset($pokemonData['basic']['pokedex_number'])) {
        $response = [
            'success' => true,
            'redirect' => 'index.php?id=' . $pokemonData['basic']['pokedex_number']
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Pokemon not found. Try a different name or ID.'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Please enter a Pokemon name or ID.'
    ];
}

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>