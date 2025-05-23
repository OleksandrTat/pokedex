CREATE DATABASE IF NOT EXISTS pokemon;
USE pokemon;

CREATE TABLE pokemon (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(100) NOT NULL,
    description TEXT,
    height DECIMAL(5,2),
    weight DECIMAL(5,2),
    gender_rate INT,      -- -1: sin género, 0: sólo macho, 8: sólo hembra, 1-7: probabilidad de ser hembra
    image_url VARCHAR(255)
);

CREATE TABLE types (
    id INT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) NOT NULL
);

CREATE TABLE pokemon_types (
    pokemon_id INT,
    type_id INT,
    PRIMARY KEY (pokemon_id, type_id),
    FOREIGN KEY (pokemon_id) REFERENCES pokemon(id),
    FOREIGN KEY (type_id) REFERENCES types(id)
);

CREATE TABLE stats (
    pokemon_id INT PRIMARY KEY,
    hp INT NOT NULL,
    attack INT NOT NULL,
    defense INT NOT NULL,
    special_attack INT NOT NULL,
    special_defense INT NOT NULL,
    speed INT NOT NULL,
    FOREIGN KEY (pokemon_id) REFERENCES pokemon(id)
);

CREATE TABLE evolution_methods (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE evolutions (
    id INT PRIMARY KEY,
    from_pokemon_id INT,
    to_pokemon_id INT,
    evolution_method_id INT,
    level_required INT,
    item_required VARCHAR(100),
    condition_description TEXT,
    FOREIGN KEY (from_pokemon_id) REFERENCES pokemon(id),
    FOREIGN KEY (to_pokemon_id) REFERENCES pokemon(id),
    FOREIGN KEY (evolution_method_id) REFERENCES evolution_methods(id)
);

CREATE TABLE favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pokemon_id INT,
    user_id INT,
    date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pokemon_id) REFERENCES pokemon(id)
);

CREATE TABLE comparisons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pokemon1_id INT,
    pokemon2_id INT,
    date_compared DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pokemon1_id) REFERENCES pokemon(id),
    FOREIGN KEY (pokemon2_id) REFERENCES pokemon(id)
);