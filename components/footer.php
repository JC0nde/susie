<?php
/**
 * Susie - Common Structural Footer Component
 * Renders the global site navigation links, social matrix endpoints, and engine credits.
 *
 * @package Susie
 */

global $config;

// Extract configured social handles and links from config.ini
$_masto_id = $config['mastodon']['id'] ?? '';
$_masto_instance = rtrim($config['mastodon']['instance'] ?? 'mastodon.social', '/');
$_linkedin_url = $config['social']['linkedin'] ?? '';
?>
<footer class="site-footer">
    <div class="footer-container">
        <nav class="main-nav">
            <ul style="list-style: none; margin: 0; padding: 0; display: flex; gap: 15px;">
                <li><a href="/projets.html" style="text-decoration: none;">projets</a></li>
            </ul>
        </nav> 
        
        <div class="footer-links">
            <a href="/feed.xml" rel="alternate" type="application/rss+xml" title="RSS Feed">RSS</a>
            
            <?php if (!empty($_masto_id)): ?>
                <span class="separator" style="color: var(--muted-color); padding: 0 10px;">•</span>
                <a rel="me" target="_blank" href="https://<?= $_masto_instance ?>/@<?= ltrim($_masto_id, '@') ?>">Mastodon</a>
            <?php endif; ?>
            
            <?php if (!empty($_linkedin_url)): ?>
                <span class="separator" style="color: var(--muted-color); padding: 0 10px;">•</span>
                <a href="<?= htmlspecialchars($_linkedin_url) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
            <?php endif; ?>
            
            <span class="separator" style="color: var(--muted-color); padding: 0 10px;">•</span>
            <?php 
            $email_text = "Email";
            include __DIR__ . '/email_link.php'; 
            ?>
        </div>
    </div>

    <?php 
    if (file_exists(__DIR__ . '/categories.php')) {
        include __DIR__ . '/categories.php'; 
    }
    ?>

    <div class="footer-note">
        <small>Proudly powered by <a href="https://github.com/JC0nde/susie" target="_blank" rel="noopener" style="color: inherit; text-decoration: underline;">Susie</a> (HTML/PHP <?= phpversion(); ?>) on Linux.</small>
    </div>
</footer>
