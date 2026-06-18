<header class="site-header">
    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center;">
        
        <?php if (($current_slug ?? 'index') === 'index'): ?>
            <h1 class="logo-wrapper" style="margin: 0;">
                <a href="/" class="logo" style="text-decoration: none;">&lt;<?= htmlspecialchars($config['site']['title'] ?? 'Conde') ?>&gt;</a>
            </h1>
        <?php else: ?>
            <a href="/" class="logo" style="text-decoration: none;">&lt;<?= htmlspecialchars($config['site']['title'] ?? 'Conde') ?>&gt;</a>
        <?php endif; ?>
        
        <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
            
            <?php if (($config['features']['generate_search'] ?? 'false') === 'true'): ?>
                <button id="search-trigger" style="background: none; border: none; color: var(--muted-color); font-family: monospace; padding: 0; cursor: pointer; font-size: 18px;">
                    <span style="color: var(--accent-color);">/</span> rechercher
                </button>
            <?php endif; ?>

            <nav class="main-nav">
                <ul style="list-style: none; margin: 0; padding: 0; display: flex; gap: 15px;">
                    <li><a href="/projets.html" style="text-decoration: none;">projets</a></li>
                </ul>
            </nav>
            
        </div>
    </div>
</header>

<div id="search-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(18, 18, 18, 0.95); z-index: 1000; padding: 2rem;">
    <div style="background: var(--code-bg); max-width: 600px; margin: 10% auto; padding: 20px; border: 1px solid var(--border-color);">
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
            <span style="color: var(--accent-color); font-family: monospace;">/</span>
            <input type="text" id="search-input" placeholder="rechercher un article..." style="width: 100%; background: none; border: none; color: var(--text-color); font-family: monospace; font-size: 18px; outline: none;" autofocus>
        </div>
        <hr style="border: none; border-top: 1px solid var(--border-color); margin: 10px 0;">
        
        <ul id="search-results" style="list-style: none; padding: 0; margin-top: 20px; max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;"></ul>
    </div>
</div>

<div class="site-content-wrapper" style="display: flex; flex-direction: column; gap: 30px; width: 100%; margin-top: 20px;">
