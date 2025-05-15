/**
 * JavaScript navigation-simplified.js 
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get navigation buttons
    const upButton = document.querySelector('.up');
    const downButton = document.querySelector('.down');
    const randomButton = document.querySelector('.buttonRandom');
    const idButton = document.querySelector('.buttonId');
    const nameButton = document.querySelector('.buttonName');
    const exportButton = document.querySelector('.buttonExport');
    
    // Current pokemon ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentId = urlParams.get('id') || 25; // Default to Pikachu (#25)
    
    // Add event listeners for navigation buttons
    upButton.addEventListener('click', function() {
        // Navigate to previous Pokemon
        fetchPokemon('next', currentId);
    });
    
    downButton.addEventListener('click', function() {
        // Navigate to next Pokemon
        fetchPokemon('prev', currentId);
    });
    
    // Random button
    randomButton.addEventListener('click', function() {
        fetchPokemon('random', currentId);
    });
    
    // ID search button
    idButton.addEventListener('click', function() {
        searchPokemon('id');
    });
    
    // Name search button
    nameButton.addEventListener('click', function() {
        searchPokemon('name');
    });
    
    // Export button
    exportButton.addEventListener('click', function() {
        exportPokemon(currentId);
    });
    
    /**
     * Fetch Pokemon data via AJAX
     */
    function fetchPokemon(action, id) {
        // Show loading state
        document.body.classList.add('loading');
        
        // Create URL for AJAX request
        const url = `pokemon_ajax.php?action=${action}&id=${id}`;
        
        // Fetch data
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    // Redirect to new pokemon
                    window.location.href = data.redirect;
                } else if (data.error) {
                    console.error('Error:', data.error);
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            })
            .finally(() => {
                // Remove loading state
                document.body.classList.remove('loading');
            });
    }
    
    /**
     * Search for a Pokemon by ID or name
     */
    function searchPokemon(type) {
        let prompt;
        if (type === 'id') {
            prompt = 'Enter the Pokedex number:';
        } else {
            prompt = 'Enter the Pokemon name:';
        }
        
        const searchTerm = window.prompt(prompt);
        
        if (searchTerm !== null && searchTerm.trim() !== '') {
            // Show loading state
            document.body.classList.add('loading');
            
            // Create form data
            const formData = new FormData();
            formData.append('term', searchTerm.trim());
            
            // Send POST request
            fetch('search.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Pokemon not found');
                }
            })
            .catch(error => {
                console.error('Error searching Pokemon:', error);
                alert('An error occurred while searching');
            })
            .finally(() => {
                // Remove loading state
                document.body.classList.remove('loading');
            });
        }
    }

    /**
     * Export Pokemon data as JSON
     */
    function exportPokemon(id) {
        window.location.href = `export.php?id=${id}`;
    }

});