<?php
// Include database configuration and functions
require_once 'config.php';
require_once 'pokemon_functions.php';

// Get the requested pokemon id
$pokemonId = isset($_GET['id']) ? (int)$_GET['id'] : 25; // Default to Pikachu (#25)

// Get pokemon data
$pokemonData = getPokemonById($conn, $pokemonId);

// If no pokemon found, return error
if (empty($pokemonData)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Pokemon not found']);
    exit;
}

// Create export data structure
$exportData = [
    'id' => $pokemonData['basic']['pokedex_number'],
    'name' => $pokemonData['basic']['name'],
    'types' => [
        'primary' => $pokemonData['basic']['primary_type']
    ],
    'description' => $pokemonData['basic']['description'],
    'category' => $pokemonData['basic']['category'],
    'image_url' => $pokemonData['basic']['image_url'],
    'stats' => $pokemonData['stats'] ?? [],
    'physical' => [
        'height' => $pokemonData['physical']['height'] / 10, // Convert to meters
        'weight' => $pokemonData['physical']['weight'] / 10  // Convert to kg
    ],
    'abilities' => $pokemonData['abilities'] ?? [],
    'moves' => $pokemonData['moves'] ?? [],
    'exported_on' => date('Y-m-d H:i:s')
];

// Add secondary type if it exists
if (!empty($pokemonData['basic']['secondary_type'])) {
    $exportData['types']['secondary'] = $pokemonData['basic']['secondary_type'];
}

// Close the database connection
$conn->close();

// Set the appropriate headers for file download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="pokemon_' . $pokemonId . '.json"');

// Output the JSON data
echo json_encode($exportData, JSON_PRETTY_PRINT);
?>