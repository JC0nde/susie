<?php
/**
 * Susie - XML Sitemap Manifest Synthesizer
 * Crawls compiled static distribution assets to generate a search-engine-ready mapping manifest.
 *
 * @package Susie
 */

require __DIR__ . '/../functions.php';

global $config, $base_url;

$exclude_categories = ($config['seo']['exclude_categories_from_sitemap'] ?? 'false') === 'true';

$sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

$dir = new RecursiveDirectoryIterator('dist_tmp');
$iterator = new RecursiveIteratorIterator($dir);
$html_files = new RegexIterator($iterator, '/^.+\.html$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($html_files as $file) {
    $absolute_path = $file[0];
    $filename = basename($absolute_path);

    // Hard skip for production custom routing error files
    if ($filename === '404.html') {
        continue;
    }

    // Normalize Windows and Unix path structures down to production URI standards
    $clean_url_path = str_replace('dist_tmp/', '', str_replace('\\', '/', $absolute_path));
    
    // Save state before stripping for priority evaluation
    $is_root_index = ($filename === 'index.html' && strpos($clean_url_path, '/') === false);

    if ($filename === 'index.html') {
        // Enforce canonical clean directory paths by stripping explicit index pointers
        $clean_url_path = str_replace('index.html', '', $clean_url_path);
    }

    // Contextual exclusion rules for category indexing routes
    if ($exclude_categories && strpos($clean_url_path, 'categorie/') === 0) {
        continue;
    }

    // Default priority fallback value
    $priority = '0.5';

    // Standard high-level landing pages priority mapping weight
    if (in_array($filename, ['susie.html','about.html', 'contact.html', 'blog.html', 'projects.html']) || strpos($clean_url_path, 'projects/') === 0) {
        $priority = '0.8';
    }

    // Absolute root directory configuration weight mapping pass
    if ($is_root_index) {
        $priority = '1.0';
    }

    // Deduplicate trailing directory separation slashes
    $final_url = rtrim($base_url . '/' . $clean_url_path, '/');
    
    if ($final_url === $base_url) {
        $final_url .= '/';
    }

    $sitemap .= '  <url><loc>' . $final_url . '</loc><priority>' . $priority . '</priority></url>' . PHP_EOL;
}

$sitemap .= '</urlset>' . PHP_EOL;

file_put_contents('dist_tmp/sitemap.xml', $sitemap);
echo "[BUILD] Syndication Module: sitemap.xml generated." . PHP_EOL;
