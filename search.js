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
            e.preventDefault(); /* Securite : Bloque la recherche native du navigateur */
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
        Array.from(links).forEach((link, index) => {
            if (index === activeIndex) {
                link.style.background = 'var(--accent-color)';
                link.style.color = '#121212';
                link.focus();
            } else {
                link.style.background = 'none';
                link.style.color = 'var(--accent-color)';
            }
        });
    }

    window.addEventListener('keydown', (e) => {
        const links = results.getElementsByTagName('a');
        if (links.length === 0 || modal.style.display === 'none') return;

        if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            links[activeIndex].click();
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
            li.innerHTML = `<a href="${item.slug}" style="color: var(--accent-color); text-decoration: none; display: inline-block; width: 100%;"><strong>${item.title}</strong></a><br><small style="color: var(--muted-color);">${item.description || ''}</small>`;
            results.appendChild(li);
        });

        if (matches.length === 0) {
            results.innerHTML = '<li style="color: var(--muted-color); font-family: monospace;">Aucun résultat trouvé.</li>';
        }
    });
}
