// pokemon.js - Логіка роботи з API покемонів

// Поточний покемон
let currentPokemon = null;
let currentPokedexNumber = 25; // Початковий покемон (Pikachu)

// Функція для ініціалізації покемона
function initPokemon() {
    // Завантажуємо початкового покемона при завантаженні сторінки
    fetchPokemon(currentPokedexNumber);
    
    // Налаштовуємо обробники подій для кнопок навігації
    const upButton = document.querySelector('.up');
    const downButton = document.querySelector('.down');
    
    upButton.addEventListener('click', () => {
        navigatePokemon('up');
    });
    
    downButton.addEventListener('click', () => {
        navigatePokemon('down');
    });
}

// Функція для завантаження даних покемона
function fetchPokemon(pokedexNumber) {
    // Додаємо клас завантаження
    document.body.classList.add('loading');
    
    // Запит до API
    fetch(`get_pokemon.php?action=get&number=${pokedexNumber}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPokemon = data.data;
                currentPokedexNumber = pokedexNumber;
                updatePokemonUI(currentPokemon);
            } else {
                console.error('Помилка отримання покемона:', data.message);
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Помилка запиту:', error);
            showError('Помилка зв\'язку з сервером');
        })
        .finally(() => {
            // Видаляємо клас завантаження
            document.body.classList.remove('loading');
        });
}

// Функція для навігації вгору/вниз
function navigatePokemon(direction) {
    // Додаємо клас завантаження
    document.body.classList.add('loading');
    
    // Запит до API для отримання наступного покемона
    fetch(`get_pokemon.php?action=navigate&number=${currentPokedexNumber}&direction=${direction}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentPokemon = data.data;
                currentPokedexNumber = currentPokemon.pokedex_number;
                updatePokemonUI(currentPokemon);
            } else {
                console.error('Помилка навігації:', data.message);
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Помилка запиту:', error);
            showError('Помилка зв\'язку з сервером');
        })
        .finally(() => {
            // Видаляємо клас завантаження
            document.body.classList.remove('loading');
        });
}

// Функція для оновлення UI з даними покемона
function updatePokemonUI(pokemon) {
    // Оновлюємо верхню панель
    document.querySelector('.topbar h2').textContent = pokemon.name;
    document.querySelector('.topbar p').textContent = `#${pokemon.pokedex_number}`;
    
    // Оновлюємо типи
    const type1Element = document.querySelector('.type1');
    type1Element.textContent = pokemon.primary_type;
    type1Element.className = `type1 type-${pokemon.primary_type.toLowerCase()}`;
    
    const type2Element = document.querySelector('.type2');
    if (pokemon.secondary_type) {
        type2Element.textContent = pokemon.secondary_type;
        type2Element.className = `type2 type-${pokemon.secondary_type.toLowerCase()}`;
        type2Element.style.display = 'block';
    } else {
        type2Element.style.display = 'none';
    }
    
    // Оновлюємо зображення (на всіх екранах)
    const pokemonImages = document.querySelectorAll('.screen img.pokemon, .screen img[alt="Pikachu"]');
    pokemonImages.forEach(img => {
        img.src = pokemon.image_url || `img/${pokemon.name.toLowerCase()}.png`;
        img.alt = pokemon.name;
    });
    
    // Екран 1: Опис
    const speciesElements = document.querySelectorAll('.species');
    speciesElements.forEach(element => {
        element.innerHTML = `Species:<br>${pokemon.category}`;
    });
    
    const descriptionElements = document.querySelectorAll('h5');
    descriptionElements.forEach(element => {
        element.textContent = pokemon.description || 'No description available.';
    });
    
    // Екран 2: Статистика
    if (pokemon.stats) {
        document.querySelector('.second-screen div:nth-child(2) p:nth-child(1)').innerHTML = `<strong>HP:</strong> ${pokemon.stats.hp}`;
        document.querySelector('.second-screen div:nth-child(2) p:nth-child(2)').innerHTML = `<strong>AT:</strong> ${pokemon.stats.attack}`;
        document.querySelector('.second-screen div:nth-child(3) p:nth-child(1)').innerHTML = `<strong>DEF:</strong> ${pokemon.stats.defense}`;
        document.querySelector('.second-screen div:nth-child(3) p:nth-child(2)').innerHTML = `<strong>SDEF:</strong> ${pokemon.stats.special_defense}`;
        document.querySelector('.second-screen div:nth-child(4) p:nth-child(1)').innerHTML = `<strong>SAT:</strong> ${pokemon.stats.special_attack}`;
        document.querySelector('.second-screen div:nth-child(4) p:nth-child(2)').innerHTML = `<strong>SP:</strong> ${pokemon.stats.speed}`;
    }
    
    // Екран 3: Розмір і вага
    document.querySelector('.height').innerHTML = `<span>}</span>${pokemon.height} m`;
    document.querySelector('.weight').textContent = `${pokemon.weight} kg`;
    
    // Екран 4: Еволюції
    updateEvolutionUI(pokemon);
    
    // Екран 5: Здібності
    updateAbilitiesUI(pokemon);
    
    // Екран 6: Ходи
    updateMovesUI(pokemon);
}

