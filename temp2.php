<?php
// Підключення до бази даних
$servername = "localhost";
$username = "user"; // змініть на ваше ім'я користувача
$password = "10190919Ifp"; // змініть на ваш пароль
$dbname = "php_pokemon";

// Створюємо з'єднання
$conn = new mysqli($servername, $username, $password, $dbname);

// Перевіряємо з'єднання
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обробка AJAX запиту для отримання даних про покемона
if (isset($_POST['action']) && $_POST['action'] == 'getPokemon') {
    $direction = $_POST['direction'];
    $currentId = $_POST['currentId'];
    
    if ($direction == 'up') {
        // Знаходимо наступного покемона (з більшим id)
        $sql = "SELECT p.*, ps.hp, ps.attack, ps.defense, ps.special_attack, ps.special_defense, ps.speed, 
                t1.name as primary_type, t2.name as secondary_type, p.category as species
                FROM pokemon p
                LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id
                LEFT JOIN types t1 ON p.primary_type_id = t1.id
                LEFT JOIN types t2 ON p.secondary_type_id = t2.id
                WHERE p.pokedex_number > $currentId
                ORDER BY p.pokedex_number ASC LIMIT 1";
    } else {
        // Знаходимо попереднього покемона (з меншим id)
        $sql = "SELECT p.*, ps.hp, ps.attack, ps.defense, ps.special_attack, ps.special_defense, ps.speed, 
                t1.name as primary_type, t2.name as secondary_type, p.category as species
                FROM pokemon p
                LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id
                LEFT JOIN types t1 ON p.primary_type_id = t1.id
                LEFT JOIN types t2 ON p.secondary_type_id = t2.id
                WHERE p.pokedex_number < $currentId
                ORDER BY p.pokedex_number DESC LIMIT 1";
    }
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $pokemon = $result->fetch_assoc();
        
        // Отримуємо здібності покемона
        $abilitiesSql = "SELECT a.name, a.description
                         FROM abilities a
                         JOIN pokemon_abilities pa ON a.id = pa.ability_id
                         WHERE pa.pokemon_id = " . $pokemon['id'];
        
        $abilitiesResult = $conn->query($abilitiesSql);
        $abilities = [];
        
        if ($abilitiesResult->num_rows > 0) {
            while($ability = $abilitiesResult->fetch_assoc()) {
                $abilities[] = $ability;
            }
        }
        
        // Отримуємо ходи покемона
        $movesSql = "SELECT m.name, m.power as pw, m.accuracy as ac, m.pp, t.name as type, m.description
                     FROM moves m
                     JOIN pokemon_moves pm ON m.id = pm.move_id
                     JOIN types t ON m.type_id = t.id
                     WHERE pm.pokemon_id = " . $pokemon['id'] . "
                     LIMIT 2";
        
        $movesResult = $conn->query($movesSql);
        $moves = [];
        
        if ($movesResult->num_rows > 0) {
            while($move = $movesResult->fetch_assoc()) {
                $moves[] = $move;
            }
        }
        
        // Отримуємо інформацію про еволюцію
        $evoSql = "SELECT ec.id as chain_id,
                    base.pokedex_number as base_pokedex, base.name as base_name, 
                    evolved.pokedex_number as evolved_pokedex, evolved.name as evolved_name,
                    e.evolution_method, e.item_required
                   FROM evolution_chains ec
                   JOIN evolutions e ON ec.id = e.evolution_chain_id
                   JOIN pokemon base ON e.base_pokemon_id = base.id
                   JOIN pokemon evolved ON e.evolved_pokemon_id = evolved.id
                   WHERE base.id = " . $pokemon['id'] . " OR evolved.id = " . $pokemon['id'];
                   
        $evoResult = $conn->query($evoSql);
        $evolution = [];
        
        if ($evoResult->num_rows > 0) {
            while($evo = $evoResult->fetch_assoc()) {
                $evolution[] = $evo;
            }
        }
        
        // Додаємо всі дані до відповіді
        $pokemon['abilities'] = $abilities;
        $pokemon['moves'] = $moves;
        $pokemon['evolution'] = $evolution;
        
        echo json_encode($pokemon);
    } else {
        // Якщо покемона не знайдено, робимо циклічний перехід
        if ($direction == 'up') {
            // Якщо досягли кінця, повертаємось до першого покемона
            $sql = "SELECT p.*, ps.hp, ps.attack, ps.defense, ps.special_attack, ps.special_defense, ps.speed, 
                    t1.name as primary_type, t2.name as secondary_type, p.category as species
                    FROM pokemon p
                    LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id
                    LEFT JOIN types t1 ON p.primary_type_id = t1.id
                    LEFT JOIN types t2 ON p.secondary_type_id = t2.id
                    ORDER BY p.pokedex_number ASC LIMIT 1";
        } else {
            // Якщо досягли початку, переходимо до останнього покемона
            $sql = "SELECT p.*, ps.hp, ps.attack, ps.defense, ps.special_attack, ps.special_defense, ps.speed, 
                    t1.name as primary_type, t2.name as secondary_type, p.category as species
                    FROM pokemon p
                    LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id
                    LEFT JOIN types t1 ON p.primary_type_id = t1.id
                    LEFT JOIN types t2 ON p.secondary_type_id = t2.id
                    ORDER BY p.pokedex_number DESC LIMIT 1";
        }
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $pokemon = $result->fetch_assoc();
            
            // Отримуємо здібності, ходи та еволюції аналогічно як вище
            // (Код повторюється для спрощення прикладу)
            
            // Отримуємо здібності покемона
            $abilitiesSql = "SELECT a.name, a.description
                             FROM abilities a
                             JOIN pokemon_abilities pa ON a.id = pa.ability_id
                             WHERE pa.pokemon_id = " . $pokemon['id'];
            
            $abilitiesResult = $conn->query($abilitiesSql);
            $abilities = [];
            
            if ($abilitiesResult->num_rows > 0) {
                while($ability = $abilitiesResult->fetch_assoc()) {
                    $abilities[] = $ability;
                }
            }
            
            // Отримуємо ходи покемона
            $movesSql = "SELECT m.name, m.power as pw, m.accuracy as ac, m.pp, t.name as type, m.description
                         FROM moves m
                         JOIN pokemon_moves pm ON m.id = pm.move_id
                         JOIN types t ON m.type_id = t.id
                         WHERE pm.pokemon_id = " . $pokemon['id'] . "
                         LIMIT 2";
            
            $movesResult = $conn->query($movesSql);
            $moves = [];
            
            if ($movesResult->num_rows > 0) {
                while($move = $movesResult->fetch_assoc()) {
                    $moves[] = $move;
                }
            }
            
            // Отримуємо інформацію про еволюцію
            $evoSql = "SELECT ec.id as chain_id,
                        base.pokedex_number as base_pokedex, base.name as base_name, 
                        evolved.pokedex_number as evolved_pokedex, evolved.name as evolved_name,
                        e.evolution_method, e.item_required
                       FROM evolution_chains ec
                       JOIN evolutions e ON ec.id = e.evolution_chain_id
                       JOIN pokemon base ON e.base_pokemon_id = base.id
                       JOIN pokemon evolved ON e.evolved_pokemon_id = evolved.id
                       WHERE base.id = " . $pokemon['id'] . " OR evolved.id = " . $pokemon['id'];
                       
            $evoResult = $conn->query($evoSql);
            $evolution = [];
            
            if ($evoResult->num_rows > 0) {
                while($evo = $evoResult->fetch_assoc()) {
                    $evolution[] = $evo;
                }
            }
            
            // Додаємо всі дані до відповіді
            $pokemon['abilities'] = $abilities;
            $pokemon['moves'] = $moves;
            $pokemon['evolution'] = $evolution;
            
            echo json_encode($pokemon);
        } else {
            echo json_encode(['error' => 'No pokemon found']);
        }
    }
    
    $conn->close();
    exit;
}

