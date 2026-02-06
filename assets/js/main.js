// assets/js/main.js

document.addEventListener('DOMContentLoaded', function () {
  // Example: Alert welcome message on homepage
  if (document.body.classList.contains('homepage')) {
    alert('Welcome to PawConnect! Find your new best friend today.');
  }

  // Example: Simple smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });
});
