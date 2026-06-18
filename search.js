const modal = document.getElementById('search-modal');
const trigger = document.getElementById('search-trigger');
const input = document.getElementById('search-input');
const results = document.getElementById('search-results');
let searchIndex = null;
let activeIndex = -1;

if (trigger && modal) {

    async function openSearch() {
        modal.style.display = 'block';
        input.focus();
        if (!searchIndex) {
            const response = await fetch('/search-index.json');
            searchIndex = await response.json();
        }
    }

    function closeSearch() {
        modal.style.display = 'none';
        input.value = '';
        results.innerHTML = '';
        activeIndex = -1;
    }

    trigger.addEventListener('click', openSearch);
    
    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            modal.style.display === 'none' ? openSearch() : closeSearch();
        }
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault(); // Bloque la recherche native du navigateur !
            if (modal.style.display === 'none' || !modal.style.display) {
                openSearch();
            }
        }
        if (e.key === 'Escape') closeSearch();
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeSearch();
    });

    function updateSelection(links) {
        links.forEach((link, index) => {
            const li = link.parentElement;
            if (index === activeIndex) {
                li.style.backgroundColor = 'rgba(0, 255, 204, 0.15)';
                li.style.paddingLeft = '5px';
                li.style.borderRadius = '3px';
                link.style.color = '#fff';
            } else {
                li.style.backgroundColor = '';
                li.style.paddingLeft = '0px';
                link.style.color = '#00ffcc';
            }
        });
    }

    input.addEventListener('keydown', (e) => {
        const links = results.querySelectorAll('a');
        if (links.length === 0) return;

        if (e.key === 'Enter') {
            e.preventDefault();
            if (links.length === 1) {
                links[0].click();
            } else if (activeIndex >= 0) {
                links[activeIndex].click();
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex++;
            if (activeIndex >= links.length) activeIndex = 0;
            updateSelection(links);
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex--;
            if (activeIndex < 0) activeIndex = links.length - 1;
            updateSelection(links);
        }
    });

    input.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        results.innerHTML = '';
        activeIndex = -1;
        if (query.length < 2 || !searchIndex) return;

        const matches = searchIndex.filter(item => 
            item.title.toLowerCase().includes(query) || 
            item.content.includes(query) || 
            item.description.toLowerCase().includes(query)
        );

        matches.forEach(item => {
            const li = document.createElement('li');
            li.style.margin = '10px 0';
            li.style.transition = 'all 0.1s ease';
            li.innerHTML = `<a href="${item.slug}" style="color: #00ffcc; text-decoration: none; display: inline-block; width: 100%;"><strong>${item.title}</strong></a><br><small style="color: #aaa;">${item.description}</small>`;
            results.appendChild(li);
        });

        if (matches.length === 0) {
            results.innerHTML = '<li style="color: #888;">Aucun résultat trouvé.</li>';
        }
    });
}