// За замовчуванням отримуємо першого покемона (Pikachu - #25)
$defaultPokemonId = 25;
$sql = "SELECT p.*, ps.hp, ps.attack, ps.defense, ps.special_attack, ps.special_defense, ps.speed, 
        t1.name as primary_type, t2.name as secondary_type, p.category as species
        FROM pokemon p
        LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id
        LEFT JOIN types t1 ON p.primary_type_id = t1.id
        LEFT JOIN types t2 ON p.secondary_type_id = t2.id
        WHERE p.pokedex_number = $defaultPokemonId";

$result = $conn->query($sql);

$initialPokemon = [];
if ($result->num_rows > 0) {
    $initialPokemon = $result->fetch_assoc();
    
    // Отримуємо здібності покемона
    $abilitiesSql = "SELECT a.name, a.description
                     FROM abilities a
                     JOIN pokemon_abilities pa ON a.id = pa.ability_id
                     WHERE pa.pokemon_id = " . $initialPokemon['id'] . "
                     LIMIT 3";
    
    $abilitiesResult = $conn->query($abilitiesSql);
    $abilities = [];
    
    if ($abilitiesResult->num_rows > 0) {
        while($ability = $abilitiesResult->fetch_assoc()) {
            $abilities[] = $ability;
        }
    }
    
    // Отримуємо ходи покемона
    $movesSql = "SELECT m.name, m.power as pw, m.accuracy as ac, m.pp, t.name as type, m.description
                 FROM moves m
                 JOIN pokemon_moves pm ON m.id = pm.move_id
                 JOIN types t ON m.type_id = t.id
                 WHERE pm.pokemon_id = " . $initialPokemon['id'] . "
                 LIMIT 2";
    
    $movesResult = $conn->query($movesSql);
    $moves = [];
    
    if ($movesResult->num_rows > 0) {
        while($move = $movesResult->fetch_assoc()) {
            $moves[] = $move;
        }
    }
    
    // Отримуємо еволюційний ланцюг
    $evoChainSql = "SELECT ec.id 
                    FROM evolution_chains ec
                    JOIN evolutions e ON ec.id = e.evolution_chain_id
                    WHERE e.base_pokemon_id = " . $initialPokemon['id'] . " 
                    OR e.evolved_pokemon_id = " . $initialPokemon['id'] . "
                    LIMIT 1";
                    
    $evoChainResult = $conn->query($evoChainSql);
    $chainId = 0;
    
    if ($evoChainResult->num_rows > 0) {
        $chain = $evoChainResult->fetch_assoc();
        $chainId = $chain['id'];
        
        $evoSql = "SELECT e.order_in_chain, 
                   base.id as base_id, base.pokedex_number as base_number, base.name as base_name, base.image_url as base_image,
                   evolved.id as evolved_id, evolved.pokedex_number as evolved_number, evolved.name as evolved_name, evolved.image_url as evolved_image,
                   e.evolution_method, e.item_required
                   FROM evolutions e
                   JOIN pokemon base ON e.base_pokemon_id = base.id
                   JOIN pokemon evolved ON e.evolved_pokemon_id = evolved.id
                   WHERE e.evolution_chain_id = $chainId
                   ORDER BY e.order_in_chain";
        
        $evoResult = $conn->query($evoSql);
        $evolution = [];
        
        if ($evoResult->num_rows > 0) {
            while($evo = $evoResult->fetch_assoc()) {
                $evolution[] = $evo;
            }
        }
        
        $initialPokemon['evolution_chain'] = $evolution;
    }
    
    $initialPokemon['abilities'] = $abilities;
    $initialPokemon['moves'] = $moves;
} else {
    // Якщо Pikachu не знайдено, встановлюємо базові дані
    $initialPokemon = [
        'pokedex_number' => 25,
        'name' => 'Pikachu',
        'primary_type' => 'Electric',
        'secondary_type' => null,
        'description' => 'When several of these Pokémon gather, their electricity could build and cause lightning storms.',
        'image_url' => 'img/pikachu.png',
        'height' => 0.4,
        'weight' => 6.0,
        'species' => 'Mouse Pokemon',
        'hp' => 35,
        'attack' => 55,
        'defense' => 35,
        'special_attack' => 50,
        'special_defense' => 40,
        'speed' => 90,
        'abilities' => [
            ['name' => 'Static', 'description' => 'May paralyze the attacker when hit by a physical attack.'],
            ['name' => 'Lightning Rod', 'description' => 'Draws electrical moves to boost its SP. Attack stat.'],
        ],
        'moves' => [
            ['name' => 'Thunderbolt', 'type' => 'Electric', 'pw' => 90, 'ac' => 100, 'pp' => 15, 'description' => 'A strong electric blast crashes down on the target.'],
            ['name' => 'Quick Attack', 'type' => 'Normal', 'pw' => 40, 'ac' => 100, 'pp' => 30, 'description' => 'The user lunges at the target at a speed that makes it almost invisible.'],
        ],
        'evolution_chain' => [
            [
                'base_name' => 'Pichu',
                'base_number' => 172,
                'base_image' => 'img/pichu.png',
                'evolved_name' => 'Pikachu',
                'evolved_number' => 25,
                'evolved_image' => 'img/pikachu.png',
                'evolution_method' => 'friendship',
                'order_in_chain' => 1
            ],
            [
                'base_name' => 'Pikachu',
                'base_number' => 25,
                'base_image' => 'img/pikachu.png',
                'evolved_name' => 'Raichu',
                'evolved_number' => 26,
                'evolved_image' => 'img/raichu.png',
                'evolution_method' => 'item',
                'item_required' => 'THUNDER STONE',
                'order_in_chain' => 2
            ]
        ]
    ];
}

