<?php
// pokemon_info.php
// Display Pokemon information from MySQL database using PDO

// Database configuration
$host = '127.0.0.1';
$db   = 'pokemon';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

// Get identifier from query parameter (pokedex_number or name)
$identifier = isset($_GET['id']) ? $_GET['id'] : null;
if (!$identifier) {
    exit('Please provide a Pokemon id or name via ?id=');
}

// Prepare SQL to fetch from view
$sql = "SELECT * FROM pokemon_details_view WHERE pokedex_number = :id OR name = :name LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $identifier, 'name' => $identifier]);
$pokemon = $stmt->fetch();

if (!$pokemon) {
    exit('Pokemon not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pokemon['name']); ?> - Pokemon Info</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: auto; }
        img { max-width: 200px; }
        .stats { display: flex; flex-wrap: wrap; gap: 1rem; }
        .stat { flex: 1 1 100px; background: #f0f0f0; padding: 8px; border-radius: 4px; text-align: center; }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($pokemon['name']); ?> <small>#<?php echo $pokemon['pokedex_number']; ?></small></h1>
    <img src="<?php echo htmlspecialchars($pokemon['image_url']); ?>" alt="<?php echo htmlspecialchars($pokemon['name']); ?>">
    <p><strong>Type:</strong> <?php echo htmlspecialchars($pokemon['primary_type']); ?><?php if ($pokemon['secondary_type']) echo ' / ' . htmlspecialchars($pokemon['secondary_type']); ?></p>
    <p><strong>Generation:</strong> <?php echo $pokemon['generation']; ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($pokemon['category']); ?></p>
    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($pokemon['description'])); ?></p>
    <h2>Stats</h2>
    <div class="stats">
        <?php foreach (['hp', 'attack', 'defense', 'special_attack', 'special_defense', 'speed', 'total_points'] as $stat): ?>
            <div class="stat">
                <strong><?php echo ucwords(str_replace('_', ' ', $stat)); ?></strong><br>
                <?php echo $pokemon[$stat]; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
