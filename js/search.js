/**
 * Student Event Management (EMS) - Dynamic Search & Filter for Events
 */
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const cardsGrid = document.querySelector('.cards-grid');

    if (!cardsGrid) return;

    const cards = Array.from(cardsGrid.querySelectorAll('.card'));
    let noMatchAlert = document.getElementById('noMatchAlert');

    if (!noMatchAlert) {
        noMatchAlert = document.createElement('div');
        noMatchAlert.id = 'noMatchAlert';
        noMatchAlert.className = 'alert alert-info';
        noMatchAlert.style.display = 'none';
        noMatchAlert.innerHTML = '<p style="text-align: center; margin: 0;">No events match your search query. Try different keywords.</p>';
        cardsGrid.parentNode.insertBefore(noMatchAlert, cardsGrid.nextSibling);
    }

    function filterAndSortEvents() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const sortBy = sortSelect ? sortSelect.value : '';

        let visibleCount = 0;

        // Filter cards
        cards.forEach(card => {
            const title = (card.querySelector('h3')?.textContent || '').toLowerCase();
            const body = (card.querySelector('.card-body')?.textContent || '').toLowerCase();
            const textToMatch = title + ' ' + body;

            const matches = !query || textToMatch.includes(query);
            if (matches) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide no match message
        if (visibleCount === 0 && cards.length > 0) {
            noMatchAlert.style.display = 'block';
        } else {
            noMatchAlert.style.display = 'none';
        }

        // Sort cards
        if (sortBy) {
            const sortedCards = [...cards].sort((a, b) => {
                if (sortBy === 'title') {
                    const titleA = (a.querySelector('h3')?.textContent || '').trim().toLowerCase();
                    const titleB = (b.querySelector('h3')?.textContent || '').trim().toLowerCase();
                    return titleA.localeCompare(titleB);
                } else if (sortBy === 'date') {
                    const dateTextA = a.querySelector('.card-info')?.textContent || '';
                    const dateTextB = b.querySelector('.card-info')?.textContent || '';
                    const timeA = Date.parse(dateTextA) || 0;
                    const timeB = Date.parse(dateTextB) || 0;
                    return timeA - timeB;
                }
                return 0;
            });

            sortedCards.forEach(card => cardsGrid.appendChild(card));
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterAndSortEvents);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', filterAndSortEvents);
    }
});
