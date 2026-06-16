document.addEventListener('DOMContentLoaded', function () {
    const themeBtn = document.getElementById('themeToggle');
    if (!themeBtn) return;

    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.body.classList.toggle('light-mode', currentTheme === 'light');
    themeBtn.textContent = currentTheme === 'light' ? 'Dark Mode' : 'Light Mode';

    themeBtn.addEventListener('click', () => {
        const isLight = document.body.classList.toggle('light-mode');
        themeBtn.textContent = isLight ? 'Dark Mode' : 'Light Mode';
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    });
});
