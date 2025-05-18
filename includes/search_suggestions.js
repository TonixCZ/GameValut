// Autocomplete vyhledávání
const searchInput = document.querySelector('input[name="search"]');
const suggestionsBox = document.getElementById('search-suggestions');

searchInput.addEventListener('input', () => {
  const query = searchInput.value.trim();
  if (query.length < 2) {
    suggestionsBox.style.display = 'none';
    return;
  }

  fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      if (data.length === 0) {
        suggestionsBox.style.display = 'none';
        return;
      }
      suggestionsBox.innerHTML = '';
      data.forEach(game => {
        const div = document.createElement('div');
        div.textContent = game.name;
        div.addEventListener('click', () => {
          searchInput.value = game.name;
          suggestionsBox.style.display = 'none';
        });
        suggestionsBox.appendChild(div);
      });
      suggestionsBox.style.display = 'block';
    })
    .catch(() => {
      suggestionsBox.style.display = 'none';
    });
});

// Kliknutí mimo box zavře suggestions
document.addEventListener('click', (e) => {
  if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
    suggestionsBox.style.display = 'none';
  }
});
