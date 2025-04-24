// main.js

document.addEventListener('DOMContentLoaded', () => {
  // Configuración inicial
  const MAX_POKEMON = 898;
  let currentPokemon = 1;
  let displayMode = 'basic'; // Modos: 'basic', 'stats', 'moves'
  let pokedexPower = true; // Estado de encendido/apagado
  let language = 'es'; // Idioma inicial: español (es) o inglés (en)
  
  // Referencias a elementos del DOM
  const screen = document.querySelector('.pantalla');
  
  // Crear elementos de la interfaz
  function setupInterface() {
    // Limpiar pantalla
    screen.innerHTML = '';
    
    // Crear contenedor principal
    const container = document.createElement('div');
    container.className = 'pokemon-display';
    container.innerHTML = `
      <div class="pokemon-header">
        <div id="pokemon-id"></div>
        <div id="pokemon-name"></div>
      </div>
      <div class="pokemon-content">
        <div class="pokemon-image-container">
          <img id="pokemon-image" src="" alt="Pokemon">
          <div class="loading-spinner"></div>
        </div>
        <div class="pokemon-info">
          <div id="pokemon-types" class="pokemon-types"></div>
          <div id="pokemon-details" class="pokemon-details"></div>
        </div>
      </div>
      <div id="status-message" class="status-message"></div>
      <div id="search-interface" class="search-interface">
        <div class="search-box">
          <h3>${language === 'es' ? 'Buscar Pokémon' : 'Search Pokemon'}</h3>
          <div class="input-container">
            <input type="number" id="pokemon-id-input" min="1" max="${MAX_POKEMON}" placeholder="ID (1-${MAX_POKEMON})">
          </div>
          <div class="button-container">
            <button id="search-confirm">${language === 'es' ? 'Buscar' : 'Search'}</button>
            <button id="search-cancel">${language === 'es' ? 'Cancelar' : 'Cancel'}</button>
          </div>
        </div>
      </div>
    `;
    
    screen.appendChild(container);
    
    // Añadir estilos
    const styles = document.createElement('style');
    styles.textContent = `
      .pokemon-display {
        height: 100%;
        display: flex;
        flex-direction: column;
        color: #111;
        font-family: monospace;
        overflow: hidden;
        position: relative;
      }
      
      .pokemon-header {
        display: flex;
        justify-content: space-between;
        padding: 5px;
        background: rgba(0,0,0,0.1);
        font-weight: bold;
      }
      
      .pokemon-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        overflow-y: auto;
      }
      
      .pokemon-image-container {
        position: relative;
        height: 50%;
        width: 90%;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      
      #pokemon-image {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
      }
      
      .pokemon-info {
        width: 100%;
        margin-top: 10px;
      }
      
      .pokemon-types {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 8px;
      }
      
      .type-badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        color: white;
        text-shadow: 0 1px 1px rgba(0,0,0,0.5);
      }
      
      .pokemon-details {
        font-size: 12px;
      }
      
      .stats-bar {
        height: 8px;
        background: #ddd;
        margin-top: 2px;
        position: relative;
        border-radius: 4px;
        overflow: hidden;
      }
      
      .stats-bar-fill {
        height: 100%;
        background: #4caf50;
        width: 0%;
        transition: width 0.5s;
      }
      
      .move-item {
        background: rgba(0,0,0,0.05);
        margin: 3px 0;
        padding: 3px;
        border-radius: 4px;
      }
      
      .status-message {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
      }
      
      .loading-spinner {
        position: absolute;
        width: 30px;
        height: 30px;
        border: 4px solid rgba(0,0,0,0.1);
        border-radius: 50%;
        border-top-color: #3498db;
        animation: spin 1s ease-in-out infinite;
        opacity: 0;
      }
      
      @keyframes spin {
        to { transform: rotate(360deg); }
      }
      
      .theme-dark {
        background-color: #333;
        color: #eee;
      }
      
      .theme-dark .pokemon-header {
        background: rgba(255,255,255,0.1);
      }
      
      .theme-dark .move-item {
        background: rgba(255,255,255,0.1);
      }
      
      .pokedex-off {
        background-color: #111 !important;
        color: #333 !important;
        transition: background-color 0.3s, color 0.3s;
      }
      
      .pokedex-off * {
        opacity: 0;
        transition: opacity 0.3s;
      }
      
      .pokedex-off .status-message {
        opacity: 0 !important;
      }
      
      .search-interface {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
      }
      
      .search-box {
        background: white;
        padding: 15px;
        border-radius: 10px;
        width: 80%;
        max-width: 200px;
        text-align: center;
      }
      
      .search-box h3 {
        margin-top: 0;
        color: #333;
      }
      
      .input-container {
        margin: 15px 0;
      }
      
      #pokemon-id-input {
        width: 80%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
        font-size: 16px;
      }
      
      .button-container {
        display: flex;
        justify-content: space-around;
      }
      
      .button-container button {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background: #4caf50;
        color: white;
      }
      
      .button-container button#search-cancel {
        background: #f44336;
      }
    `;
    document.head.appendChild(styles);
  }
  
  // Tipos de Pokémon y colores
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
    fairy: '#EE99AC'
  };
  
  // Traducciones
  const translations = {
    es: {
      height: 'Altura',
      weight: 'Peso',
      baseExperience: 'Experiencia base',
      abilities: 'Habilidades',
      stats: 'Estadísticas',
      moves: 'Movimientos',
      searchPokemon: 'Buscar Pokémon',
      search: 'Buscar',
      cancel: 'Cancelar',
      invalidId: 'Número de Pokémon inválido',
      loadError: 'Error al cargar Pokémon',
      meters: 'm',
      kilograms: 'kg'
    },
    en: {
      height: 'Height',
      weight: 'Weight',
      baseExperience: 'Base Experience',
      abilities: 'Abilities',
      stats: 'Stats',
      moves: 'Moves',
      searchPokemon: 'Search Pokemon',
      search: 'Search',
      cancel: 'Cancel',
      invalidId: 'Invalid Pokemon number',
      loadError: 'Error loading Pokemon',
      meters: 'm',
      kilograms: 'kg'
    }
  };
  
  // Función para obtener texto traducido
  function getTranslation(key) {
    return translations[language][key] || key;
  }
  
  // Cargar datos de Pokémon
  async function loadPokemon(id) {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    if (id < 1 || id > MAX_POKEMON) {
      showStatusMessage(getTranslation('invalidId'));
      return;
    }
    
    showLoading(true);
    
    try {
      const response = await fetch(`https://pokeapi.co/api/v2/pokemon/${id}`);
      if (!response.ok) {
        throw new Error('Error al obtener datos');
      }
      
      const data = await response.json();
      currentPokemon = id;
      displayPokemon(data);
    } catch (error) {
      showStatusMessage(`${getTranslation('loadError')}: ${error.message}`);
    } finally {
      showLoading(false);
    }
  }
  
  // Mostrar datos del Pokémon en pantalla
  function displayPokemon(pokemon) {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    // Información básica
    document.getElementById('pokemon-id').textContent = `#${pokemon.id.toString().padStart(3, '0')}`;
    document.getElementById('pokemon-name').textContent = capitalizeFirstLetter(pokemon.name);
    
    // Imagen
    const imageElement = document.getElementById('pokemon-image');
    imageElement.src = pokemon.sprites.other['official-artwork'].front_default || 
                       pokemon.sprites.front_default;
    
    // Tipos
    const typesContainer = document.getElementById('pokemon-types');
    typesContainer.innerHTML = '';
    pokemon.types.forEach(typeInfo => {
      const type = typeInfo.type.name;
      const badge = document.createElement('span');
      badge.className = 'type-badge';
      badge.textContent = capitalizeFirstLetter(type);
      badge.style.backgroundColor = typeColors[type] || '#999';
      typesContainer.appendChild(badge);
    });
    
    // Detalles según el modo de visualización
    updateDisplayMode(pokemon);
  }
  
  // Actualizar contenido según el modo seleccionado
  function updateDisplayMode(pokemon) {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    const detailsContainer = document.getElementById('pokemon-details');
    detailsContainer.innerHTML = '';
    
    switch(displayMode) {
      case 'stats':
        // Mostrar estadísticas
        const statsHTML = pokemon.stats.map(stat => {
          const statName = stat.stat.name.replace('-', ' ');
          const statValue = stat.base_stat;
          const percentage = Math.min((statValue / 255) * 100, 100);
          
          return `
            <div class="stat-item">
              <div class="stat-name">${capitalizeFirstLetter(statName)}: ${statValue}</div>
              <div class="stats-bar">
                <div class="stats-bar-fill" style="width: ${percentage}%"></div>
              </div>
            </div>
          `;
        }).join('');
        
        detailsContainer.innerHTML = `
          <h4>${getTranslation('stats')}</h4>
          ${statsHTML}
        `;
        break;
        
      case 'moves':
        // Mostrar movimientos (limitados a 10)
        const movesHTML = pokemon.moves.slice(0, 10).map(moveInfo => {
          const moveName = moveInfo.move.name.replace('-', ' ');
          return `<div class="move-item">${capitalizeFirstLetter(moveName)}</div>`;
        }).join('');
        
        detailsContainer.innerHTML = `
          <h4>${getTranslation('moves')}</h4>
          ${movesHTML}
        `;
        break;
        
      default:
        // Modo básico: información general
        detailsContainer.innerHTML = `
          <p>${getTranslation('height')}: ${pokemon.height / 10} ${getTranslation('meters')}</p>
          <p>${getTranslation('weight')}: ${pokemon.weight / 10} ${getTranslation('kilograms')}</p>
          <p>${getTranslation('baseExperience')}: ${pokemon.base_experience}</p>
          <p>${getTranslation('abilities')}: ${pokemon.abilities.map(a => 
            capitalizeFirstLetter(a.ability.name.replace('-', ' ')))
            .join(', ')}</p>
        `;
    }
  }
  
  // Mostrar mensaje de estado
  function showStatusMessage(message) {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    const statusElement = document.getElementById('status-message');
    statusElement.innerHTML = `<p>${message}</p>`;
    statusElement.style.opacity = '1';
    statusElement.style.pointerEvents = 'auto';
    
    setTimeout(() => {
      statusElement.style.opacity = '0';
      statusElement.style.pointerEvents = 'none';
    }, 3000);
  }
  
  // Mostrar/ocultar carga
  function showLoading(show) {
    if (!pokedexPower && show) return; // No mostrar carga si está apagado
    
    const spinner = document.querySelector('.loading-spinner');
    spinner.style.opacity = show ? '1' : '0';
  }
  
  // Mostrar interfaz de búsqueda por ID
  function showSearchInterface() {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    // Actualizar texto según el idioma
    document.querySelector('.search-box h3').textContent = getTranslation('searchPokemon');
    document.getElementById('search-confirm').textContent = getTranslation('search');
    document.getElementById('search-cancel').textContent = getTranslation('cancel');
    
    const searchInterface = document.getElementById('search-interface');
    searchInterface.style.opacity = '1';
    searchInterface.style.pointerEvents = 'auto';
    
    // Enfocar el campo de entrada
    const input = document.getElementById('pokemon-id-input');
    input.value = '';
    input.focus();
  }
  
  // Ocultar interfaz de búsqueda
  function hideSearchInterface() {
    const searchInterface = document.getElementById('search-interface');
    searchInterface.style.opacity = '0';
    searchInterface.style.pointerEvents = 'none';
  }
  
  // Alternar estado de encendido/apagado
  function togglePower() {
    pokedexPower = !pokedexPower;
    screen.classList.toggle('pokedex-off', !pokedexPower);
    
    // Nota: Se han eliminado los sonidos de encendido/apagado
    
    if (pokedexPower) {
      // Esperar un momento para cargar el primer Pokémon
      setTimeout(() => {
        loadPokemon(currentPokemon);
      }, 500);
    }
  }
  
  // Alternar el tema (claro/oscuro)
  function toggleTheme(switchElement) {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    const parent = switchElement.parentElement;
    parent.classList.toggle('on');
    screen.classList.toggle('theme-dark');
  }
  
  // Alternar idioma (español/inglés)
  function toggleLanguage(switchElement) {
    if (!pokedexPower) return; // No hacer nada si está apagado
    
    const parent = switchElement.parentElement;
    parent.classList.toggle('on');
    language = parent.classList.contains('on') ? 'en' : 'es';
    
    // Recargar los datos actuales para actualizar el idioma
    fetch(`https://pokeapi.co/api/v2/pokemon/${currentPokemon}`)
      .then(response => response.json())
      .then(data => {
        displayPokemon(data);
      });
  }
  
  // Helpers
  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
  
  // Configurar controles
  function setupControls() {
    // Botones de dirección
    document.querySelector('.left').addEventListener('click', () => {
      if (!pokedexPower) return;
      const prevId = currentPokemon > 1 ? currentPokemon - 1 : MAX_POKEMON;
      loadPokemon(prevId);
    });
    
    document.querySelector('.right').addEventListener('click', () => {
      if (!pokedexPower) return;
      const nextId = currentPokemon < MAX_POKEMON ? currentPokemon + 1 : 1;
      loadPokemon(nextId);
    });
    
    document.querySelector('.up').addEventListener('click', () => {
      if (!pokedexPower) return;
      displayMode = displayMode === 'basic' ? 'stats' : 
                    displayMode === 'stats' ? 'moves' : 'basic';
      
      // Volver a cargar los datos actualizados
      fetch(`https://pokeapi.co/api/v2/pokemon/${currentPokemon}`)
        .then(response => response.json())
        .then(data => updateDisplayMode(data));
    });
    
    document.querySelector('.down').addEventListener('click', () => {
      if (!pokedexPower) return;
      displayMode = displayMode === 'basic' ? 'moves' : 
                    displayMode === 'moves' ? 'stats' : 'basic';
      
      // Volver a cargar los datos actualizados
      fetch(`https://pokeapi.co/api/v2/pokemon/${currentPokemon}`)
        .then(response => response.json())
        .then(data => updateDisplayMode(data));
    });
    
    // Botón azul (Encender/Apagar)
    document.querySelector('.circulo_azul').addEventListener('click', togglePower);
    
    // Botón amarillo (Búsqueda por ID)
    document.querySelector('.boton_amarillo').addEventListener('click', () => {
      if (!pokedexPower) return;
      showSearchInterface();
    });
    
    // Botón principal superior (Retroceder 10 Pokémon)
    document.querySelector('.boton_principal_top').addEventListener('click', () => {
      if (!pokedexPower) return;
      const newId = currentPokemon > 10 ? currentPokemon - 10 : MAX_POKEMON - (10 - currentPokemon);
      loadPokemon(newId);
    });
    
    // Botón principal inferior (Avanzar 10 Pokémon)
    document.querySelector('.boton_principal_bottom').addEventListener('click', () => {
      if (!pokedexPower) return;
      const newId = (currentPokemon + 10) <= MAX_POKEMON ? currentPokemon + 10 : currentPokemon % 10 + 1;
      loadPokemon(newId);
    });
    
    // Interruptores (switches)
    // El primer interruptor cambia el tema
    const themeSwitch = document.querySelectorAll('.lever')[0];
    themeSwitch.addEventListener('click', () => {
      if (!pokedexPower) return;
      toggleTheme(themeSwitch);
    });
    
    // El segundo interruptor cambia el idioma
    const languageSwitch = document.querySelectorAll('.lever')[1];
    languageSwitch.addEventListener('click', () => {
      if (!pokedexPower) return;
      toggleLanguage(languageSwitch);
    });
    
    // Configurar controles de búsqueda
    document.getElementById('search-confirm').addEventListener('click', () => {
      const input = document.getElementById('pokemon-id-input');
      const id = parseInt(input.value);
      
      if (!isNaN(id) && id >= 1 && id <= MAX_POKEMON) {
        loadPokemon(id);
        hideSearchInterface();
      } else {
        showStatusMessage(getTranslation('invalidId'));
      }
    });
    
    document.getElementById('search-cancel').addEventListener('click', hideSearchInterface);
    
    // También permitir búsqueda con Enter
    document.getElementById('pokemon-id-input').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        document.getElementById('search-confirm').click();
      }
    });
  }
  
  // Inicializar
  function init() {
    setupInterface();
    setupControls();
    loadPokemon(1);
  }
  
  // Lanzar inicialización
  init();
});