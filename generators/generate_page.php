<?php
/**
 * Susie - Static Page Generator Engine
 * Compiles both pure PHP files and Markdown static pages into minified production HTML.
 *
 * @package Susie
 */

require __DIR__ . '/../functions.php';

$filepath = $argv[1] ?? null;
$filename_slug = $argv[2] ?? null;

if (!$filepath || !$filename_slug || !file_exists($filepath)) {
    fwrite(STDERR, "Usage: php generate_page.php <filepath> <filename_slug>" . PHP_EOL);
    exit(1);
}

$current_slug = $filename_slug;
$extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

// Ingest global site variables parsed from config.ini
global $site_lang, $site_title, $site_author;

$title = $site_title;
$description = 'A minimalist static page powered by Susie.';
$lang = $site_lang; 
$author = $site_author;

if ($extension === 'md') {
    // --- STRATEGY A: MARKDOWN PARSING ---
    // Extract metadata block and source markdown content using unified core helpers
    $parsed = parse_front_matter($filepath);
    $meta = $parsed['meta'];
    $content_raw = apply_responsive_images($parsed['markdown']);

    // Contextual overrides from local Markdown Front Matter blocks
    if (isset($meta['title'])) $title = $meta['title'];
    if (isset($meta['description'])) $description = $meta['description'];
    if (isset($meta['lang'])) $lang = $meta['lang'];
    if (isset($meta['author'])) $author = $meta['author'];

    $parsedown = new Parsedown();
    $content = $parsedown->text($content_raw);

} else {
    // --- STRATEGY B: NATIVE PHP INGESTION ---
    // Buffer native PHP execution. Local layout variables will naturally override defaults.
    ob_start();
    include $filepath;
    $content = ob_get_clean();
}

// Assemble page wrapper structure inside global main layout
if (file_exists(__DIR__ . '/../layouts/main.php')) {
    ob_start();
    include __DIR__ . '/../layouts/main.php';
    echo minify_html(ob_get_clean());
}
