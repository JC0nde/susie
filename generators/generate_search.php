<?php
/**
 * Susie - Search Index Compiler Engine
 * Collects compiled post attributes and extracts clean text nodes into a minified JSON catalog.
 *
 * @package Susie
 */

require __DIR__ . '/../functions.php';

if (function_exists('get_blog_posts')) {
    $posts = get_blog_posts();
    $search_index = [];

    // Instantiate Parsedown safely once outside the loop to limit allocation overhead
    $parsedown = new Parsedown();

    foreach ($posts as $post) {
        // Strip down Markdown wrappers and HTML tags to extract raw, readable text nodes
        $clean_text = strip_tags($parsedown->text($post['excerpt_markdown'] ?? ''));
        
        $search_index[] = [
            'title'       => $post['title'],
            'slug'        => '/blog/' . $post['slug'] . '.html',
            'category'    => $post['category'] ?? 'General',
            'description' => $post['description'] ?? '',
            'content'     => strtolower($clean_text) // Lowercase to simplify client-side filtering passes
        ];
    }

    // Safely dump the JSON data structure into the temporary compilation distribution bucket
    file_put_contents('dist_tmp/search-index.json', json_encode($search_index, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
    echo "[SEARCH] Indexing framework completed: search-index.json generated." . PHP_EOL;
}
