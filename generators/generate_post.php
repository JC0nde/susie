<?php
/**
 * Susie - Static Blog Post Generator Engine
 * Compiles individual Markdown posts into minified standalone HTML layouts.
 *
 * @package Susie
 */

require __DIR__ . '/../functions.php';

$markdown_file = $argv[1] ?? null;
$filename_slug = $argv[2] ?? null;

if (!$markdown_file || !$filename_slug || !file_exists($markdown_file)) {
    fwrite(STDERR, "Usage: php generate_post.php <markdown_file> <filename_slug>" . PHP_EOL);
    exit(1);
}

// Ingest core variables from global scope
global $site_lang, $site_author;

$current_slug = 'blog/' . $filename_slug;

// Separate Front Matter blocks from source body markdown content
$parsed = parse_front_matter($markdown_file);
$meta = $parsed['meta'];
$clean_markdown = apply_responsive_images($parsed['markdown']);

$parsedown = new Parsedown();
$content = $parsedown->text($clean_markdown);

// Front Matter overrides with configuration fallbacks
$title = $meta['title'] ?? ucwords(str_replace('-', ' ', $filename_slug));
$description = $meta['description'] ?? "A minimalist post powered by Susie.";
$lang = $meta['lang'] ?? $site_lang;
$date = $meta['date'] ?? date('Y-m-d');
$author = $meta['author'] ?? $site_author;
$category = $meta['category'] ?? "General";

// Establish contextual pagination nodes
$all_posts = get_blog_posts();
$prev_post = null;
$next_post = null;

foreach ($all_posts as $index => $p) {
    if ($p['slug'] === $filename_slug) {
        if (isset($all_posts[$index - 1])) $prev_post = $all_posts[$index - 1];
        if (isset($all_posts[$index + 1])) $next_post = $all_posts[$index + 1];
        break;
    }
}

// Inject variables into standalone post template wrapper
if (file_exists(__DIR__ . '/../templates/post_wrapper.php')) {
    ob_start();
    include __DIR__ . '/../templates/post_wrapper.php';
    echo minify_html(ob_get_clean());
}
