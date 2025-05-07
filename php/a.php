<?php
require_once 'config.php';

// Función para obtener datos de la PokeAPI
function fetchFromPokeAPI($endpoint) {
    $url = "https://pokeapi.co/api/v2/" . $endpoint;
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }
    
    curl_close($ch);
    
    return json_decode($response, true);
}

// Función para obtener y guardar un Pokémon por ID o nombre
function getPokemonAndSave($identifier) {
    // Convertir a minúsculas si es un nombre
    if (!is_numeric($identifier)) {
        $identifier = strtolower($identifier);
    }
    
    // Obtener datos básicos del Pokémon
    $pokemonData = fetchFromPokeAPI("pokemon/{$identifier}");
    
    if (isset($pokemonData['error']) || !isset($pokemonData['id'])) {
        return ['error' => 'Pokémon no encontrado'];
    }
    
    // Obtener especie y descripción
    $speciesData = fetchFromPokeAPI("pokemon-species/{$pokemonData['id']}");
    
    // Extraer la descripción en español o inglés
    $description = '';
    if (isset($speciesData['flavor_text_entries'])) {
        foreach ($speciesData['flavor_text_entries'] as $entry) {
            if ($entry['language']['name'] === 'es') {
                $description = $entry['flavor_text'];
                break;
            } elseif ($entry['language']['name'] === 'en' && empty($description)) {
                $description = $entry['flavor_text'];
            }
        }
    }
    
    // Limpiar descripción (eliminar saltos de línea extraños)
    $description = str_replace(["\n", "\f"], " ", $description);
    
    // Obtener datos de evolución
    $evolutionData = [];
    if (isset($speciesData['evolution_chain']['url'])) {
        $evolutionChainId = basename(rtrim($speciesData['evolution_chain']['url'], '/'));
        $evolutionChain = fetchFromPokeAPI("evolution-chain/{$evolutionChainId}");
        $evolutionData = processEvolutionChain($evolutionChain);
    }
    
    // Preparar datos para guardar en la base de datos
    $pokemon = [
        'id' => $pokemonData['id'],
        'name' => ucfirst($pokemonData['name']),
        'height' => $pokemonData['height'] / 10, // Convertir a metros
        'weight' => $pokemonData['weight'] / 10, // Convertir a kg
        'species' => isset($speciesData['genera']) ? getGenusInLanguage($speciesData['genera']) : '',
        'description' => $description,
        'image_url' => $pokemonData['sprites']['other']['official-artwork']['front_default'] ?? $pokemonData['sprites']['front_default'],
        'types' => array_map(function($type) {
            return ucfirst($type['type']['name']);
        }, $pokemonData['types']),
        'stats' => [
            'hp' => findStatValue($pokemonData['stats'], 'hp'),
            'attack' => findStatValue($pokemonData['stats'], 'attack'),
            'defense' => findStatValue($pokemonData['stats'], 'defense'),
            'special_attack' => findStatValue($pokemonData['stats'], 'special-attack'),
            'special_defense' => findStatValue($pokemonData['stats'], 'special-defense'),
            'speed' => findStatValue($pokemonData['stats'], 'speed')
        ],
        'evolution_chain' => $evolutionData
    ];
    
    // Guardar en la base de datos
    savePokemonToDatabase($pokemon);
    
    return $pokemon;
}

// Función auxiliar para encontrar el valor de una estadística
function findStatValue($stats, $statName) {
    foreach ($stats as $stat) {
        if ($stat['stat']['name'] === $statName) {
            return $stat['base_stat'];
        }
    }
    return 0;
}

// Función para obtener el género en español o inglés
function getGenusInLanguage($genera) {
    foreach ($genera as $genus) {
        if ($genus['language']['name'] === 'es') {
            return $genus['genus'];
        } elseif ($genus['language']['name'] === 'en') {
            $englishGenus = $genus['genus'];
        }
    }
    return $englishGenus ?? '';
}

