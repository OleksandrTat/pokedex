<?php
// get_pokemon.php - API для отримання даних про покемонів

// Підключення до бази даних
require_once 'php/db_connect.php';

// Ініціалізація відповіді
$response = ['success' => false, 'data' => null, 'message' => ''];

// Отримання параметрів
$action = isset($_GET['action']) ? $_GET['action'] : '';
$pokedex_number = isset($_GET['number']) ? (int)$_GET['number'] : 0;
$direction = isset($_GET['direction']) ? $_GET['direction'] : '';

try {
    switch ($action) {
        case 'get':
            // Отримання інформації про конкретного покемона
            if ($pokedex_number > 0) {
                $pokemon = getPokemonByNumber($conn, $pokedex_number);
                if ($pokemon) {
                    $response['success'] = true;
                    $response['data'] = $pokemon;
                } else {
                    $response['message'] = "Покемон #$pokedex_number не знайдений";
                }
            } else {
                $response['message'] = "Необхідно вказати номер покемона";
            }
            break;
            
        case 'navigate':
            // Навігація вгору/вниз між покемонами
            if ($pokedex_number > 0 && !empty($direction)) {
                $next_pokemon = getNextPokemon($conn, $pokedex_number, $direction);
                if ($next_pokemon) {
                    $response['success'] = true;
                    $response['data'] = $next_pokemon;
                } else {
                    $response['message'] = "Не вдалося знайти наступного покемона";
                }
            } else {
                $response['message'] = "Необхідно вказати номер покемона та напрямок";
            }
            break;
            
        default:
            $response['message'] = "Неправильна дія";
    }
} catch (Exception $e) {
    $response['message'] = "Помилка: " . $e->getMessage();
}

// Повернення результату у форматі JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Отримати дані про покемона за номером Pokedex
 */
function getPokemonByNumber($conn, $pokedex_number) {
    // Основна інформація про покемона
    $sql = "SELECT 
                p.id, p.pokedex_number, p.name, p.description, p.height, p.weight, p.category,
                p.is_legendary, p.is_mythical, p.generation, p.image_url,
                t1.name AS primary_type, t2.name AS secondary_type
            FROM pokemon p
            JOIN types t1 ON p.primary_type_id = t1.id
            LEFT JOIN types t2 ON p.secondary_type_id = t2.id
            WHERE p.pokedex_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pokedex_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $pokemon = $result->fetch_assoc();
    $pokemon_id = $pokemon['id'];
    
    // Додаємо статистику
    $sql_stats = "SELECT hp, attack, defense, special_attack, special_defense, speed, total_points 
                  FROM pokemon_stats WHERE pokemon_id = ?";
    $stmt_stats = $conn->prepare($sql_stats);
    $stmt_stats->bind_param("i", $pokemon_id);
    $stmt_stats->execute();
    $result_stats = $stmt_stats->get_result();
    
    if ($result_stats->num_rows > 0) {
        $pokemon['stats'] = $result_stats->fetch_assoc();
    } else {
        $pokemon['stats'] = null;
    }
    
    // Додаємо здібності
    $sql_abilities = "SELECT 
                        a.name AS ability_name, 
                        a.description AS ability_description,
                        pa.is_hidden
                      FROM abilities a
                      JOIN pokemon_abilities pa ON a.id = pa.ability_id
                      WHERE pa.pokemon_id = ?";
    $stmt_abilities = $conn->prepare($sql_abilities);
    $stmt_abilities->bind_param("i", $pokemon_id);
    $stmt_abilities->execute();
    $result_abilities = $stmt_abilities->get_result();
    
    $pokemon['abilities'] = [];
    while ($ability = $result_abilities->fetch_assoc()) {
        $pokemon['abilities'][] = $ability;
    }
    
    // Додаємо інформацію про еволюцію
    $pokemon['evolutions'] = getPokemonEvolutions($conn, $pokemon_id);
    
    // Додаємо топ-5 найсильніших ходів
    $sql_moves = "SELECT 
                    m.name AS move_name,
                    t.name AS move_type,
                    m.power,
                    m.accuracy,
                    m.pp,
                    m.damage_class,
                    m.description,
                    lm.name AS learn_method,
                    pm.level_learned
                FROM moves m
                JOIN pokemon_moves pm ON m.id = pm.move_id
                JOIN types t ON m.type_id = t.id
                JOIN learn_methods lm ON pm.learn_method_id = lm.id
                WHERE pm.pokemon_id = ? AND m.power IS NOT NULL
                ORDER BY m.power DESC, m.accuracy DESC
                LIMIT 5";
    $stmt_moves = $conn->prepare($sql_moves);
    $stmt_moves->bind_param("i", $pokemon_id);
    $stmt_moves->execute();
    $result_moves = $stmt_moves->get_result();
    
    $pokemon['moves'] = [];
    while ($move = $result_moves->fetch_assoc()) {
        $pokemon['moves'][] = $move;
    }
    
    return $pokemon;
}

