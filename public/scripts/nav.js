const toggleButton = document.getElementsByClassName('toggle-button')[0];
const mobileLinks = document.getElementsByClassName('mobile-links')[0];

toggleButton.addEventListener('click', () => {
    mobileLinks.classList.toggle("active");
});

document.addEventListener('click', e=> {
    const isDropdownButton = e.target.matches("[data-dropdown-button]");
    if(!isDropdownButton && e.target.closest('[data-dropdown]') != null) return
    
    let currentDropdown
    if(isDropdownButton) {
        currentDropdown = e.target.closest('[data-dropdown]');
        currentDropdown.classList.toggle('active');
    }

    document.querySelectorAll('[data-dropdown].active').forEach(dropdown => {
        if(dropdown === currentDropdown) return
        dropdown.classList.remove('active');
    });
}); 