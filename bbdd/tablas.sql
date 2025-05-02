-- Створюємо та використовуємо базу даних
CREATE DATABASE IF NOT EXISTS pokemon;
USE pokemon;

-- Таблиця типів покемонів (для нормалізації даних)
CREATE TABLE types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  color_code VARCHAR(7) DEFAULT '#FFFFFF' -- Колір для відображення типу (hex)
);

-- Основна таблиця покемонів
CREATE TABLE pokemon (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokedex_number INT NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  primary_type_id INT NOT NULL,
  secondary_type_id INT NULL,
  description TEXT,
  image_url VARCHAR(250) NOT NULL,
  height FLOAT, -- Висота в метрах
  weight FLOAT, -- Вага в кг
  category VARCHAR(50), -- Категорія покемона (наприклад, "Seed Pokemon")
  generation INT, -- Покоління, до якого належить покемон
  is_legendary BOOLEAN DEFAULT FALSE,
  is_mythical BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (primary_type_id) REFERENCES types(id),
  FOREIGN KEY (secondary_type_id) REFERENCES types(id)
);

-- Таблиця характеристик покемонів
CREATE TABLE pokemon_stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokemon_id INT NOT NULL,
  hp INT NOT NULL,
  attack INT NOT NULL,
  defense INT NOT NULL,
  special_attack INT NOT NULL,
  special_defense INT NOT NULL,
  speed INT NOT NULL,
  total_points INT GENERATED ALWAYS AS (hp + attack + defense + special_attack + special_defense + speed) STORED, -- Обчислюване поле
  FOREIGN KEY (pokemon_id) REFERENCES pokemon(id) ON DELETE CASCADE
);

-- Таблиця здібностей
CREATE TABLE abilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  is_hidden BOOLEAN DEFAULT FALSE -- Позначення прихованих здібностей
);

-- Таблиця зв'язку покемонів і здібностей
CREATE TABLE pokemon_abilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokemon_id INT NOT NULL,
  ability_id INT NOT NULL,
  is_hidden BOOLEAN DEFAULT FALSE, -- Чи є здібність прихованою для цього покемона
  FOREIGN KEY (pokemon_id) REFERENCES pokemon(id) ON DELETE CASCADE,
  FOREIGN KEY (ability_id) REFERENCES abilities(id) ON DELETE CASCADE,
  UNIQUE KEY unique_pokemon_ability (pokemon_id, ability_id) -- Унікальна комбінація
);

-- Таблиця груп еволюцій
CREATE TABLE evolution_chains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  identifier VARCHAR(100)
);

-- Таблиця деталей еволюції
CREATE TABLE evolutions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  evolution_chain_id INT NOT NULL,
  base_pokemon_id INT NOT NULL,
  evolved_pokemon_id INT NOT NULL,
  level_required INT,
  item_required VARCHAR(100),
  evolution_method VARCHAR(100), -- Метод еволюції (камінь, обмін, дружба і т.д.)
  evolution_condition TEXT, -- Додаткові умови
  order_in_chain INT NOT NULL, -- Порядок в ланцюжку еволюції
  FOREIGN KEY (evolution_chain_id) REFERENCES evolution_chains(id) ON DELETE CASCADE,
  FOREIGN KEY (base_pokemon_id) REFERENCES pokemon(id) ON DELETE CASCADE,
  FOREIGN KEY (evolved_pokemon_id) REFERENCES pokemon(id) ON DELETE CASCADE
);

-- Таблиця ходів
CREATE TABLE moves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  type_id INT NOT NULL,
  power INT,
  accuracy INT,
  pp INT, -- Power Points
  damage_class ENUM('Physical', 'Special', 'Status') NOT NULL,
  effect TEXT,
  effect_chance INT, -- Шанс додаткового ефекту (у відсотках)
  description TEXT,
  FOREIGN KEY (type_id) REFERENCES types(id)
);

-- Таблиця способів вивчення ходів
CREATE TABLE learn_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE -- Наприклад: leveling, TM/HM, breeding, tutor
);

-- Таблиця зв'язку покемонів і ходів
CREATE TABLE pokemon_moves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pokemon_id INT NOT NULL,
  move_id INT NOT NULL,
  learn_method_id INT NOT NULL,
  level_learned INT, -- Рівень, на якому вивчається хід (NULL для TM/HM)
  generation_id INT, -- У якому поколінні доступний
  FOREIGN KEY (pokemon_id) REFERENCES pokemon(id) ON DELETE CASCADE,
  FOREIGN KEY (move_id) REFERENCES moves(id) ON DELETE CASCADE,
  FOREIGN KEY (learn_method_id) REFERENCES learn_methods(id) ON DELETE CASCADE
);

-- Таблиця слабкостей і стійкостей типів
CREATE TABLE type_effectiveness (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attacking_type_id INT NOT NULL,
  defending_type_id INT NOT NULL,
  effectiveness FLOAT NOT NULL, -- 0, 0.5, 1, 2 (не діє, не дуже ефективно, нормально, супер ефективно)
  FOREIGN KEY (attacking_type_id) REFERENCES types(id) ON DELETE CASCADE,
  FOREIGN KEY (defending_type_id) REFERENCES types(id) ON DELETE CASCADE,
  UNIQUE KEY unique_type_matchup (attacking_type_id, defending_type_id)
);

