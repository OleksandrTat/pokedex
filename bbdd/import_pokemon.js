// import_pokemon.js - Скрипт для імпорту даних з PokeAPI в базу даних MySQL

const axios = require('axios');
const mysql = require('mysql2/promise');

// Налаштування бази даних
const dbConfig = {
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'pokemon'
};

// Базова URL-адреса API
const API_BASE_URL = 'https://pokeapi.co/api/v2';

// Основна функція імпорту
async function importPokemonData() {
  console.log('Починаємо імпорт даних про покемонів...');
  
  // Створюємо підключення до бази даних
  const connection = await mysql.createConnection(dbConfig);
  console.log('Підключено до бази даних');
  
  try {
    // 1. Імпортуємо типи покемонів
    await importTypes(connection);
    
    // 2. Імпортуємо здібності
    await importAbilities(connection);
    
    // 3. Імпортуємо ходи
    await importMoves(connection);
    
    // 4. Імпортуємо покемонів та пов'язані дані
    await importPokemons(connection);
    
    // 5. Імпортуємо дані про еволюції
    await importEvolutions(connection);
    
    console.log('Імпорт даних успішно завершено!');
  } catch (error) {
    console.error('Помилка під час імпорту даних:', error);
  } finally {
    // Закриваємо підключення
    await connection.end();
    console.log('Підключення до бази даних закрито');
  }
}

// Функція для імпорту типів
async function importTypes(connection) {
  console.log('Імпортуємо типи покемонів...');
  
  const response = await axios.get(`${API_BASE_URL}/type`);
  const types = response.data.results;
  
  for (const type of types) {
    // Отримуємо детальну інформацію про тип
    const typeDetails = await axios.get(type.url);
    const typeName = type.name;
    
    // Визначаємо колір для типу (це приблизні значення, можна налаштувати)
    const typeColors = {
      normal: '#A8A878',
      fire: '#F08030',
      water: '#6890F0',
      electric: '#F8D030',
      grass: '#78C850',
      ice: '#98D8D8',
      fighting: '#C03028',
      poison: '#A040A0',
      ground: '#E0C068',
      flying: '#A890F0',
      psychic: '#F85888',
      bug: '#A8B820',
      rock: '#B8A038',
      ghost: '#705898',
      dragon: '#7038F8',
      dark: '#705848',
      steel: '#B8B8D0',
      fairy: '#EE99AC',
      unknown: '#68A090',
      shadow: '#705898'
    };
    
    const colorCode = typeColors[typeName] || '#777777';
    
    // Вставляємо тип в базу даних
    try {
      await connection.execute(
        'INSERT IGNORE INTO types (name, color_code) VALUES (?, ?)',
        [typeName, colorCode]
      );
      console.log(`Тип "${typeName}" додано`);
      
      // Додаємо ефективність типів
      await importTypeEffectiveness(connection, typeDetails.data, typeName);
    } catch (error) {
      console.error(`Помилка при додаванні типу "${typeName}":`, error);
    }
  }
  
  console.log('Імпорт типів завершено');
}

// Функція для імпорту ефективності типів
async function importTypeEffectiveness(connection, typeData, attackingType) {
  // Отримуємо ID типу, що атакує
  const [attackingTypeRow] = await connection.execute(
    'SELECT id FROM types WHERE name = ?',
    [attackingType]
  );
  
  if (attackingTypeRow.length === 0) return;
  const attackingTypeId = attackingTypeRow[0].id;
  
  // Обробка подвійної шкоди (effectiveness = 2)
  for (const relation of typeData.damage_relations.double_damage_to) {
    await addTypeEffectiveness(connection, attackingTypeId, relation.name, 2);
  }
  
  // Обробка половинної шкоди (effectiveness = 0.5)
  for (const relation of typeData.damage_relations.half_damage_to) {
    await addTypeEffectiveness(connection, attackingTypeId, relation.name, 0.5);
  }
  
  // Обробка відсутності шкоди (effectiveness = 0)
  for (const relation of typeData.damage_relations.no_damage_to) {
    await addTypeEffectiveness(connection, attackingTypeId, relation.name, 0);
  }
  
  // Нормальна шкода для всіх інших (за замовчуванням effectiveness = 1)
}

