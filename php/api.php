<?php
// api.php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'pokemon';
$user = 'ваш_юзер';
$pass = 'ваш_пароль';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// отримуємо pokedex_number з GET
$num = isset($_GET['num']) ? (int)$_GET['num'] : 1;
if ($num < 1) $num = 1;

// вибірка основних даних
$stmt = $pdo->prepare("
    SELECT 
      p.pokedex_number,
      p.name,
      t1.name AS primary_type,
      t2.name AS secondary_type,
      p.description,
      p.image_url,
      p.height,
      p.weight,
      ps.hp, ps.attack, ps.defense, ps.special_attack, ps.special_defense, ps.speed
    FROM pokemon p
    JOIN types t1 ON p.primary_type_id = t1.id
    LEFT JOIN types t2 ON p.secondary_type_id = t2.id
    JOIN pokemon_stats ps ON ps.pokemon_id = p.id
    WHERE p.pokedex_number = ?
");
$stmt->execute([$num]);
$data = $stmt->fetch();

if (!$data) {
    http_response_code(404);
    echo json_encode(['error' => 'Pokémon not found']);
    exit;
}

echo json_encode($data);
