 // Dark Mode Toggle Functionality
 document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const htmlElement = document.documentElement;

    // Check for saved dark mode preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      htmlElement.setAttribute('data-bs-theme', savedTheme);
      updateDarkModeIcon(savedTheme);
    }

    darkModeToggle.addEventListener('click', () => {
      const currentTheme = htmlElement.getAttribute('data-bs-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      
      htmlElement.setAttribute('data-bs-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      updateDarkModeIcon(newTheme);
    });

    function updateDarkModeIcon(theme) {
      const icon = darkModeToggle.querySelector('i');
      if (theme === 'dark') {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        darkModeToggle.classList.remove('btn-outline-secondary');
        darkModeToggle.classList.add('btn-outline-light');
      } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        darkModeToggle.classList.remove('btn-outline-light');
        darkModeToggle.classList.add('btn-outline-secondary');
      }
    }
  });