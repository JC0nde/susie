<?php
require __DIR__ . '/../functions.php';

global $config;
$enabled = ($config['features']['generate_categories'] ?? 'true') === 'true';

if (!$enabled) {
    echo "[SKIP]  Pages de categories desactivees (config.ini)." . PHP_EOL;
    exit;
}

$categories = get_categories();
$noindex = ($config['seo']['exclude_categories_from_sitemap'] ?? 'false') === 'true';

if (!empty($categories)) {
    @mkdir('dist_tmp/categorie', 0755, true);
    foreach ($categories as $cat_slug => $cat) {
        $current_slug = 'categorie/' . $cat_slug;
        $category_name = $cat['name'];
        $category_posts = $cat['posts'];

        ob_start();
        include 'templates/category_wrapper.php';
        ob_end_clean();

        ob_start();
        include './layouts/main.php';
        $html = minify_html(ob_get_clean());

        file_put_contents('dist_tmp/categorie/' . $cat_slug . '.html', $html);
        echo "[CAT]   Generated : dist/categorie/{$cat_slug}.html" . PHP_EOL;
    }
} else {
    echo "[SKIP]  No categories found — skipping." . PHP_EOL;
}
