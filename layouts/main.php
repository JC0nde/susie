<!DOCTYPE html>
<html lang="<?= $lang ?? 'en' ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= htmlspecialchars($title ?? ($config['site']['title'] ?? "Jonathan Conde")) ?></title>

        <link rel="icon" type="image/png" href="/favicon.png">

        <meta name="author" content="<?= htmlspecialchars($author ?? ($config['site']['author'] ?? 'Jonathan Conde')) ?>">
        <meta name="description" content="<?= htmlspecialchars($description ?? 'Site et blog minimaliste propulsé par Susie.') ?>">

        <meta property="og:type" content="website">
        <meta property="og:url" content="<?= $base_url ?>/<?= htmlspecialchars($current_slug ?? 'index') ?>.html">
        <meta property="og:title" content="<?= htmlspecialchars($title ?? ($config['site']['title'] ?? "Jonathan Conde")) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($description ?? 'Site et blog minimaliste.') ?>">
        <meta property="og:image" content="<?= $base_url ?>/favicon.png">

        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:title" content="<?= htmlspecialchars($title ?? ($config['site']['title'] ?? "Jonathan Conde")) ?>">
        <meta property="twitter:description" content="<?= htmlspecialchars($description ?? 'Site et blog minimaliste.') ?>">

        <?php if (!empty($noindex)): ?>
                <meta name="robots" content="noindex, follow">
        <?php endif; ?>

        <?php 
        $css_mode = $config['assets']['css_mode'] ?? 'inline';
        $build_ver = getenv('BUILD_VERSION') ?: time(); 
        ?>

        <?php if ($css_mode === 'inline'): ?>
            <style>
                <?php
                if (file_exists(__DIR__ . '/../style.min.css')) {
                    echo file_get_contents(__DIR__ . '/../style.min.css');
                } elseif (file_exists(__DIR__ . '/../style.css')) {
                    echo file_get_contents(__DIR__ . '/../style.css');
                }
                ?>
            </style>
        <?php else: ?>
            <link rel="stylesheet" href="/style.css?v=<?= $build_ver ?>">
        <?php endif; ?>

        <?php 
        // Correction des chemins pour le pipeline d'assets pendant le build
        $js_mode = $config['assets']['js_mode'] ?? 'inline';
        $base_dir = __DIR__ . '/../dist_tmp'; // Pendant le build, Susie écrit dans dist_tmp
        
        $js_inline_file = $base_dir . '/temp_bundle.js';
        $js_bundle_file = $base_dir . '/bundle.js';

        if ($js_mode === 'inline' && file_exists($js_inline_file)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?= file_get_contents($js_inline_file); ?>
                });
            </script>
        <?php elseif ($js_mode === 'file' && file_exists($js_bundle_file)): ?>
            <script src="/bundle.js?v=<?= $build_ver ?>" defer></script>
        <?php endif; ?>

    </head>
    <body>

        <?php include __DIR__ . '/../components/header.php'; ?>

        <main>
        <?php if (isset($post_header_html)) echo $post_header_html; ?>

        <?= $content; ?>

        <?php if (isset($navigation_html)) echo $navigation_html; ?>
        </main>

        <?php include __DIR__ . '/../components/footer.php'; ?>

        <div id="search-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 1000;">
            <div style="background: #1a1a1a; max-width: 600px; margin: 10% auto; padding: 20px; border: 1px solid #333; font-family: monospace;">
                <input type="text" id="search-input" placeholder="Taper pour rechercher..." style="width: 100%; padding: 10px; background: #121212; color: #00ffcc; border: 1px solid #333; font-family: monospace;" autofocus>
                <ul id="search-results" style="list-style: none; padding: 0; margin-top: 20px; max-height: 300px; overflow-y: auto;"></ul>
            </div>
        </div>

        <?php if (getenv('DEV_MODE') === '1'): ?>
        <script>
            (function() {
                let currentVersion = null;
                setInterval(function() {
                    fetch('/build-version.txt', { cache: 'no-store' })
                        .then(function(res) { return res.text(); })
                        .then(function(version) {
                            version = version.trim();
                            if (currentVersion === null) {
                                currentVersion = version;
                            } else if (version !== currentVersion) {
                                location.reload();
                            }
                        })
                        .catch(function() { /* serveur down pendant rebuild */ });
                }, 1000);
            })();
        </script>
        <?php endif; ?>

    </body>
</html>
