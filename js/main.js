// main.js (o donde tengas tu código)
document.querySelectorAll('.switcher').forEach(switcher => {
  switcher.addEventListener('click', () => {
    // Cambiamos entre “on” y “off”
    switcher.classList.toggle('on');
  });
});




document.addEventListener('DOMContentLoaded', () => {
  const screens = document.querySelectorAll('.screen');
  const leftButton = document.querySelector('.left');
  const rightButton = document.querySelector('.right');
  let currentIndex = 0;

  function showScreen(index) {
    screens.forEach((screen, i) => {
      screen.classList.toggle('active', i === index);
    });
  }

  leftButton.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + screens.length) % screens.length;
    showScreen(currentIndex);
  });

  rightButton.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % screens.length;
    showScreen(currentIndex);
  });

  // Mostrar la primera pantalla al cargar
  showScreen(currentIndex);
});


document.addEventListener('DOMContentLoaded', () => {
  const pantalla = document.querySelector('.pantalla');
  const botonAzul = document.querySelector('.circulo_azul');

  botonAzul.addEventListener('click', () => {
    pantalla.classList.toggle('off');
  });
});
