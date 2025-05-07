<?php
// ajax_handlers.php - Manejadores para las solicitudes AJAX

require_once 'api_functions.php';

// Comprobar que tipo de solicitud se está realizando
if (!isset($_POST['action'])) {
    echo json_encode(['error' => 'No se especificó ninguna acción']);
    exit;
}

// Manejar la acción correspondiente
switch ($_POST['action']) {
    case 'searchById':
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(['error' => 'No se proporcionó un ID válido']);
            exit;
        }
        
        $pokemon = findPokemonInDatabase($_POST['id']);
        echo json_encode($pokemon);
        break;
        
    case 'searchByName':
        if (!isset($_POST['name']) || empty($_POST['name'])) {
            echo json_encode(['error' => 'No se proporcionó un nombre válido']);
            exit;
        }
        
        $pokemon = findPokemonInDatabase($_POST['name']);
        echo json_encode($pokemon);
        break;
        
    case 'random':
        $pokemon = getRandomPokemon();
        echo json_encode($pokemon);
        break;
        
    case 'compare':
        if (!isset($_POST['id1']) || empty($_POST['id1']) || !isset($_POST['id2']) || empty($_POST['id2'])) {
            echo json_encode(['error' => 'No se proporcionaron dos IDs válidos para comparar']);
            exit;
        }
        
        $comparison = comparePokemon($_POST['id1'], $_POST['id2']);
        echo json_encode($comparison);
        break;
        
    case 'like':
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(['error' => 'No se proporcionó un ID válido']);
            exit;
        }
        
        $result = addPokemonToFavorites($_POST['id']);
        echo json_encode(['success' => $result]);
        break;
        
    case 'export':
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(['error' => 'No se proporcionó un ID válido']);
            exit;
        }
        
        $json = exportPokemonToJson($_POST['id']);
        echo $json;
        break;
        
    default:
        echo json_encode(['error' => 'Acción no reconocida']);
        break;
}