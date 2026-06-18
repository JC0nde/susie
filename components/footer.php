<footer class="site-footer">
    <div class="footer-container">
        <p class="copyright">&copy;&nbsp;<?= date('Y'); ?>&nbsp;<?= htmlspecialchars($config['site']['author'] ?? 'Jonathan Conde') ?>. Tous droits réservés.</p>

        <div class="footer-links">
            <a href="https://www.linkedin.com/in/jonathanconde" target="_blank" rel="noopener noreferrer">LinkedIn</a>
            <span class="separator" style="color: var(--muted-color); padding: 0 10px;">•</span>
            <a href="mailto:mail@jonathanconde.ch">Contact</a>
        </div>
    </div>
<?php include __DIR__ . '/categories.php'; ?>
    <div class="footer-note">
        <small>Propulsé fièrement par Susie (HTML/PHP&nbsp;<?= phpversion(); ?>) sous Linux.</small>
    </div>
</footer>