// Додавання запису про ефективність
async function addTypeEffectiveness(connection, attackingTypeId, defendingTypeName, effectiveness) {
  try {
    // Отримуємо ID типу, що захищається
    const [defendingTypeRow] = await connection.execute(
      'SELECT id FROM types WHERE name = ?',
      [defendingTypeName]
    );
    
    if (defendingTypeRow.length === 0) return;
    const defendingTypeId = defendingTypeRow[0].id;
    
    // Додаємо або оновлюємо запис про ефективність
    await connection.execute(
      `INSERT INTO type_effectiveness (attacking_type_id, defending_type_id, effectiveness)
       VALUES (?, ?, ?)
       ON DUPLICATE KEY UPDATE effectiveness = ?`,
      [attackingTypeId, defendingTypeId, effectiveness, effectiveness]
    );
  } catch (error) {
    console.error('Помилка при додаванні ефективності типів:', error);
  }
}

// Функція для імпорту здібностей
async function importAbilities(connection) {
  console.log('Імпортуємо здібності покемонів...');
  
  // Отримуємо список всіх здібностей
  const response = await axios.get(`${API_BASE_URL}/ability?limit=1000`);
  const abilities = response.data.results;
  
  for (const ability of abilities) {
    try {
      // Отримуємо детальну інформацію про здібність
      const abilityDetails = await axios.get(ability.url);
      const abilityData = abilityDetails.data;
      
      // Знаходимо опис англійською мовою
      let description = '';
      for (const flavorText of abilityData.flavor_text_entries) {
        if (flavorText.language.name === 'en') {
          description = flavorText.flavor_text;
          break;
        }
      }
      
      // Вставляємо здібність в базу даних
      await connection.execute(
        'INSERT IGNORE INTO abilities (name, description, is_hidden) VALUES (?, ?, ?)',
        [abilityData.name, description, false] // За замовчуванням не прихована
      );
      
      console.log(`Здібність "${abilityData.name}" додано`);
    } catch (error) {
      console.error(`Помилка при додаванні здібності "${ability.name}":`, error);
    }
  }
  
  console.log('Імпорт здібностей завершено');
}

