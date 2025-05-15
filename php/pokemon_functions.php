<?php
// Include database configuration
require_once 'config.php';

/**
 * Get a pokemon by ID
 * 
 * @param int $id The pokedex number
 * @return array|null The pokemon data or null if not found
 */
function getPokemonById($conn, $id) {
    // Call the stored procedure
    $stmt = $conn->prepare("CALL GetFullPokemonData(?)");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    // Get results from multiple result sets
    $result = [];
    
    // First result set: Basic pokemon information
    $basicInfo = $stmt->get_result();
    if ($basicInfo->num_rows > 0) {
        $result['basic'] = $basicInfo->fetch_assoc();
    }
    
    // Check if there are more result sets
    if ($stmt->more_results()) {
        $stmt->next_result();
        // Second result set: Stats
        $statsInfo = $stmt->get_result();
        if ($statsInfo->num_rows > 0) {
            $result['stats'] = $statsInfo->fetch_assoc();
        }
    }
    
    // Physical characteristics
    if ($stmt->more_results()) {
        $stmt->next_result();
        $physicalInfo = $stmt->get_result();
        if ($physicalInfo->num_rows > 0) {
            $result['physical'] = $physicalInfo->fetch_assoc();
        }
    }
    
    // Abilities
    if ($stmt->more_results()) {
        $stmt->next_result();
        $abilitiesInfo = $stmt->get_result();
        $result['abilities'] = [];
        while ($ability = $abilitiesInfo->fetch_assoc()) {
            $result['abilities'][] = $ability;
        }
    }
    
    // Moves
    if ($stmt->more_results()) {
        $stmt->next_result();
        $movesInfo = $stmt->get_result();
        $result['moves'] = [];
        while ($move = $movesInfo->fetch_assoc()) {
            $result['moves'][] = $move;
        }
    }
    
    // Evolution chain
    if ($stmt->more_results()) {
        $stmt->next_result();
        $evolutionInfo = $stmt->get_result();
        if ($evolutionInfo->num_rows > 0) {
            $result['evolution'] = $evolutionInfo->fetch_assoc();
        }
    }
    
    $stmt->close();
    
    return empty($result) ? null : $result;
}

/**
 * Get the next or previous pokemon ID
 * 
 * @param int $currentId The current pokedex number
 * @param string $direction 'next' or 'prev'
 * @return int The next or previous pokedex number
 */
function getAdjacentPokemonId($conn, $currentId, $direction) {
    $sql = "";
    
    if ($direction === 'next') {
        $sql = "SELECT pokedex_number FROM pokemon WHERE pokedex_number > ? ORDER BY pokedex_number ASC LIMIT 1";
    } else {
        $sql = "SELECT pokedex_number FROM pokemon WHERE pokedex_number < ? ORDER BY pokedex_number DESC LIMIT 1";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $currentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['pokedex_number'];
    } else {
        // If no next/prev pokemon found, get the first/last one
        if ($direction === 'next') {
            $sql = "SELECT MIN(pokedex_number) as pokedex_number FROM pokemon";
        } else {
            $sql = "SELECT MAX(pokedex_number) as pokedex_number FROM pokemon";
        }
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['pokedex_number'];
    }
}

/**
 * Get total count of pokemon in the database
 */
function getTotalPokemonCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM pokemon");
    $row = $result->fetch_assoc();
    return $row['total'];
}
?>