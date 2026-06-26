<?php
/**
 * Susie - Category Archive Generator Engine
 * Iterates through identified post categories and builds static listing index directories.
 *
 * @package Susie
 */

require __DIR__ . '/../functions.php';

global $config;
$enabled = ($config['features']['generate_categories'] ?? 'true') === 'true';

if (!$enabled) {
    echo "[SKIP]  Category Module: Listing framework deactivated in config.ini." . PHP_EOL;
    exit;
}

$categories = get_categories();
$noindex = ($config['seo']['exclude_categories_from_sitemap'] ?? 'false') === 'true';

if (!empty($categories)) {
    // Re-create the tracking compilation directory structure safely
    if (!is_dir('dist_tmp/categorie')) {
        @mkdir('dist_tmp/categorie', 0755, true);
    }

    foreach ($categories as $cat_slug => $cat) {
        $current_slug = 'categorie/' . $cat_slug;
        $category_name = $cat['name'];
        $category_posts = $cat['posts'];

        // SUCKLESS PIPELINE HOOK: Let the wrapper build the content string and execute the layout chain
        ob_start();
        include __DIR__ . '/../templates/category_wrapper.php';
        $html = ob_get_clean();

        // Write the finalized minified markup code out into production distribution buckets
        file_put_contents('dist_tmp/categorie/' . $cat_slug . '.html', $html);
        echo "[CAT]   Generated compilation index: dist/categorie/{$cat_slug}.html" . PHP_EOL;
    }
} else {
    echo "[SKIP]  Category Module: No posts categorized — skipping rendering pass." . PHP_EOL;
}
