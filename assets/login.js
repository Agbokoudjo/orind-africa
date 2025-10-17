   document.addEventListener('DOMContentLoaded', () => {
    const toggleInput = document.getElementById('remember_me');
    const slider = toggleInput.nextElementSibling;

    // Mettre à jour le style automatiquement quand l'état change
    toggleInput.addEventListener('change', () => {
        slider.classList.toggle('active', toggleInput.checked);
        console.log('Checked:', toggleInput.checked);
    });

    // Au chargement, synchroniser
    if (toggleInput.checked) slider.classList.add('active');
});
