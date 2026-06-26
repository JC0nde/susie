<?php
/**
 * Susie - Common Structural Header Component
 * Assembles the persistent site branding area and the reactive site-wide overlay trigger.
 *
 * @package Susie
 */

global $config;
?>
<header class="site-header">
    <div class="header-container" style="display: flex; justify-content: space-between; align-items: center;">
        
        <a href="/" class="logo" style="text-decoration: none; font-weight: bold;">&lt;<?= htmlspecialchars($config['site']['title'] ?? 'Susie') ?>&gt;</a>
        
        <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
            
            <?php if (($config['features']['generate_search'] ?? 'false') === 'true'): ?>
                <button id="search-trigger" style="background: none; border: none; color: var(--muted-color); font-family: monospace; padding: 0; cursor: pointer; font-size: 18px;">
                    <span style="color: var(--accent-color);">/</span> search
                </button>
            <?php endif; ?>

        </div>
    </div>
</header>

<?php if (($config['features']['generate_search'] ?? 'false') === 'true'): ?>
<div id="search-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(18, 18, 18, 0.95); z-index: 1000; padding: 2rem;">
    <div style="background: var(--code-bg, #1e1e1e); max-width: 600px; margin: 10% auto; padding: 20px; border: 1px solid var(--border-color, #333);">
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
            <span style="color: var(--accent-color); font-family: monospace;">/</span>
            <input type="text" id="search-input" placeholder="search articles..." style="width: 100%; background: none; border: none; color: var(--text-color); font-family: monospace; font-size: 18px; outline: none;">
        </div>
        <hr style="border: none; border-top: 1px solid var(--border-color, #333); margin: 10px 0;">
        
        <ul id="search-results" style="list-style: none; padding: 0; margin-top: 20px; max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;"></ul>
        <div style="text-align: right; margin-top: 15px;">
            <small style="color: var(--muted-color); font-family: monospace; font-size: 11px;">[ESC to close]</small>
        </div>
    </div>
</div>

<?php endif; ?>
