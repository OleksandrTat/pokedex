// js/pokedex.js

document.addEventListener('DOMContentLoaded', () => {
    let currentNumber = parseInt(
      document.getElementById('poke-number').textContent.replace('#',''),
      10
    );
    let currentPokemonId = initialPokemon.pokedex_number || 25;

    const elems = {
      name:        document.getElementById('pokemon-name'),
      number:      document.getElementById('pokemon-number'),
      type1:       document.getElementById('type1'),
      type2:       document.getElementById('type2'),
      img1:        document.getElementById('poke-img'),
    //   img2:        document.getElementById('poke-img-2'),
      species:     document.getElementById('species'),
      desc:        document.getElementById('description'),
      stats: {
        hp:  document.getElementById('stat-hp'),
        at:  document.getElementById('stat-at'),
        def: document.getElementById('stat-def'),
        sat: document.getElementById('stat-sat'),
        sdef:document.getElementById('stat-sdef'),
        sp:  document.getElementById('stat-sp'),
      },
      height: document.getElementById('height'),
      weight: document.getElementById('weight'),
    };
  
    function loadPokemon(num) {
      fetch(`index.php?ajax=1&id=${num}`)
        .then(res => {
          if (!res.ok) throw new Error('Pokémon не знайдено');
          return res.json();
        })
        .then(data => {
          currentNumber = data.pokedex_number;
          elems.name.textContent        = data.name;
          elems.number.textContent      = `#${data.pokedex_number}`;
          elems.type1.textContent       = data.primary_type;
          elems.type2.textContent       = data.secondary_type || '';
          elems.img1.src                = data.image_url;
          elems.img1.alt                = data.name;
          elems.img2.src                = data.image_url;
          elems.img2.alt                = data.name;
          elems.species.innerHTML       = `Species:<br>${data.species}`;
          elems.desc.textContent        = data.description;
          elems.stats.hp.textContent    = data.hp;
          elems.stats.at.textContent    = data.attack;
          elems.stats.def.textContent   = data.defense;
          elems.stats.sat.textContent   = data.special_attack;
          elems.stats.sdef.textContent  = data.special_defense;
          elems.stats.sp.textContent    = data.speed;
          elems.height.textContent      = `${data.height} m`;
          elems.weight.textContent      = `${data.weight} kg`;
        })
        .catch(err => console.error(err));
    }
  
    document.querySelector('.up').addEventListener('click', () => {
      const prev = currentNumber > 1 ? currentNumber - 1 : 1;
      if (prev !== currentNumber) loadPokemon(prev);
    });
  
    document.querySelector('.down').addEventListener('click', () => {
      loadPokemon(currentNumber + 1);
    });
  
    // Оновлюємо інтерфейс початковими даними
    updatePokemonInfo(initialPokemon);
  });
  // pokedex.js - Основний файл для керування даними покемонів
    let currentPokemonId = initialPokemon.pokedex_number || 25;
    
    // Оновлюємо інтерфейс початковими даними
    updatePokemonInfo(initialPokemon);
    
    // Обробники подій для кнопок "up" і "down"
    const upButton = document.querySelector('.up');
    const downButton = document.querySelector('.down');
    
    upButton.addEventListener('click', () => {
        loadPokemon('up');
    });
    
    downButton.addEventListener('click', () => {
        loadPokemon('down');
    });
    
    // Функція завантаження даних про покемона з сервера
    function loadPokemon(direction) {
        // Показуємо індикатор завантаження або анімацію (опціонально)
        // ...
        
        // Відправляємо AJAX-запит на сервер
        const formData = new FormData();
        formData.append('action', 'getPokemon');
        formData.append('direction', direction);
        formData.append('currentId', currentPokemonId);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            // Оновлюємо поточний ID покемона
            currentPokemonId = data.pokedex_number;
            
            // Оновлюємо інтерфейс
            updatePokemonInfo(data);
        })
        .catch(error => {
            console.error('Network error:', error);
        });
    }
    
    // Функція оновлення інтерфейсу даними про покемона
    function updatePokemonInfo(pokemon) {
        // Оновлюємо основну інформацію
        document.getElementById('pokemon-name').textContent = pokemon.name;
        document.getElementById('pokemon-number').textContent = '#' + pokemon.pokedex_number;
        
        // Оновлюємо типи
        const type1Element = document.getElementById('type1');
        const type2Element = document.getElementById('type2');
        
        type1Element.textContent = pokemon.primary_type;
        
        if (pokemon.secondary_type) {
            type2Element.textContent = pokemon.secondary_type;
            type2Element.style.display = 'flex';
        } else {
            type2Element.style.display = 'none';
        }
        
        // Оновлюємо опис
        const description = pokemon.description || 'No description available';
        document.getElementById('description').textContent = description;
        document.getElementById('description-first').textContent = description;
        
        // Оновлюємо зображення на всіх екранах
        const imageUrl = pokemon.image_url || `img/${pokemon.name.toLowerCase()}.png`;
        document.getElementById('pokemon-image').src = imageUrl;
        document.getElementById('pokemon-image-stats').src = imageUrl;
        document.getElementById('pokemon-image-size').src = imageUrl;
        
        // Оновлюємо інформацію про вид
        const species = pokemon.species || 'Unknown Species';
        document.getElementById('species').innerHTML = `Species:<br>${species}`;
        document.getElementById('species-first').innerHTML = `Species:<br>${species}`;
        
        // Оновлюємо статистику
        document.getElementById('stat-hp').textContent = pokemon.hp || '0';
        document.getElementById('stat-attack').textContent = pokemon.attack || '0';
        document.getElementById('stat-defense').textContent = pokemon.defense || '0';
        document.getElementById('stat-special-attack').textContent = pokemon.special_attack || '0';
        document.getElementById('stat-special-defense').textContent = pokemon.special_defense || '0';
        document.getElementById('stat-speed').textContent = pokemon.speed || '0';
        
        // Оновлюємо фізичні параметри
        document.getElementById('height').textContent = pokemon.height || '0.0';
        document.getElementById('weight').textContent = pokemon.weight || '0.0';
        
        // Оновлюємо еволюційний ланцюг
        updateEvolutionChain(pokemon);
        
        // Оновлюємо здібності
        updateAbilities(pokemon.abilities || []);
        
        // Оновлюємо ходи
        updateMoves(pokemon.moves || []);
    }
    
    // Функція оновлення еволюційного ланцюга
    function updateEvolutionChain(pokemon) {
        const evoChain = pokemon.evolution_chain || pokemon.evolution || [];
        
        // За замовчуванням - еволюційний ланцюг Пікачу
        let firstPokemon = { name: 'Pichu', pokedex_number: 172, image_url: 'img/pichu.png' };
        let secondPokemon = { name: 'Pikachu', pokedex_number: 25, image_url: 'img/pikachu.png' };
        let thirdPokemon = { name: 'Raichu', pokedex_number: 26, image_url: 'img/raichu.png' };
        
        let evoText = `
            <strong>Starting Pokémon:</strong> Pichu (ID=172) <br><br>
            <strong>[step 1]</strong> Pichu (ID=172) evolves into Pikachu (ID=25) via friendship method <br><br>
            <strong>[step 2]</strong> Pikachu (ID=25) evolves into Raichu (ID=26) using a THUNDER STONE via item method
        `;
        
        if (evoChain.length > 0) {
            // Складаємо текст еволюції на основі даних з сервера
            const evoTextParts = [];
            
            // Знаходимо перший покемон в ланцюгу еволюції
            const firstEvo = evoChain.find(evo => evo.order_in_chain === 1);
            if (firstEvo) {
                firstPokemon = { 
                    name: firstEvo.base_name, 
                    pokedex_number: firstEvo.base_number,
                    image_url: firstEvo.base_image || `img/${firstEvo.base_name.toLowerCase()}.png`
                };
                
                secondPokemon = { 
                    name: firstEvo.evolved_name, 
                    pokedex_number: firstEvo.evolved_number,
                    image_url: firstEvo.evolved_image || `img/${firstEvo.evolved_name.toLowerCase()}.png`
                };
                
                evoTextParts.push(`<strong>Starting Pokémon:</strong> ${firstPokemon.name} (ID=${firstPokemon.pokedex_number}) <br><br>`);
                evoTextParts.push(`<strong>[step 1]</strong> ${firstPokemon.name} (ID=${firstPokemon.pokedex_number}) evolves into ${secondPokemon.name} (ID=${secondPokemon.pokedex_number}) via ${firstEvo.evolution_method} method`);
                
                if (firstEvo.item_required) {
                    evoTextParts[evoTextParts.length-1] += ` using a ${firstEvo.item_required}`;
                }
                evoTextParts[evoTextParts.length-1] += ' <br><br>';
            }
            
            // Знаходимо другу еволюцію, якщо вона є
            const secondEvo = evoChain.find(evo => evo.order_in_chain === 2);
            if (secondEvo) {
                thirdPokemon = { 
                    name: secondEvo.evolved_name, 
                    pokedex_number: secondEvo.evolved_number,
                    image_url: secondEvo.evolved_image || `img/${secondEvo.evolved_name.toLowerCase()}.png`
                };
                
                evoTextParts.push(`<strong>[step 2]</strong> ${secondPokemon.name} (ID=${secondPokemon.pokedex_number}) evolves into ${thirdPokemon.name} (ID=${thirdPokemon.pokedex_number}) via ${secondEvo.evolution_method} method`);
                
                if (secondEvo.item_required) {
                    evoTextParts[evoTextParts.length-1] += ` using a ${secondEvo.item_required}`;
                }
            }
            
            if (evoTextParts.length > 0) {
                evoText = evoTextParts.join('');
            }
        }
        
        // Оновлюємо зображення еволюцій
        document.getElementById('evolution-first').src = firstPokemon.image_url;
        document.getElementById('evolution-first').alt = firstPokemon.name;
        
        document.getElementById('evolution-second').src = secondPokemon.image_url;
        document.getElementById('evolution-second').alt = secondPokemon.name;
        
        document.getElementById('evolution-third').src = thirdPokemon.image_url;
        document.getElementById('evolution-third').alt = thirdPokemon.name;
        
        // Оновлюємо текст еволюції
        document.getElementById('evolution-text').innerHTML = evoText;
    }
    
    // Функція оновлення здібностей
    function updateAbilities(abilities) {
        const container = document.getElementById('abilities-container');
        container.innerHTML = '';
        
        // Якщо немає здібностей, додаємо одну за замовчуванням
        if (abilities.length === 0) {
            abilities.push({ name: 'Unknown', description: 'No ability information available' });
        }
        
        // Додаємо не більше 3 здібностей
        const maxAbilities = Math.min(abilities.length, 3);
        
        for (let i = 0; i < maxAbilities; i++) {
            const ability = abilities[i];
            
            const abilityHtml = `
                <div class="habilidad">
                    <h1>${ability.name} <span><img src="img/show.png" alt=""></span></h1>
                    <p>${ability.description || 'No description available'}</p>
                </div>
            `;
            
            container.innerHTML += abilityHtml;
        }
    }
    
    // Функція оновлення ходів
    function updateMoves(moves) {
        const container = document.getElementById('moves-container');
        container.innerHTML = '';
        
        // Якщо немає ходів, додаємо один за замовчуванням
        if (moves.length === 0) {
            moves.push({ 
                name: 'Unknown', 
                description: 'No move information available',
                type: 'Normal',
                pw: 0,
                ac: 0,
                pp: 0
            });
        }
        
        // Додаємо не більше 2 ходів
        const maxMoves = Math.min(moves.length, 2);
        
        for (let i = 0; i < maxMoves; i++) {
            const move = moves[i];
            
            const moveHtml = `
                <div class="moves">
                    <div class="general">
                        <h1>${move.name}</h1>
                        <p>${move.description || 'No description available'}</p>
                    </div>
                    <div class="detalles">
                        <p>${move.type || 'Normal'}</p>
                        <p><strong>PW:</strong> ${move.pw || 0}</p>
                        <p><strong>AC:</strong> ${move.ac || 0}</p>
                        <p><strong>PP:</strong> ${move.pp || 0}</p>
                    </div>
                </div>
            `;
            
            container.innerHTML += moveHtml;
        }
    }