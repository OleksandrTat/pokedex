-- Inicio
INSERT INTO pokedex (numero_dex, nombre, tipo_primario, tipo_secundario, descripcion, imagen, hp, ataque, defensa, ataque_especial, defensa_especial, velocidad, categoria)
VALUES
  (25, 'Pikachu', 'Electrico', NULL, 'Pikachu es un Pokémon de tipo eléctrico.', 'url_pikachu', 35, 55, 40, 50, 50, 90, 'Roedor'),
  (4, 'Charmander', 'Fuego', NULL, 'Charmander es un Pokémon de tipo fuego.', 'url_charmander', 39, 52, 43, 60, 50, 65, 'Lagartija'),
  (1, 'Bulbasaur', 'Planta', 'Veneno', 'Bulbasaur es un Pokémon de tipo planta y veneno.', 'url_bulbasaur', 45, 49, 49, 65, 65, 45, 'Semilla'),
  (2, 'Ivysaur', 'Planta', 'Veneno', 'Ivysaur es la evolución de Bulbasaur.', 'url_ivysaur', 60, 62, 63, 80, 80, 60, 'Semilla'),
  (19, 'Rattata', 'Normal', NULL, 'Rattata es un Pokémon de tipo normal.', 'url_rattata', 30, 56, 35, 25, 35, 72, 'Roedor');


INSERT INTO movimientos (nombre, tipo, potencia, precicion, pp, clase, efecto, descripcion)
VALUES
  ('Thunderbolt', 'Electrico', 90, 100, 15, 'Especial', 'Puede paralizar.', 'Ataque eléctrico con posibilidad de paralizar.'),
  ('Flamethrower', 'Fuego', 90, 100, 15, 'Especial', 'Puede quemar.', 'Ataque de fuego con posibilidad de quemar.'),
  ('Tackle', 'Normal', 40, 100, 35, 'Fisico', NULL, 'Ataque físico básico.'),
  ('Vine Whip', 'Planta', 45, 100, 25, 'Fisico', NULL, 'Ataque físico de planta.');



INSERT INTO habilidades (nombre, descripcion, tipo)
VALUES
  ('Static', 'Puede causar parálisis.', 'Normal'),
  ('Blaze', 'Potencia los ataques de fuego.', 'Fuego'),
  ('Overgrow', 'Potencia los movimientos de planta.', 'Planta');

INSERT INTO pokemon_habilidades (pokemon_id, habilidad_id) VALUES (1, 1);
INSERT INTO pokemon_habilidades (pokemon_id, habilidad_id) VALUES (1, 2);
INSERT INTO pokemon_habilidades (pokemon_id, habilidad_id) VALUES (2, 2);
INSERT INTO pokemon_habilidades (pokemon_id, habilidad_id) VALUES (3, 3);



-- verificar el primer trigger de auditoría
INSERT INTO evoluciones (pokemon_base_id, pokemon_evolucion_id, nivel_requerido, metodo)
VALUES (3, 4, 16, 'Nivel');

SELECT * FROM auditoria_evoluciones;


-- verificar el segundo trigger de comprobacion de los movimientos
INSERT INTO pokemon_movimientos (pokemon_id, movimiento_id)
VALUES (1, 1); -- True

INSERT INTO pokemon_movimientos (pokemon_id, movimiento_id)
VALUES (2, 2); -- True

INSERT INTO pokemon_movimientos (pokemon_id, movimiento_id)
VALUES (5, 3); -- True

INSERT INTO pokemon_movimientos (pokemon_id, movimiento_id)
VALUES (3, 4); -- True

INSERT INTO pokemon_movimientos (pokemon_id, movimiento_id)
VALUES (2, 3); -- False

INSERT INTO pokemon_movimientos (pokemon_id, movimiento_id)
VALUES (2, 4); -- False





-- verificar el tercer trigger de auditoria al UPDATE
UPDATE evoluciones
SET nivel_requerido = 18
WHERE id = 1;

SELECT * FROM auditoria_evoluciones;