-- Таблиця аудиту змін в еволюціях
CREATE TABLE evolution_audit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  operation VARCHAR(200),
  pokemon_id INT,
  evolution_chain_id INT,
  user_id VARCHAR(50), -- Якщо у вас є система користувачів
  details TEXT
);

-- Представлення для швидкого пошуку покемонів
CREATE VIEW pokemon_search_view AS
SELECT 
  p.id,
  p.pokedex_number,
  p.name,
  t1.name AS primary_type,
  t2.name AS secondary_type,
  p.image_url,
  ps.hp,
  ps.attack,
  ps.defense,
  ps.special_attack,
  ps.special_defense,
  ps.speed,
  ps.total_points
FROM pokemon p
JOIN types t1 ON p.primary_type_id = t1.id
LEFT JOIN types t2 ON p.secondary_type_id = t2.id
LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id;

-- Представлення для детальної інформації про покемона
CREATE VIEW pokemon_details_view AS
SELECT 
  p.id,
  p.pokedex_number,
  p.name,
  t1.name AS primary_type,
  t2.name AS secondary_type,
  p.description,
  p.image_url,
  p.height,
  p.weight,
  p.category,
  p.generation,
  p.is_legendary,
  p.is_mythical,
  ps.hp,
  ps.attack,
  ps.defense,
  ps.special_attack,
  ps.special_defense,
  ps.speed,
  ps.total_points,
  ec.id AS evolution_chain_id
FROM pokemon p
JOIN types t1 ON p.primary_type_id = t1.id
LEFT JOIN types t2 ON p.secondary_type_id = t2.id
LEFT JOIN pokemon_stats ps ON p.id = ps.pokemon_id
LEFT JOIN evolutions e ON p.id = e.base_pokemon_id OR p.id = e.evolved_pokemon_id
LEFT JOIN evolution_chains ec ON e.evolution_chain_id = ec.id
GROUP BY p.id;

-- Індекси для оптимізації пошуку
CREATE INDEX idx_pokemon_name ON pokemon(name);
CREATE INDEX idx_pokemon_pokedex_number ON pokemon(pokedex_number);
CREATE INDEX idx_pokemon_primary_type ON pokemon(primary_type_id);
CREATE INDEX idx_pokemon_secondary_type ON pokemon(secondary_type_id);
CREATE INDEX idx_pokemon_legendary ON pokemon(is_legendary);
CREATE INDEX idx_pokemon_mythical ON pokemon(is_mythical);
CREATE INDEX idx_pokemon_generation ON pokemon(generation);

-- Тригер для запису змін у таблиці еволюцій
DELIMITER //
CREATE TRIGGER after_evolution_insert
AFTER INSERT ON evolutions
FOR EACH ROW
BEGIN
    INSERT INTO evolution_audit (operation, pokemon_id, evolution_chain_id, details)
    VALUES ('INSERT', NEW.base_pokemon_id, NEW.evolution_chain_id, 
            CONCAT('Added evolution from Pokemon #', NEW.base_pokemon_id, ' to #', NEW.evolved_pokemon_id));
END;
//

CREATE TRIGGER after_evolution_delete
AFTER DELETE ON evolutions
FOR EACH ROW
BEGIN
    INSERT INTO evolution_audit (operation, pokemon_id, evolution_chain_id, details)
    VALUES ('DELETE', OLD.base_pokemon_id, OLD.evolution_chain_id, 
            CONCAT('Removed evolution from Pokemon #', OLD.base_pokemon_id, ' to #', OLD.evolved_pokemon_id));
END;
//

CREATE TRIGGER after_evolution_update
AFTER UPDATE ON evolutions
FOR EACH ROW
BEGIN
    INSERT INTO evolution_audit (operation, pokemon_id, evolution_chain_id, details)
    VALUES ('UPDATE', NEW.base_pokemon_id, NEW.evolution_chain_id, 
            CONCAT('Updated evolution from Pokemon #', NEW.base_pokemon_id, ' to #', NEW.evolved_pokemon_id));
END;
//
DELIMITER ;

-- Приклад заповнення таблиці типів
INSERT INTO types (name, color_code) VALUES 
('Normal', '#A8A878'),
('Fire', '#F08030'),
('Water', '#6890F0'),
('Electric', '#F8D030'),
('Grass', '#78C850'),
('Ice', '#98D8D8'),
('Fighting', '#C03028'),
('Poison', '#A040A0'),
('Ground', '#E0C068'),
('Flying', '#A890F0'),
('Psychic', '#F85888'),
('Bug', '#A8B820'),
('Rock', '#B8A038'),
('Ghost', '#705898'),
('Dragon', '#7038F8'),
('Dark', '#705848'),
('Steel', '#B8B8D0'),
('Fairy', '#EE99AC');

-- Приклад заповнення таблиці методів вивчення ходів
INSERT INTO learn_methods (name) VALUES 
('Level Up'),
('TM/HM'),
('Egg Move'),
('Tutor'),
('Event');