// Función para procesar la cadena de evolución
function processEvolutionChain($evolutionChain) {
    $evolutions = [];
    
    if (!isset($evolutionChain['chain'])) {
        return $evolutions;
    }
    
    $chain = $evolutionChain['chain'];
    $processChain = function($chain, $evolvesFrom = null) use (&$processChain, &$evolutions) {
        $pokemon = [
            'name' => $chain['species']['name'],
            'id' => basename(rtrim($chain['species']['url'], '/')),
        ];
        
        // Si hay datos de evolución
        if ($evolvesFrom !== null) {
            $evolutionDetails = $chain['evolution_details'][0] ?? [];
            $pokemon['evolves_from'] = $evolvesFrom;
            
            // Determinar el método de evolución
            if (isset($evolutionDetails['trigger'])) {
                $trigger = $evolutionDetails['trigger']['name'];
                
                if ($trigger === 'level-up') {
                    if (isset($evolutionDetails['min_level'])) {
                        $pokemon['evolution_method'] = "level {$evolutionDetails['min_level']}";
                    } elseif (isset($evolutionDetails['min_happiness'])) {
                        $pokemon['evolution_method'] = "friendship";
                    } else {
                        $pokemon['evolution_method'] = "level up";
                    }
                } elseif ($trigger === 'use-item' && isset($evolutionDetails['item'])) {
                    $pokemon['evolution_method'] = strtolower($evolutionDetails['item']['name']);
                } elseif ($trigger === 'trade') {
                    $pokemon['evolution_method'] = "trade";
                } else {
                    $pokemon['evolution_method'] = $trigger;
                }
            }
        }
        
        $evolutions[] = $pokemon;
        
        // Procesar evoluciones
        foreach ($chain['evolves_to'] as $evolution) {
            $processChain($evolution, $pokemon['id']);
        }
    };
    
    $processChain($chain);
    
    return $evolutions;
}

