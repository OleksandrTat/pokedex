// main.js (o donde tengas tu código)
document.querySelectorAll('.switcher').forEach(switcher => {
  switcher.addEventListener('click', () => {
    // Cambiamos entre “on” y “off”
    switcher.classList.toggle('on');
  });
});