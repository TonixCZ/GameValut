// Dropdowny: Kategorie a Uživatel
const categoryBtn = document.getElementById('categoryBtn');
const categoryMenu = document.getElementById('categoryMenu');
const userDropdownBtn = document.getElementById('userDropdownBtn');
const userDropdownMenu = document.getElementById('userDropdownMenu');

// Kategorie dropdown
if (categoryBtn && categoryMenu) {
  categoryBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const expanded = categoryBtn.getAttribute('aria-expanded') === 'true';
    categoryBtn.setAttribute('aria-expanded', !expanded);
    categoryMenu.classList.toggle('show');
    if(userDropdownMenu) {
      userDropdownMenu.classList.remove('show');
      if (userDropdownBtn) userDropdownBtn.setAttribute('aria-expanded', 'false');
    }
  });

  categoryMenu.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', () => {
      const category = item.getAttribute('data-value');
      window.location.href = `games.php?category=${encodeURIComponent(category)}`;
    });
  });
}

// Uživatelský dropdown
if(userDropdownBtn && userDropdownMenu) {
  userDropdownBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const expanded = userDropdownBtn.getAttribute('aria-expanded') === 'true';
    userDropdownBtn.setAttribute('aria-expanded', !expanded);
    userDropdownMenu.classList.toggle('show');
    if (categoryMenu && categoryBtn) {
      categoryMenu.classList.remove('show');
      categoryBtn.setAttribute('aria-expanded', 'false');
    }
  });
}

// Zavření při kliknutí mimo
document.addEventListener('click', () => {
  if (categoryMenu && categoryBtn) {
    categoryMenu.classList.remove('show');
    categoryBtn.setAttribute('aria-expanded', 'false');
  }
  if(userDropdownMenu && userDropdownBtn) {
    userDropdownMenu.classList.remove('show');
    userDropdownBtn.setAttribute('aria-expanded', 'false');
  }
});