// Функція для оновлення блоку еволюцій
function updateEvolutionUI(pokemon) {
    const evolutionContainer = document.querySelector('.evolution');
    const evolutionText = document.querySelector('.evolution-text');
    
    // Очищаємо контейнери
    evolutionContainer.innerHTML = '';
    evolutionText.innerHTML = '';
    
    if (pokemon.evolutions && pokemon.evolutions.length > 0) {
        // Сортуємо еволюції за порядком
        const sortedEvolutions = [...pokemon.evolutions].sort((a, b) => a.order_in_chain - b.order_in_chain);
        
        // Знаходимо початкового покемона (найменший pokedex_number)
        const startingPokemon = sortedEvolutions.reduce((prev, current) => {
            return prev.base_pokedex_number < current.base_pokedex_number ? prev : current;
        });
        
        // Створюємо унікальний список всіх покемонів у ланцюжку
        const uniquePokemonIds = new Set();
        sortedEvolutions.forEach(evo => {
            uniquePokemonIds.add(evo.base_pokedex_number);
            uniquePokemonIds.add(evo.evolved_pokedex_number);
        });
        
        // Перетворюємо на відсортований масив
        const allPokemonInChain = Array.from(uniquePokemonIds).sort();
        
        // Створюємо HTML для ланцюжка еволюції
        allPokemonInChain.forEach((pokedexNumber, index) => {
            // Додаємо зображення покемона
            const pokemonImg = document.createElement('img');
            pokemonImg.className = 'pokemon';
            
            // Знаходимо ім'я покемона
            const pokemonName = sortedEvolutions.find(e => 
                e.base_pokedex_number === pokedexNumber
            )?.base_name || sortedEvolutions.find(e => 
                e.evolved_pokedex_number === pokedexNumber
            )?.evolved_name;
            
            pokemonImg.src = `img/${pokemonName?.toLowerCase() || 'pokemon'}.png`;
            pokemonImg.alt = pokemonName || `Pokemon ${pokedexNumber}`;
            
            evolutionContainer.appendChild(pokemonImg);
            
            // Додаємо стрілку, якщо це не останній покемон
            if (index < allPokemonInChain.length - 1) {
                const arrowImg = document.createElement('img');
                arrowImg.src = 'img/flecha.svg';
                arrowImg.alt = '>';
                evolutionContainer.appendChild(arrowImg);
            }
        });
        
        // Створюємо текстовий опис еволюції
        const startText = `<strong>Starting Pokémon:</strong> ${startingPokemon.base_name} (ID=${startingPokemon.base_pokedex_number})`;
        
        let stepsText = '';
        sortedEvolutions.forEach((evo, index) => {
            stepsText += `<br><br><strong>[step ${index + 1}]</strong> ${evo.base_name} (ID=${evo.base_pokedex_number}) evolves into ${evo.evolved_name} (ID=${evo.evolved_pokedex_number})`;
            
            // Додаємо метод еволюції, якщо він є
            if (evo.evolution_method) {
                if (evo.item_required) {
                    stepsText += ` using a ${evo.item_required} via ${evo.evolution_method} method`;
                } else if (evo.level_required) {
                    stepsText += ` at level ${evo.level_required} via ${evo.evolution_method} method`;
                } else {
                    stepsText += ` via ${evo.evolution_method} method`;
                }
            }
            
            // Додаємо умову еволюції, якщо вона є
            if (evo.evolution_condition) {
                stepsText += ` (${evo.evolution_condition})`;
            }
        });
        
        evolutionText.innerHTML = startText + stepsText;
        
    } else {
        // Якщо немає еволюцій
        evolutionText.innerHTML = `<strong>This Pokémon has no evolutions</strong>`;
    }
}

