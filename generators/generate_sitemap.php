<?php
require __DIR__ . '/../functions.php';

$exclude_categories = ($config['seo']['exclude_categories_from_sitemap'] ?? 'false') === 'true';

$sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

$dir = new RecursiveDirectoryIterator('dist_tmp');
$iterator = new RecursiveIteratorIterator($dir);
$html_files = new RegexIterator($iterator, '/^.+\.html$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($html_files as $file) {
    $absolute_path = $file[0];
    $filename = basename($absolute_path);

    if ($filename === '404.html') continue;

    $clean_url_path = str_replace('dist_tmp/', '', str_replace('\\', '/', $absolute_path));

    if ($filename === 'index.html') {
        // On retire "index.html" pour n'avoir que la racine ou le sous-dossier propre
        $clean_url_path = str_replace('index.html', '', $clean_url_path);
    }
    // -----------------------------------------------------------------

    if ($exclude_categories && strpos($clean_url_path, 'categorie/') === 0) continue;

    $priority = '0.5';

    if (in_array($filename, ['services.html', 'about.html', 'a-propos.html', 'contact.html', 'blog.html', 'projets.html']) || strpos($clean_url_path, 'projets/') === 0) {
        $priority = '0.8';
    }

    if ($filename === 'index.html') {
        $priority = '1.0';
    }

    // On utilise rtrim() à la fin pour éviter d'avoir un double slash final (ex: domaine.com//) si $clean_url_path est vide
    $final_url = rtrim($base_url . '/' . $clean_url_path, '/');
    
    // Si c'est la racine pure, on rajoute juste le slash final standard pour faire propre
    if ($final_url === $base_url) {
        $final_url .= '/';
    }

    $sitemap .= '  <url><loc>' . $final_url . '</loc><priority>' . $priority . '</priority></url>' . PHP_EOL;
}

$sitemap .= '</urlset>' . PHP_EOL;
file_put_contents('dist_tmp/sitemap.xml', $sitemap);
echo "[BUILD] Syndication Module: sitemap.xml generated." . PHP_EOL;