$conn->close();

// Передаємо дані про початкового покемона в JavaScript
$initialPokemonJson = json_encode($initialPokemon);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
    content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/animation.css">
  <link rel="stylesheet" href="css/screen.css">
  <title>Pokedex</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.2/vanilla-tilt.min.js"></script>
</head>

<body>
    <div class="main tilt-main">
      <!-- logo image -->
      <img src="img/logo.png" alt="POKEMON" class="logo first-level-parax">

      <!-- background -->
      <div class="bg-blanco"></div>
      <img src="img/Rectangle 1.svg" alt="" class="bg-red-svg">

      <!-- elementos negros -->
      <div class="rectangulo_negro black"></div>
      <div class="circulo_negro black"></div>

      <!-- pantalla -->
      <div class="content_canva black">
        <div class="pantalla first-level-parax">

          <!-- fondo -->
          <div class="background">
            <div class="topbg"></div>
            <div class="cornerbg"></div>
            <div class="centerbg">
              <div class="mainoutline"></div>
              <div class="centeroutline1"></div>
              <div class="centeroutline2"></div>
              <div class="lineoutline1"></div>
              <div class="lineoutline2"></div>
              <div class="lineoutline3"></div>
              <div class="lineoutline4"></div>
            </div>
          </div>


          <!-- topbar -->
          <div class="topbar">
            <div>
              <h2 id="pokemon-name">Pikachu</h2>
              <p id="pokemon-number">#25</p>
            </div>
            <div>
              <div id="type1" class="type1">Electric</div>
              <div id="type2" class="type2" style="display: none;">Electric</div>
            </div>
          </div>

            <p id="species" class="species">Species:<br>Mouse Pokemon</p>
            <h5 id="description">When several of these Pokémon gather, their electricity could build and cause lightning storms.</h5>
          </section>

          <!-- first screen -->
          <section class="screen first-screen active">
            <img id="pokemon-image" src="img/pikachu.png" alt="Pikachu">

            <p id="species-first" class="species">Species:<br>Mouse Pokemon</p>
            <h5 id="description-first">When several of these Pokémon gather, their electricity could build and cause lightning storms.</h5>
          </section>

          <!-- second screen -->
          <section class="screen second-screen">
            <img id="pokemon-image-stats" src="img/pikachu.png" alt="Pikachu">

            <div>
              <p><strong>HP:</strong> <span id="stat-hp">35</span></p>
              <p><strong>AT:</strong> <span id="stat-attack">55</span></p>
            </div>
            <div>
              <p><strong>DEF:</strong> <span id="stat-defense">35</span></p>
              <p><strong>SDEF:</strong> <span id="stat-special-defense">55</span></p>
            </div>
            <div>
              <p><strong>SAT:</strong> <span id="stat-special-attack">35</span></p>
              <p><strong>SP:</strong> <span id="stat-speed">55</span></p>
            </div>
          </section>
          
          <!-- third screen -->
          <section class="screen third-screen">
            <div class="img-height-weight">
              <img id="pokemon-image-size" src="img/pikachu.png" alt="Pikachu">
              <p class="height"><span>}</span><span id="height">0.4</span> m</p>
              <p class="weight"><span id="weight">6.0</span> kg</p>
            </div>
            <div class="gender">
              <p>M</p>
              <p>F</p>
            </div>
          </section>

          <!-- fourth screen -->
          <section class="screen fourth-screen">
            <div class="evolution">
              <img id="evolution-first" class="pokemon" src="img/pichu.png" alt="pichu">
              <img src="img/flecha.svg" alt=">">
              <img id="evolution-second" class="pokemon" src="img/pikachu.png" alt="pichu">
              <img src="img/flecha.svg" alt=">">
              <img id="evolution-third" class="pokemon" src="img/raichu.png" alt="pichu">
            </div>
            <p id="evolution-text" class="evolution-text">
              <strong>Starting Pokémon:</strong> Pichu (ID=172) <br><br>
              <strong>[step 1]</strong> Pichu (ID=172) evolves into Pikachu (ID=25) via friendship method <br><br>
              <strong>[step 2]</strong> Pikachu (ID=25) evolves into Raichu (ID=26) using a THUNDER STONE via item method
            </p>
          </section>

          <!-- fifth screen -->
          <section class="screen fifth-screen">
            <div id="abilities-container">
              <div class="habilidad">
                <h1>Static <span><img src="img/show.png" alt=""></span></h1>
                <p>May paralyze attackers</p>
              </div>
              <div class="habilidad">
                <h1>Lightning-rod <span><img src="img/show.png" alt=""></span></h1>
                <p>draws electrical moves</p>
              </div>
            </div>
          </section>

          <!-- sixth screen -->
          <section class="screen sixth-screen">
            <div id="moves-container">
              <div class="moves">
                <div class="general">
                  <h1>Thunderbolt</h1>
                  <p>A strong electric blast crashes down on the target.</p>
                </div>
                <div class="detalles">
                  <p>Electric</p>
                  <p><strong>PW:</strong> 90</p>
                  <p><strong>AC:</strong> 100</p>
                  <p><strong>PP:</strong> 15</p>
                </div>
              </div>
              <div class="moves">
                <div class="general">
                  <h1>Quick Attack</h1>
                  <p>The user lunges at the target at a speed that makes it almost invisible.</p>
                </div>
                <div class="detalles">
                  <p>Normal</p>
                  <p><strong>PW:</strong> 40</p>
                  <p><strong>AC:</strong> 100</p>
                  <p><strong>PP:</strong> 30</p>
                </div>
              </div>
            </div>
          </section>

        </div>
      </div>

      <!-- lineas diagonales -->
      <div class="container_diagonales first-level-parax">
        <div class="linea_diagonal brown"></div>
        <div class="linea_diagonal brown"></div>
        <div class="linea_diagonal brown"></div>
        <div class="linea_diagonal brown"></div>
      </div>

      <!-- button panel -->
      <div class="button-panel">
          <button class="buttonId">Id</button>                 <!-- buscar por id -->
          <button class="buttonCompare">Compare</button>       <!-- comparar pokemon -->
          <button class="buttonRandom">Random</button>         <!-- random pokemon -->
          <button class="buttonName">Name</button>             <!-- buscar por nombre -->
          <button class="buttonLike">Like</button>             <!-- Añadir en favoritos el pokemon -->
          <button class="buttonExport">Export</button>         <!-- exportar info sobre pokemon (json) -->
      </div>
          
      <!-- botones de izquierda -->
      <div class="boton_principal_izquierda">
        <button class="boton_principal_top white pre-level-para">
          <div class="circulo_negro_buton black"></div>
        </button>
        <button class="boton_principal_bottom white pre-level-para">
          <div class="circulo_negro_buton black"></div>
        </button>
        <!-- boton azul -->
        <button class="circulo_azul blue pre-level-para"></button>
      </div>

      <div class="botones-control pre-level-parax">
        <button class="up"><img src="img/arrow.png" alt=">"></button>
        <button class="down"><img src="img/arrow.png" alt=">"></button>
        <button class="left"><img src="img/arrow.png" alt=">"></button>
        <button class="right"><img src="img/arrow.png" alt=">"></button>
      </div>

      <!-- switches -->
      <div class="container_switches pre-level-parax">
        <div class="switcher brown">
          <div class="lever white pre-level-parax"></div>
        </div>
        <div class="switcher brown">
          <div class="lever white pre-level-parax"></div>
        </div>
      </div>

      <!-- lights -->
      <div class="lights pre-level-parax">
        <div class="light-red"></div>
        <div class="light-yellow"></div>
        <div class="light-green"></div>
      </div>

    </div>

    <script>
      // Передаємо початкові дані в JavaScript
      const initialPokemon = <?php echo $initialPokemonJson; ?>;
    </script>
    <script src="js/tilt.js"></script>
    <script src="js/pokedex.js"></script>
    <script src="js/main.js"></script>
</body>
</html>