// Функція для імпорту ходів
async function importMoves(connection) {
  console.log('Імпортуємо ходи покемонів...');
  
  // Додаємо методи вивчення ходів
  const learnMethods = ['level-up', 'machine', 'egg', 'tutor', 'event'];
  for (const method of learnMethods) {
    try {
      await connection.execute(
        'INSERT IGNORE INTO learn_methods (name) VALUES (?)',
        [method]
      );
    } catch (error) {
      console.error(`Помилка при додаванні методу "${method}":`, error);
    }
  }
  
  // Отримуємо список всіх ходів
  const response = await axios.get(`${API_BASE_URL}/move?limit=1000`);
  const moves = response.data.results;
  
  let counter = 0;
  for (const move of moves) {
    try {
      // Отримуємо детальну інформацію про хід
      const moveDetails = await axios.get(move.url);
      const moveData = moveDetails.data;
      
      // Знаходимо ID типу
      const [typeRow] = await connection.execute(
        'SELECT id FROM types WHERE name = ?',
        [moveData.type.name]
      );
      
      if (typeRow.length === 0) continue;
      const typeId = typeRow[0].id;
      
      // Знаходимо опис англійською мовою
      let description = '';
      for (const flavorText of moveData.flavor_text_entries) {
        if (flavorText.language.name === 'en') {
          description = flavorText.flavor_text;
          break;
        }
      }
      
      // Визначаємо клас пошкодження
      let damageClass = 'Status';
      if (moveData.damage_class) {
        if (moveData.damage_class.name === 'physical') {
          damageClass = 'Physical';
        } else if (moveData.damage_class.name === 'special') {
          damageClass = 'Special';
        }
      }
      
      // Вставляємо хід в базу даних
      await connection.execute(
        `INSERT IGNORE INTO moves (name, type_id, power, accuracy, pp, damage_class, effect, effect_chance, description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          moveData.name,
          typeId,
          moveData.power || null,
          moveData.accuracy || null,
          moveData.pp || null,
          damageClass,
          moveData.effect_entries.length > 0 ? moveData.effect_entries[0].effect : '',
          moveData.effect_chance || null,
          description
        ]
      );
      
      counter++;
      if (counter % 50 === 0) {
        console.log(`Додано ${counter} ходів...`);
      }
    } catch (error) {
      console.error(`Помилка при додаванні ходу "${move.name}":`, error);
    }
  }
  
  console.log(`Імпорт ходів завершено. Всього додано ${counter} ходів.`);
}

// Функція для імпорту покемонів та пов'язаних даних
async function importPokemons(connection) {
  console.log('Імпортуємо дані про покемонів...');
  
  // Отримуємо список всіх покемонів
  // Зазвичай є близько 1000+ покемонів, тому встановлюємо високе значення ліміту
  const response = await axios.get(`${API_BASE_URL}/pokemon?limit=2000`);
  const pokemons = response.data.results;
  
  let counter = 0;
  for (const pokemon of pokemons) {
    try {
      // Отримуємо детальну інформацію про покемона
      const pokemonDetails = await axios.get(pokemon.url);
      const pokemonData = pokemonDetails.data;
      
      // Отримуємо додаткову інформацію (види, категорії і т.д.)
      const speciesDetails = await axios.get(pokemonData.species.url);
      const speciesData = speciesDetails.data;
      
      // Визначаємо, чи є покемон легендарним або міфічним
      const isLegendary = speciesData.is_legendary || false;
      const isMythical = speciesData.is_mythical || false;
      const generation = speciesData.generation ? 
        parseInt(speciesData.generation.url.split('/').filter(Boolean).pop()) : null;
      
      // Знаходимо опис англійською мовою
      let description = '';
      for (const flavorText of speciesData.flavor_text_entries) {
        if (flavorText.language.name === 'en') {
          description = flavorText.flavor_text.replace(/\n/g, ' ').replace(/\f/g, ' ');
          break;
        }
      }
      
      // Знаходимо категорію (жанр) покемона
      let category = '';
      for (const genus of speciesData.genera) {
        if (genus.language.name === 'en') {
          category = genus.genus;
          break;
        }
      }
      
      // Типи покемона
      const primaryType = pokemonData.types.find(t => t.slot === 1);
      const secondaryType = pokemonData.types.find(t => t.slot === 2);
      
      // Знаходимо ID типів
      let primaryTypeId = null;
      let secondaryTypeId = null;
      
      if (primaryType) {
        const [primaryRow] = await connection.execute(
          'SELECT id FROM types WHERE name = ?',
          [primaryType.type.name]
        );
        if (primaryRow.length > 0) {
          primaryTypeId = primaryRow[0].id;
        }
      }
      
      if (secondaryType) {
        const [secondaryRow] = await connection.execute(
          'SELECT id FROM types WHERE name = ?',
          [secondaryType.type.name]
        );
        if (secondaryRow.length > 0) {
          secondaryTypeId = secondaryRow[0].id;
        }
      }
      
      // Вставляємо покемона в базу даних
      const [result] = await connection.execute(
        `INSERT IGNORE INTO pokemon 
         (pokedex_number, name, primary_type_id, secondary_type_id, description, 
          image_url, height, weight, category, generation, is_legendary, is_mythical)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          pokemonData.id,
          pokemonData.name,
          primaryTypeId,
          secondaryTypeId,
          description,
          pokemonData.sprites.other['official-artwork'].front_default || pokemonData.sprites.front_default,
          pokemonData.height / 10, // API дає значення в дециметрах, конвертуємо в метри
          pokemonData.weight / 10, // API дає значення в гектограмах, конвертуємо в кілограми
          category,
          generation,
          isLegendary,
          isMythical
        ]
      );
      
      // Отримуємо ID вставленого покемона
      const [pokemonRow] = await connection.execute(
        'SELECT id FROM pokemon WHERE pokedex_number = ?',
        [pokemonData.id]
      );
      
      if (pokemonRow.length === 0) continue;
      const pokemonId = pokemonRow[0].id;
      
      // Додаємо статистики
      await importPokemonStats(connection, pokemonId, pokemonData.stats);
      
      // Додаємо здібності
      await importPokemonAbilities(connection, pokemonId, pokemonData.abilities);
      
      // Додаємо ходи
      await importPokemonMoves(connection, pokemonId, pokemonData.moves);
      
      counter++;
      if (counter % 20 === 0) {
        console.log(`Додано ${counter} покемонів...`);
      }
    } catch (error) {
      console.error(`Помилка при додаванні покемона "${pokemon.name}":`, error);
    }
  }
  
  console.log(`Імпорт покемонів завершено. Всього додано ${counter} покемонів.`);
}

