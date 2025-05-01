CREATE DATABASE pokemon;
DROP DATABASE pokemon;
USE pokemon;

CREATE TABLE pokedex (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero_dex INT NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  tipo_primario VARCHAR(50) NOT NULL,
  tipo_secundario VARCHAR(50),
  descripcion TEXT,
  imagen VARCHAR(250) NOT NULL,
  hp INT NOT NULL,
  ataque INT NOT NULL,
  defensa INT NOT NULL,
  ataque_especial INT NOT NULL,
  defensa_especial INT NOT NULL,
  velocidad INT NOT NULL,
  categoria VARCHAR(50)
);
-- SELECT * FROM pokedex;



CREATE TABLE habilidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE, -- Nombre de la habilidad
  descripcion TEXT,           -- Descripcion detallada de la habilidad
  tipo VARCHAR(10)
);
-- SELECT * FROM habilidades;



CREATE TABLE pokemon_habilidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokemon_id INT NOT NULL,       -- Relacion con la tabla Pokedex
  habilidad_id INT NOT NULL,      -- Relacion con la tabla Habilidades
  FOREIGN KEY (pokemon_id) REFERENCES pokedex(id),
  FOREIGN KEY (habilidad_id) REFERENCES habilidades(id)
);
-- SELECT * FROM pokemon_habilidades;



CREATE TABLE evoluciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokemon_base_id INT NOT NULL,    -- Pokemon que evoluciona
  pokemon_evolucion_id INT NOT NULL, -- Pokemon resultante de la evolucion
  nivel_requerido INT,        -- Nivel necesario para evolucionar (puede ser NULL)
  metodo VARCHAR(100),        -- Metodo de evolucion (Piedra, Amistad, etc.)
  FOREIGN KEY (pokemon_base_id) REFERENCES pokedex(id),
  FOREIGN KEY (pokemon_evolucion_id) REFERENCES pokedex(id)
);
-- SELECT * FROM evoluciones;



CREATE TABLE movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  tipo VARCHAR(50) NOT NULL,
  potencia INT,
  precicion INT,
  pp INT,
  clase ENUM('Fisico', 'Especial', 'Estado'),
  efecto TEXT,
  descripcion TEXT
);
-- SELECT * FROM movimientos;



CREATE TABLE pokemon_movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokemon_id INT NOT NULL,       -- Relacion con la tabla Pokedex
  movimiento_id INT NOT NULL,     -- Relacion con la tabla Movimientos
  FOREIGN KEY (pokemon_id) REFERENCES pokedex(id),
  FOREIGN KEY (movimiento_id) REFERENCES movimientos(id)
);
-- SELECT * FROM pokemon_movimientos;



CREATE TABLE auditoria_evoluciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  operacion VARCHAR(200)
);
-- SELECT * FROM auditoria_evoluciones;