/**
 * Отримати інформацію про еволюції покемона
 */
function getPokemonEvolutions($conn, $pokemon_id) {
    // Знаходимо ланцюжок еволюції
    $sql_chain = "SELECT DISTINCT ec.id 
                  FROM evolution_chains ec
                  JOIN evolutions e ON ec.id = e.evolution_chain_id
                  WHERE e.base_pokemon_id = ? OR e.evolved_pokemon_id = ?";
    $stmt_chain = $conn->prepare($sql_chain);
    $stmt_chain->bind_param("ii", $pokemon_id, $pokemon_id);
    $stmt_chain->execute();
    $result_chain = $stmt_chain->get_result();
    
    if ($result_chain->num_rows === 0) {
        return [];
    }
    
    $row = $result_chain->fetch_assoc();
    $chain_id = $row['id'];
    
    // Отримуємо всі еволюції в ланцюжку
    $sql_evolutions = "SELECT 
                        e.id, e.base_pokemon_id, e.evolved_pokemon_id,
                        e.level_required, e.item_required, e.evolution_method, e.evolution_condition,
                        base.pokedex_number AS base_pokedex_number, base.name AS base_name,
                        evolved.pokedex_number AS evolved_pokedex_number, evolved.name AS evolved_name,
                        e.order_in_chain
                      FROM evolutions e
                      JOIN pokemon base ON e.base_pokemon_id = base.id
                      JOIN pokemon evolved ON e.evolved_pokemon_id = evolved.id
                      WHERE e.evolution_chain_id = ?
                      ORDER BY e.order_in_chain ASC";
    
    $stmt_evolutions = $conn->prepare($sql_evolutions);
    $stmt_evolutions->bind_param("i", $chain_id);
    $stmt_evolutions->execute();
    $result_evolutions = $stmt_evolutions->get_result();
    
    $evolutions = [];
    while ($evolution = $result_evolutions->fetch_assoc()) {
        $evolutions[] = $evolution;
    }
    
    return $evolutions;
}

/**
 * Отримати наступного покемона (вгору/вниз) за номером Pokedex
 */
function getNextPokemon($conn, $current_pokedex_number, $direction) {
    // Визначаємо напрямок навігації
    $operator = $direction === 'up' ? '>' : '<';
    $order = $direction === 'up' ? 'ASC' : 'DESC';
    
    // Знаходимо наступного покемона
    $sql = "SELECT pokedex_number FROM pokemon 
            WHERE pokedex_number $operator ? 
            ORDER BY pokedex_number $order LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $current_pokedex_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Якщо не знайдено, робимо циклічний перехід
        $extreme = $direction === 'up' ? 'MIN' : 'MAX';
        $sql = "SELECT $extreme(pokedex_number) AS pokedex_number FROM pokemon";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $next_pokedex_number = $row['pokedex_number'];
    } else {
        $row = $result->fetch_assoc();
        $next_pokedex_number = $row['pokedex_number'];
    }
    
    // Отримуємо повну інформацію про наступного покемона
    return getPokemonByNumber($conn, $next_pokedex_number);
}