// Функція для імпорту статистик покемона
async function importPokemonStats(connection, pokemonId, stats) {
  try {
    // Мапінг статистик з API до наших полів в базі даних
    const statsMap = {
      'hp': 'hp',
      'attack': 'attack',
      'defense': 'defense',
      'special-attack': 'special_attack',
      'special-defense': 'special_defense',
      'speed': 'speed'
    };
    
    // Створюємо об'єкт зі статистиками
    const statsData = {
      hp: 0,
      attack: 0,
      defense: 0,
      special_attack: 0,
      special_defense: 0,
      speed: 0
    };
    
    // Заповнюємо значення статистик
    for (const stat of stats) {
      const statName = stat.stat.name;
      if (statsMap[statName]) {
        statsData[statsMap[statName]] = stat.base_stat;
      }
    }
    
    // Вставляємо статистики в базу даних
    await connection.execute(
      `INSERT IGNORE INTO pokemon_stats 
       (pokemon_id, hp, attack, defense, special_attack, special_defense, speed)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        pokemonId,
        statsData.hp,
        statsData.attack,
        statsData.defense,
        statsData.special_attack,
        statsData.special_defense,
        statsData.speed
      ]
    );
  } catch (error) {
    console.error(`Помилка при додаванні статистик для покемона ID=${pokemonId}:`, error);
  }
}

// Функція для імпорту здібностей покемона
async function importPokemonAbilities(connection, pokemonId, abilities) {
  for (const ability of abilities) {
    try {
      // Знаходимо ID здібності
      const [abilityRow] = await connection.execute(
        'SELECT id FROM abilities WHERE name = ?',
        [ability.ability.name]
      );
      
      if (abilityRow.length === 0) continue;
      const abilityId = abilityRow[0].id;
      
      // Вставляємо зв'язок покемона і здібності
      await connection.execute(
        `INSERT IGNORE INTO pokemon_abilities
         (pokemon_id, ability_id, is_hidden)
         VALUES (?, ?, ?)`,
        [pokemonId, abilityId, ability.is_hidden]
      );
    } catch (error) {
      console.error(`Помилка при додаванні здібності для покемона ID=${pokemonId}:`, error);
    }
  }
}

// Функція для імпорту ходів покемона
async function importPokemonMoves(connection, pokemonId, moves) {
  // Отримуємо ID методів вивчення
  const [methodRows] = await connection.execute('SELECT id, name FROM learn_methods');
  const methodMap = {};
  for (const row of methodRows) {
    methodMap[row.name] = row.id;
  }
  
  for (const moveData of moves) {
    try {
      // Знаходимо ID ходу
      const [moveRow] = await connection.execute(
        'SELECT id FROM moves WHERE name = ?',
        [moveData.move.name]
      );
      
      if (moveRow.length === 0) continue;
      const moveId = moveRow[0].id;
      
      // Обробляємо кожен метод вивчення
      for (const versionGroup of moveData.version_group_details) {
        // Визначаємо метод вивчення
        let methodName = 'level-up';
        let levelLearned = null;
        
        if (versionGroup.move_learn_method.name === 'level-up') {
          methodName = 'level-up';
          levelLearned = versionGroup.level_learned_at;
        } else if (versionGroup.move_learn_method.name === 'machine') {
          methodName = 'machine';
        } else if (versionGroup.move_learn_method.name === 'egg') {
          methodName = 'egg';
        } else if (versionGroup.move_learn_method.name === 'tutor') {
          methodName = 'tutor';
        } else {
          methodName = 'event';
        }
        
        // Визначаємо покоління
        const generation = parseInt(versionGroup.version_group.url.split('/').filter(Boolean).pop()) || null;
        
        // Вставляємо зв'язок покемона і ходу
        await connection.execute(
          `INSERT IGNORE INTO pokemon_moves
           (pokemon_id, move_id, learn_method_id, level_learned, generation_id)
           VALUES (?, ?, ?, ?, ?)`,
          [pokemonId, moveId, methodMap[methodName], levelLearned, generation]
        );
      }
    } catch (error) {
      console.error(`Помилка при додаванні ходу для покемона ID=${pokemonId}:`, error);
    }
  }
}

// Функція для імпорту еволюцій
async function importEvolutions(connection) {
  console.log('Імпортуємо дані про еволюції покемонів...');
  
  // Отримуємо список всіх ланцюжків еволюції
  const response = await axios.get(`${API_BASE_URL}/evolution-chain?limit=1000`);
  const chains = response.data.results;
  
  for (const chain of chains) {
    try {
      // Отримуємо детальну інформацію про ланцюжок еволюції
      const chainDetails = await axios.get(chain.url);
      const chainData = chainDetails.data;
      
      // Створюємо запис про ланцюжок еволюції
      const [result] = await connection.execute(
        'INSERT INTO evolution_chains (identifier) VALUES (?)',
        [chainData.id.toString()]
      );
      
      const chainId = result.insertId;
      
      // Рекурсивно обробляємо всі еволюції в ланцюжку
      await processEvolutionChain(connection, chainId, chainData.chain, null, 1);
    } catch (error) {
      console.error(`Помилка при обробці ланцюжка еволюції:`, error);
    }
  }
  
  console.log('Імпорт даних про еволюції завершено');
}

// Рекурсивна функція для обробки ланцюжка еволюції
async function processEvolutionChain(connection, chainId, evolutionData, basePokemonId, order) {
  try {
    // Отримуємо дані про поточний вид
    const speciesUrl = evolutionData.species.url;
    const speciesId = parseInt(speciesUrl.split('/').filter(Boolean).pop());
    
    // Знаходимо ID покемона
    const [pokemonRow] = await connection.execute(
      'SELECT id FROM pokemon WHERE pokedex_number = ?',
      [speciesId]
    );
    
    if (pokemonRow.length === 0) return;
    const currentPokemonId = pokemonRow[0].id;
    
    // Якщо є базовий покемон, створюємо запис про еволюцію
    if (basePokemonId) {
      // Отримуємо дані про метод еволюції
      const evolutionDetails = evolutionData.evolution_details[0] || {};
      
      let levelRequired = null;
      let itemRequired = null;
      let evolutionMethod = null;
      let evolutionCondition = null;
      
      // Визначаємо метод еволюції
      if (evolutionDetails.min_level) {
        levelRequired = evolutionDetails.min_level;
        evolutionMethod = 'level-up';
      } else if (evolutionDetails.item) {
        itemRequired = evolutionDetails.item.name;
        evolutionMethod = 'item';
      } else if (evolutionDetails.trigger) {
        evolutionMethod = evolutionDetails.trigger.name;
      }
      
      // Додаткові умови
      const conditions = [];
      if (evolutionDetails.time_of_day) conditions.push(`time: ${evolutionDetails.time_of_day}`);
      if (evolutionDetails.min_happiness) conditions.push(`happiness: ${evolutionDetails.min_happiness}`);
      if (evolutionDetails.min_beauty) conditions.push(`beauty: ${evolutionDetails.min_beauty}`);
      if (evolutionDetails.min_affection) conditions.push(`affection: ${evolutionDetails.min_affection}`);
      if (evolutionDetails.needs_overworld_rain) conditions.push('needs rain');
      if (evolutionDetails.turn_upside_down) conditions.push('turn upside down');
      
      evolutionCondition = conditions.length > 0 ? conditions.join(', ') : null;
      
      // Додаємо запис про еволюцію
      await connection.execute(
        `INSERT IGNORE INTO evolutions
         (evolution_chain_id, base_pokemon_id, evolved_pokemon_id, level_required, 
          item_required, evolution_method, evolution_condition, order_in_chain)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          chainId,
          basePokemonId,
          currentPokemonId,
          levelRequired,
          itemRequired,
          evolutionMethod,
          evolutionCondition,
          order
        ]
      );
    }
    
    // Рекурсивно обробляємо наступні еволюції
    for (const evolution of evolutionData.evolves_to) {
      await processEvolutionChain(connection, chainId, evolution, currentPokemonId, order + 1);
    }
  } catch (error) {
    console.error('Помилка при обробці еволюції:', error);
  }
}

// Запускаємо процес імпорту
importPokemonData()
  .then(() => console.log('Процес імпорту завершено'))
  .catch(err => console.error('Помилка в основному процесі:', err));