// Функція для оновлення блоку здібностей
function updateAbilitiesUI(pokemon) {
    const abilitiesContainer = document.querySelector('.fifth-screen');
    
    // Очищаємо контейнер
    abilitiesContainer.innerHTML = '';
    
    if (pokemon.abilities && pokemon.abilities.length > 0) {
        // Додаємо кожну здібність
        pokemon.abilities.forEach(ability => {
            const abilityDiv = document.createElement('div');
            abilityDiv.className = 'habilidad';
            
            const hidden = ability.is_hidden ? ' (Hidden)' : '';
            abilityDiv.innerHTML = `
                <h1>${ability.ability_name}${hidden} <span><img src="img/show.png" alt="show"></span></h1>
                <p>${ability.ability_description || 'No description available.'}</p>
            `;
            
            abilitiesContainer.appendChild(abilityDiv);
        });
    } else {
        // Якщо немає здібностей
        const noAbilitiesDiv = document.createElement('div');
        noAbilitiesDiv.className = 'habilidad';
        noAbilitiesDiv.innerHTML = `
            <h1>No abilities</h1>
            <p>This Pokémon has no known abilities.</p>
        `;
        
        abilitiesContainer.appendChild(noAbilitiesDiv);
    }
}

// Функція для оновлення блоку ходів
function updateMovesUI(pokemon) {
    const movesContainer = document.querySelector('.sixth-screen');
    
    // Очищаємо контейнер
    movesContainer.innerHTML = '';
    
    if (pokemon.moves && pokemon.moves.length > 0) {
        // Додаємо кожен хід (обмежуємо до 2 для зручності відображення)
        pokemon.moves.slice(0, 2).forEach(move => {
            const moveDiv = document.createElement('div');
            moveDiv.className = 'moves';
            
            moveDiv.innerHTML = `
                <div class="general">
                    <h1>${move.move_name}</h1>
                    <p>${move.description || 'No description available.'}</p>
                </div>
                <div class="detalles">
                    <p>${move.move_type}</p>
                    <p><strong>PW:</strong> ${move.power || '-'}</p>
                    <p><strong>AC:</strong> ${move.accuracy || '-'}</p>
                    <p><strong>PP:</strong> ${move.pp || '-'}</p>
                </div>
            `;
            
            movesContainer.appendChild(moveDiv);
        });
    } else {
        // Якщо немає ходів
        const noMovesDiv = document.createElement('div');
        noMovesDiv.className = 'moves';
        noMovesDiv.innerHTML = `
            <div class="general">
                <h1>No moves</h1>
                <p>This Pokémon has no known moves.</p>
            </div>
        `;
        
        movesContainer.appendChild(noMovesDiv);
    }
}

// Функція для відображення помилок
function showError(message) {
    // Можна реалізувати відображення помилок (наприклад, через спливаюче вікно)
    alert('Error: ' + message);
}

// Запускаємо ініціалізацію після завантаження сторінки
document.addEventListener('DOMContentLoaded', initPokemon);