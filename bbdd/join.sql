-- Listar todos los pokemons y sus habilidades
SELECT 
  p.id AS pokemon_id,
  p.nombre AS Pokemon, 
  GROUP_CONCAT(h.nombre ORDER BY p.nombre SEPARATOR ' | ') AS Habilidades
FROM pokedex p
JOIN pokemon_habilidades ph ON p.id = ph.pokemon_id
JOIN habilidades h ON ph.habilidad_id = h.id
GROUP BY p.id, p.nombre;


  
  -- Listar todos los pokemons con sus movimientos incluso si no tienen ninguno asignado aún
SELECT 
  p.id AS pokemon_id,
  p.nombre AS Pokemon, 
  GROUP_CONCAT(m.nombre ORDER BY p.nombre SEPARATOR ' | ') AS Movimientos
FROM pokedex p
LEFT JOIN pokemon_movimientos pm ON p.id = pm.pokemon_id
LEFT JOIN movimientos m ON pm.movimiento_id = m.id
GROUP BY p.id, p.nombre;



-- Lista todos los movimientos junto con los Pokémon que pueden aprenderlos. 
-- Incluye también los movimientos que aún no están asignados a ningún Pokémon. 
-- Muestra información adicional como el tipo del movimiento y el tipo principal del Pokémon. PISTA: RIGHT JOIN
SELECT 
    m.nombre AS Movimiento,
    m.tipo AS TipoMovimiento,
    GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.nombre SEPARATOR ' | ') AS Pokemon,
    GROUP_CONCAT(DISTINCT p.tipo_primario ORDER BY p.tipo_primario SEPARATOR ' | ') AS TipoPokemon
FROM pokemon_movimientos pm
RIGHT JOIN movimientos m ON pm.movimiento_id = m.id
LEFT JOIN pokedex p ON pm.pokemon_id = p.id
GROUP BY m.id, m.nombre, m.tipo;
