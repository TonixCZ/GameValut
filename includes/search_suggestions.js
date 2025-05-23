// Autocomplete vyhledávání
const searchInput = document.getElementById('game-search');
const suggestions = document.getElementById('search-suggestions');

let lastValue = '';
searchInput.addEventListener('input', function() {
  const val = this.value.trim();
  if (val.length < 1) {
    suggestions.style.display = 'none';
    suggestions.innerHTML = '';
    return;
  }
  if (val === lastValue) return;
  lastValue = val;
  fetch('includes/search_games.php?q=' + encodeURIComponent(val))
    .then(res => res.json())
    .then(data => {
      if (!data.length) {
        suggestions.style.display = 'none';
        suggestions.innerHTML = '';
        return;
      }
      suggestions.innerHTML = data.map(game =>
        `<a href="game_detail?id=${game.id}" class="list-group-item list-group-item-action d-flex align-items-center">
          <img src="${game.image ? 'uploads/games/' + game.image : 'assets/images/no-cover.png'}" alt="" style="width:32px;height:32px;object-fit:cover;border-radius:6px;margin-right:10px;">
          <span>${game.title}</span>
        </a>`
      ).join('');
      suggestions.style.display = 'block';
    });
});

// Hide suggestions when clicking outside
document.addEventListener('click', e => {
  if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
    suggestions.style.display = 'none';
  }
});
