// Theme scripts
import './reading-progress.js';

// Dark mode is handled entirely by dark-mode.php (inline head + footer scripts).
// prt-theme key in localStorage, 'dark'/'light' values.
// Do NOT duplicate the toggle logic here — two listeners cancel each other out.

document.addEventListener('DOMContentLoaded', () => {
  // Popout menu
  const menuToggle  = document.querySelector('.menu-toggle');
  const popout      = document.getElementById('prt-popout');
  const overlay     = document.querySelector('.prt-popout-overlay');
  const closeBtn    = document.querySelector('.prt-popout-close');

  const openMenu  = () => {
    document.body.classList.add('prt-popout-open');
    menuToggle && menuToggle.setAttribute('aria-expanded', 'true');
    popout && popout.focus();
  };
  const closeMenu = () => {
    document.body.classList.remove('prt-popout-open');
    menuToggle && menuToggle.setAttribute('aria-expanded', 'false');
  };

  menuToggle && menuToggle.addEventListener('click', openMenu);
  closeBtn   && closeBtn.addEventListener('click', closeMenu);
  overlay    && overlay.addEventListener('click', closeMenu);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });
});
