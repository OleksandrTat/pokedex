<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pokemon";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * Get the evolution path for a Pokémon by its Pokédex number
 * 
 * @param int $pokedex_number The Pokédex number of the Pokémon
 * @param mysqli $conn Database connection
 * @return array|null The evolution chain or null if not found
 */
function getEvolutionPath($pokedex_number, $conn) {
    // First, get the Pokémon ID and basic information
    $sql = "SELECT p.id, p.pokedex_number, p.name 
            FROM pokemon p 
            WHERE p.pokedex_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pokedex_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null; // Pokémon not found
    }
    
    $pokemon = $result->fetch_assoc();
    $pokemon_id = $pokemon['id'];
    
    // Find the evolution chain ID this Pokémon belongs to
    $sql = "SELECT DISTINCT ec.id 
            FROM evolution_chains ec
            JOIN evolutions e ON ec.id = e.evolution_chain_id
            WHERE e.base_pokemon_id = ? OR e.evolved_pokemon_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $pokemon_id, $pokemon_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // No evolution information found, this Pokémon might not evolve
        return [
            'pokemon' => $pokemon,
            'evolutions' => []
        ];
    }
    
    $chain_id = $result->fetch_assoc()['id'];
    
    // Get all evolutions in this chain, ordered by their position
    $sql = "SELECT 
                e.id, 
                e.order_in_chain,
                e.evolution_method,
                e.level_required,
                e.item_required,
                e.evolution_condition,
                base.id as base_id,
                base.pokedex_number as base_pokedex_number,
                base.name as base_name,
                evolved.id as evolved_id,
                evolved.pokedex_number as evolved_pokedex_number,
                evolved.name as evolved_name
            FROM evolutions e
            JOIN pokemon base ON e.base_pokemon_id = base.id
            JOIN pokemon evolved ON e.evolved_pokemon_id = evolved.id
            WHERE e.evolution_chain_id = ?
            ORDER BY e.order_in_chain";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $chain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $evolutions = [];
    while ($row = $result->fetch_assoc()) {
        $evolutions[] = $row;
    }
    
    // Determine the starting Pokémon in the chain
    // This is usually the one that doesn't appear as an evolved form in any row
    $starting_pokemon = null;
    $all_evolved_ids = array_column($evolutions, 'evolved_id');
    
    foreach ($evolutions as $evolution) {
        if (!in_array($evolution['base_id'], $all_evolved_ids)) {
            $starting_pokemon = [
                'id' => $evolution['base_id'],
                'pokedex_number' => $evolution['base_pokedex_number'],
                'name' => $evolution['base_name']
            ];
            break;
        }
    }
    
    if ($starting_pokemon === null && count($evolutions) > 0) {
        // Fallback: use the first base Pokémon in the chain
        $starting_pokemon = [
            'id' => $evolutions[0]['base_id'],
            'pokedex_number' => $evolutions[0]['base_pokedex_number'],
            'name' => $evolutions[0]['base_name']
        ];
    }
    
    return [
        'starting_pokemon' => $starting_pokemon,
        'evolutions' => $evolutions
    ];
}

/**
 * Format the evolution method into a readable description
 * 
 * @param array $evolution The evolution data
 * @return string Formatted description of evolution method
 */
function formatEvolutionMethod($evolution) {
    $method = $evolution['evolution_method'];
    $description = "";
    
    switch (strtolower($method)) {
        case 'level':
            $description = "at level " . $evolution['level_required'];
            break;
        case 'item':
            $description = "using a " . strtoupper($evolution['item_required']) . " via item method";
            break;
        case 'trade':
            $description = "when traded";
            if (!empty($evolution['evolution_condition'])) {
                $description .= " " . $evolution['evolution_condition'];
            }
            break;
        case 'friendship':
            $description = "via friendship method";
            if (!empty($evolution['evolution_condition'])) {
                $description .= " when " . $evolution['evolution_condition'];
            }
            break;
        default:
            if (!empty($evolution['evolution_condition'])) {
                $description = "when " . $evolution['evolution_condition'];
            } else {
                $description = "via " . $method . " method";
            }
    }
    
    return $description;
}

/**
 * Display the evolution path for a given Pokémon
 * 
 * @param int $pokedex_number The Pokédex number of the Pokémon
 * @param mysqli $conn Database connection
 * @return string HTML output of the evolution path
 */
function displayEvolutionPath($pokedex_number, $conn) {
    $evolution_data = getEvolutionPath($pokedex_number, $conn);
    
    if ($evolution_data === null) {
        return "<p>No Pokémon found with Pokédex number: {$pokedex_number}</p>";
    }
    
    if (empty($evolution_data['evolutions'])) {
        return "<p>Pokémon #{$pokedex_number} does not evolve.</p>";
    }
    
    $starting_pokemon = $evolution_data['starting_pokemon'];
    $evolutions = $evolution_data['evolutions'];
    
    $output = "<h2>Starting Pokémon: {$starting_pokemon['name']} (ID={$starting_pokemon['pokedex_number']})</h2>";
    $output .= "<div class='evolution-path'>";
    
    $step = 1;
    foreach ($evolutions as $evolution) {
        $output .= "<div class='evolution-step'>";
        $output .= "<p>[step {$step}] {$evolution['base_name']} (ID={$evolution['base_pokedex_number']}) ";
        $output .= "evolves into {$evolution['evolved_name']} (ID={$evolution['evolved_pokedex_number']}) ";
        $output .= formatEvolutionMethod($evolution);
        $output .= "</p>";
        $output .= "</div>";
        $step++;
    }
    
    $output .= "</div>";
    return $output;
}

// Handle form submission
$evolution_output = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pokedex_number'])) {
    $pokedex_number = intval($_POST['pokedex_number']);
    $evolution_output = displayEvolutionPath($pokedex_number, $conn);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon Evolution Path</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #e91e63;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        form {
            margin-bottom: 20px;
        }
        input[type="number"] {
            padding: 8px;
            width: 100px;
        }
        button {
            padding: 8px 16px;
            background-color: #e91e63;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #c2185b;
        }
        .evolution-path {
            margin-top: 20px;
        }
        .evolution-step {
            background-color: #f1f8e9;
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #8bc34a;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pokémon Evolution Path Finder</h1>
        
        <form method="post">
            <label for="pokedex_number">Enter Pokédex Number:</label>
            <input type="number" id="pokedex_number" name="pokedex_number" min="1" required>
            <button type="submit">Show Evolution Path</button>
        </form>
        
        <?php echo $evolution_output; ?>
        
        
    </div>
</body>
</html>