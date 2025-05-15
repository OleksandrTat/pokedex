<?php
require_once 'php/config.php';
require_once 'php/pokemon_functions.php';

// Default pokemon id (Pikachu - #25)
$pokemonId = isset($_GET['id']) ? (int)$_GET['id'] : 25;

// Get pokemon data
$pokemonData = getPokemonById($conn, $pokemonId);

// If no pokemon found, redirect to the first one
if (empty($pokemonData)) {
    header('Location: index.php?id=1');
    exit;
}

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
  <title>Pokedex - <?= htmlspecialchars($pokemonData['basic']['name'] ?? 'Pokemon') ?></title>
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
              <h2><?= htmlspecialchars($pokemonData['basic']['name'] ?? 'Unknown') ?></h2>
              <p>#<?= $pokemonData['basic']['pokedex_number'] ?? '0' ?></p>
            </div>
            <div>
              <div class="type1" style="background-color: <?= $pokemonData['basic']['primary_type_color'] ?? '#A8A878' ?>">
                <?= htmlspecialchars($pokemonData['basic']['primary_type'] ?? 'Normal') ?>
              </div>
              <?php if (!empty($pokemonData['basic']['secondary_type'])): ?>
              <div class="type2" style="background-color: <?= $pokemonData['basic']['secondary_type_color'] ?? '#A8A878' ?>">
                <?= htmlspecialchars($pokemonData['basic']['secondary_type']) ?>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- first screen -->
          <section class="screen first-screen">
            <img src="<?= htmlspecialchars($pokemonData['basic']['image_url'] ?? 'img/pikachu.png') ?>" alt="<?= htmlspecialchars($pokemonData['basic']['name'] ?? 'Pokemon') ?>">

            <p class="species">Species:<br><?= htmlspecialchars($pokemonData['basic']['category'] ?? 'Unknown Pokemon') ?></p>
            <h5><?= htmlspecialchars($pokemonData['basic']['description'] ?? 'No description available.') ?></h5>
          </section>

          <!-- second screen -->
          <section class="screen second-screen">
            <img src="<?= htmlspecialchars($pokemonData['basic']['image_url'] ?? 'img/pikachu.png') ?>" alt="<?= htmlspecialchars($pokemonData['basic']['name'] ?? 'Pokemon') ?>">

            <div>
              <p><strong>HP:</strong> <?= $pokemonData['stats']['hp'] ?? '0' ?></p>
              <p><strong>AT:</strong> <?= $pokemonData['stats']['attack'] ?? '0' ?></p>
            </div>
            <div>
              <p><strong>DEF:</strong> <?= $pokemonData['stats']['defense'] ?? '0' ?></p>
              <p><strong>SDEF:</strong> <?= $pokemonData['stats']['special_defense'] ?? '0' ?></p>
            </div>
            <div>
              <p><strong>SAT:</strong> <?= $pokemonData['stats']['special_attack'] ?? '0' ?></p>
              <p><strong>SP:</strong> <?= $pokemonData['stats']['speed'] ?? '0' ?></p>
            </div>
          </section>
          
          <!-- third screen -->
          <section class="screen third-screen">
            <div class="img-height-weight">
              <img src="<?= htmlspecialchars($pokemonData['basic']['image_url'] ?? 'img/pikachu.png') ?>" alt="<?= htmlspecialchars($pokemonData['basic']['name'] ?? 'Pokemon') ?>">
              <p class="height"><span>}</span><?= number_format($pokemonData['physical']['height'] / 10, 1) ?? '0.0' ?> m</p>
              <p class="weight"><?= number_format($pokemonData['physical']['weight'] / 10, 1) ?? '0.0' ?> kg</p>
            </div>
            <div class="gender">
              <?php if (empty($pokemonData['physical']['gender_specific']) || $pokemonData['physical']['gender_specific'] === 'M'): ?>
                <p>M</p>
              <?php endif; ?>
              <?php if (empty($pokemonData['physical']['gender_specific']) || $pokemonData['physical']['gender_specific'] === 'F'): ?>
                <p>F</p>
              <?php endif; ?>
            </div>
          </section>

          <!-- fourth screen -->
          <section class="screen fourth-screen">
            <div class="evolution">
              <?php 
              if (!empty($pokemonData['evolution']['result'])) {
                  echo $pokemonData['evolution']['result'];
              } else {
                  echo "<p>No evolution information available.</p>";
              }
              ?>
            </div>
          </section>

          <!-- fifth screen -->
          <section class="screen fifth-screen">
            <?php if (!empty($pokemonData['abilities'])): ?>
              <?php foreach ($pokemonData['abilities'] as $ability): ?>
                <div class="habilidad">
                  <h1><?= htmlspecialchars($ability['name']) ?> <span><img src="img/show.png" alt=""></span></h1>
                  <p><?= htmlspecialchars($ability['description']) ?></p>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="habilidad">
                <h1>No abilities <span><img src="img/show.png" alt=""></span></h1>
                <p>This Pokemon has no known abilities.</p>
              </div>
            <?php endif; ?>
          </section>

          <!-- sixth screen -->
          <section class="screen sixth-screen">
            <?php if (!empty($pokemonData['moves'])): ?>
              <?php foreach ($pokemonData['moves'] as $move): ?>
                <div class="moves">
                  <div class="general">
                    <h1><?= htmlspecialchars($move['name']) ?></h1>
                    <p><?= htmlspecialchars($move['description']) ?></p>
                  </div>
                  <div class="detalles">
                    <p style="background-color: <?= $move['type_color'] ?? '#A8A878' ?>"><?= htmlspecialchars($move['type_name']) ?></p>
                    <p><strong>PW:</strong> <?= $move['power'] ?? 'N/A' ?></p>
                    <p><strong>AC:</strong> <?= $move['accuracy'] ?? 'N/A' ?></p>
                    <p><strong>PP:</strong> <?= $move['pp'] ?? 'N/A' ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="moves">
                <div class="general">
                  <h1>No moves</h1>
                  <p>This Pokemon has no known moves.</p>
                </div>
              </div>
            <?php endif; ?>
          </section>

        </div>
      </div>

      

    </div>

    <script src="js/navigation.js"></script>
    <script src="js/tilt.js"></script>
    <script src="js/main.js"></script>
</body>
</html>