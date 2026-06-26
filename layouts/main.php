<!DOCTYPE html>
<html lang="<?= $lang ?? ($config['site']['lang'] ?? 'en') ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= htmlspecialchars($title ?? ($config['site']['title'] ?? "Susie Blog")) ?></title>

        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="alternate" type="application/rss+xml" title="RSS Feed" href="<?= $base_url ?>/feed.xml" />
        <meta name="author" content="<?= htmlspecialchars($author ?? ($config['site']['author'] ?? 'Susie User')) ?>">
        <meta name="description" content="<?= htmlspecialchars($description ?? 'A minimalist static blog powered by Susie.') ?>">
        
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?= $base_url ?>/<?= htmlspecialchars($current_slug ?? 'index') ?>.html">
        <meta property="og:title" content="<?= htmlspecialchars($title ?? ($config['site']['title'] ?? "Susie Blog")) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($description ?? 'A minimalist static blog.') ?>">
        <meta property="og:image" content="<?= $base_url ?>/favicon.png">

        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:title" content="<?= htmlspecialchars($title ?? ($config['site']['title'] ?? "Susie Blog")) ?>">
        <meta property="twitter:description" content="<?= htmlspecialchars($description ?? 'A minimalist static blog.') ?>">

        <?php if (!empty($noindex)): ?>
                <meta name="robots" content="noindex, follow">
        <?php endif; ?>

        <?php 
        // Asset pipeline routing strategies (inline style extraction vs linked asset stylesheets)
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
        // JavaScript pipeline path resolution during active staging phases
        $js_mode = $config['assets']['js_mode'] ?? 'inline';
        $base_dir = __DIR__ . '/../dist_tmp';
        
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
                        .catch(function() { /* Server compilation transit phase timeout bypass */ });
                }, 1000);
            })();
        </script>
        <?php endif; ?>

    </body>
</html>