// Función para guardar los datos del Pokémon en la base de datos
function savePokemonToDatabase($pokemon) {
    $conn = connectDB();
    
    // Insertar datos básicos del Pokémon
    $stmt = $conn->prepare("INSERT INTO pokemon (id, name, height, weight, species, description, image_url) 
                         VALUES (?, ?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE 
                         name = VALUES(name),
                         height = VALUES(height),
                         weight = VALUES(weight),
                         species = VALUES(species),
                         description = VALUES(description),
                         image_url = VALUES(image_url)");
    
    $stmt->bind_param("isddssss", 
        $pokemon['id'], 
        $pokemon['name'], 
        $pokemon['height'], 
        $pokemon['weight'], 
        $pokemon['species'],
        $pokemon['description'],
        $pokemon['image_url']
    );
    
    $stmt->execute();
    $stmt->close();
    
    // Eliminar tipos antiguos
    $stmt = $conn->prepare("DELETE FROM pokemon_types WHERE pokemon_id = ?");
    $stmt->bind_param("i", $pokemon['id']);
    $stmt->execute();
    $stmt->close();
    
    // Insertar nuevos tipos
    $stmt = $conn->prepare("INSERT INTO pokemon_types (pokemon_id, type_name) VALUES (?, ?)");
    
    foreach ($pokemon['types'] as $type) {
        $stmt->bind_param("is", $pokemon['id'], $type);
        $stmt->execute();
    }
    
    $stmt->close();
    
    // Insertar o actualizar estadísticas
    $stmt = $conn->prepare("INSERT INTO pokemon_stats 
                         (pokemon_id, hp, attack, defense, special_attack, special_defense, speed) 
                         VALUES (?, ?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE 
                         hp = VALUES(hp),
                         attack = VALUES(attack),
                         defense = VALUES(defense),
                         special_attack = VALUES(special_attack),
                         special_defense = VALUES(special_defense),
                         speed = VALUES(speed)");
    
    $stmt->bind_param("iiiiiii", 
        $pokemon['id'],
        $pokemon['stats']['hp'],
        $pokemon['stats']['attack'],
        $pokemon['stats']['defense'],
        $pokemon['stats']['special_attack'],
        $pokemon['stats']['special_defense'],
        $pokemon['stats']['speed']
    );
    
    $stmt->execute();
    $stmt->close();
    
    // Procesar evoluciones
    if (!empty($pokemon['evolution_chain'])) {
        // Eliminar evoluciones antiguas
        $stmt = $conn->prepare("DELETE FROM pokemon_evolution WHERE pokemon_id = ?");
        $stmt->bind_param("i", $pokemon['id']);
        $stmt->execute();
        $stmt->close();
        
        // Insertar nuevas evoluciones
        $stmt = $conn->prepare("INSERT INTO pokemon (id, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
        
        foreach ($pokemon['evolution_chain'] as $evolution) {
            $stmt->bind_param("is", $evolution['id'], $evolution['name']);
            $stmt->execute();
        }
        
        $stmt->close();
        
        // Insertar relaciones de evolución
        $stmt = $conn->prepare("INSERT INTO pokemon_evolution 
                             (pokemon_id, evolves_from, evolves_to, evolution_method) 
                             VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE
                             evolves_from = VALUES(evolves_from),
                             evolves_to = VALUES(evolves_to),
                             evolution_method = VALUES(evolution_method)");
        
        for ($i = 0; $i < count($pokemon['evolution_chain']); $i++) {
            $current = $pokemon['evolution_chain'][$i];
            $next = $pokemon['evolution_chain'][$i+1] ?? null;
            
            $currentId = $current['id'];
            $evolvesFrom = $current['evolves_from'] ?? null;
            $evolvesTo = $next ? $next['id'] : null;
            $evolutionMethod = $current['evolution_method'] ?? null;
            
            $stmt->bind_param("iiis", $currentId, $evolvesFrom, $evolvesTo, $evolutionMethod);
            $stmt->execute();
        }
        
        $stmt->close();
    }
    
    closeDB($conn);
}

// Función para obtener un Pokémon aleatorio
function getRandomPokemon() {
    // La PokeAPI tiene más de 1000 Pokémon, pero usaremos un límite más bajo
    $maxId = 898; // Hasta la generación 8
    $randomId = rand(1, $maxId);
    
    return getPokemonAndSave($randomId);
}

// Función para buscar un Pokémon en la base de datos
function findPokemonInDatabase($identifier) {
    $conn = connectDB();
    $pokemon = null;
    
    // Determinar si el identificador es un ID o un nombre
    if (is_numeric($identifier)) {
        $sql = "SELECT * FROM pokemon WHERE id = ?";
        $types = "i";
    } else {
        $sql = "SELECT * FROM pokemon WHERE name LIKE ?";
        $identifier = "%{$identifier}%";
        $types = "s";
    }
    
    // Ejecutar consulta
    $result = preparedQuery($sql, $types, [$identifier], $conn);
    
    if ($result && $result->num_rows > 0) {
        $pokemon = $result->fetch_assoc();
        
        // Obtener tipos
        $typeResult = preparedQuery(
            "SELECT type_name FROM pokemon_types WHERE pokemon_id = ?", 
            "i", 
            [$pokemon['id']], 
            $conn
        );
        
        $pokemon['types'] = [];
        while ($type = $typeResult->fetch_assoc()) {
            $pokemon['types'][] = $type['type_name'];
        }
        
        // Obtener estadísticas
        $statsResult = preparedQuery(
            "SELECT * FROM pokemon_stats WHERE pokemon_id = ?", 
            "i", 
            [$pokemon['id']], 
            $conn
        );
        
        if ($stats = $statsResult->fetch_assoc()) {
            $pokemon['stats'] = $stats;
        }
        
        // Obtener evoluciones
        $evolutionResult = preparedQuery(
            "SELECT e.*, p1.name AS from_name, p2.name AS to_name, p3.name AS current_name
             FROM pokemon_evolution e
             LEFT JOIN pokemon p1 ON e.evolves_from = p1.id
             LEFT JOIN pokemon p2 ON e.evolves_to = p2.id
             LEFT JOIN pokemon p3 ON e.pokemon_id = p3.id
             WHERE e.pokemon_id = ? OR e.evolves_from = ? OR e.evolves_to = ?", 
            "iii", 
            [$pokemon['id'], $pokemon['id'], $pokemon['id']], 
            $conn
        );
        
        $pokemon['evolutions'] = [];
        while ($evolution = $evolutionResult->fetch_assoc()) {
            $pokemon['evolutions'][] = $evolution;
        }
    }
    
    closeDB($conn);
    
    // Si no encontramos en la base de datos, intentamos obtenerlo de la API
    if (!$pokemon) {
        return getPokemonAndSave($identifier);
    }
    
    return $pokemon;
}

// Función para añadir un Pokémon a favoritos
function addPokemonToFavorites($pokemonId) {
    $conn = connectDB();
    
    $stmt = $conn->prepare("INSERT INTO pokemon_favorites (pokemon_id) VALUES (?) ON DUPLICATE KEY UPDATE date_added = CURRENT_TIMESTAMP");
    $stmt->bind_param("i", $pokemonId);
    $result = $stmt->execute();
    $stmt->close();
    
    closeDB($conn);
    
    return $result;
}

// Función para comparar dos Pokémon
function comparePokemon($pokemonId1, $pokemonId2) {
    $pokemon1 = findPokemonInDatabase($pokemonId1);
    $pokemon2 = findPokemonInDatabase($pokemonId2);
    
    if (!$pokemon1 || !$pokemon2) {
        return ['error' => 'Uno o ambos Pokémon no encontrados'];
    }
    
    // Guardar la comparación en la base de datos
    $conn = connectDB();
    
    $stmt = $conn->prepare("INSERT INTO pokemon_comparisons (pokemon_id_1, pokemon_id_2) VALUES (?, ?)");
    $stmt->bind_param("ii", $pokemon1['id'], $pokemon2['id']);
    $stmt->execute();
    $stmt->close();
    
    closeDB($conn);
    
    return [
        'pokemon1' => $pokemon1,
        'pokemon2' => $pokemon2
    ];
}

// Función para exportar datos de un Pokémon a JSON
function exportPokemonToJson($pokemonId) {
    $pokemon = findPokemonInDatabase($pokemonId);
    
    if (!$pokemon) {
        return ['error' => 'Pokémon no encontrado'];
    }
    
    return json_encode($pokemon, JSON_PRETTY_PRINT);
}