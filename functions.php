<?php
/**
 * Susie - A Suckless Static Site Generator
 * Core Engine & Global Helpers
 *
 * @package Susie
 * @license MIT
 */

// 1. Load Configuration
$config = file_exists(__DIR__ . '/config.ini') ? parse_ini_file(__DIR__ . '/config.ini', true) : [];
$base_url   = $config['site']['base_url'] ?? 'http://localhost:8000';
$site_author = $config['site']['author'] ?? 'Susie User';
$site_title  = $config['site']['title'] ?? 'Minimalist Suckless Blog';
$site_lang   = $config['site']['lang'] ?? 'en';

// 2. State & Dependencies
$current_slug = $current_slug ?? ($filename_slug ?? 'index');

if (file_exists(__DIR__ . '/Parsedown.php')) {
    require_once __DIR__ . '/Parsedown.php';
}

/**
 * Filter: Transforms MD images into responsive HTML5 <picture> tags for WebP,
 * or simple <img> tags for unconvertible assets (gif, svg, etc.).
 */
function apply_responsive_images($markdown_content) {
    $pattern = '/\!\[(.*?)\]\(images\/([^)]+?)(?:\.([a-zA-Z0-9]+))?\)/i';

    return preg_replace_callback($pattern, function($matches) {
        $alt       = $matches[1];
        $basename  = $matches[2];
        $extension = isset($matches[3]) && $matches[3] !== '' ? strtolower($matches[3]) : '';

        $convertible = ['jpg', 'jpeg', 'png'];

        if ($extension === '' || in_array($extension, $convertible)) {
            return '<picture>
        <source media="(max-width: 600px)" srcset="/images/' . $basename . '-mobile.webp" type="image/webp">
        <img src="/images/' . $basename . '.webp" alt="' . $alt . '" loading="lazy">
    </picture>';
        }

        return '<img src="/images/' . $basename . '.' . $extension . '" alt="' . $alt . '" loading="lazy">';
    }, $markdown_content);
}

/**
 * Parses Front Matter YAML-like blocks delimited by "---" from Markdown files.
 */
function parse_front_matter($file_path) {
    $content = file_get_contents($file_path);
    $parts   = preg_split('/^---[\s]*$/m', $content);
    
    $meta     = [];
    $markdown = $content;

    if (count($parts) >= 3) {
        $markdown   = trim($parts[2]);
        $meta_lines = explode("\n", trim($parts[1]));
        foreach ($meta_lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }
    }

    return [
        'meta'     => $meta,
        'markdown' => $markdown
    ];
}

/**
 * Scans the posts directory and extracts compiled metadata collections.
 */
function get_blog_posts() {
    global $site_author, $site_lang;
    $posts = [];
    $dir   = __DIR__ . '/posts';
    
    if (!is_dir($dir)) return $posts;

    $files = glob($dir . '/*.md');
    foreach ($files as $file) {
        $filename = basename($file, '.md');
        
        $parsed          = parse_front_matter($file);
        $meta            = $parsed['meta'];
        $clean_markdown  = $parsed['markdown'];
        
        // Generate automatic plain-text fallback excerpt
        $lines         = explode("\n", $clean_markdown);
        $excerpt_lines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strpos($line, '#') !== 0) {
                $excerpt_lines[] = $line;
            }
            if (count($excerpt_lines) >= 2) break;
        }
        
        $date = $meta['date'] ?? date('Y-m-d');

        $posts[] = [
            'slug'             => $filename,
            'title'            => $meta['title'] ?? ucwords(str_replace('-', ' ', $filename)),
            'date'             => $date,
            'lang'             => $meta['lang'] ?? $site_lang,
            'description'      => $meta['description'] ?? 'A minimalist post.',
            'author'           => $meta['author'] ?? $site_author, 
            'category'         => $meta['category'] ?? 'General',
            'excerpt_markdown' => implode("\n\n", $excerpt_lines)
        ];
    }
    
    // Reverse chronological sorting via timestamp
    usort($posts, function($a, $b) {
        return strtotime($b['date']) <=> strtotime($a['date']);
    });

    return $posts;
}

/**
 * Minifies JavaScript strings natively.
 */
function minify_js($js) {
    $js = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $js);
    $js = preg_replace('/(?<!:)\/\/(?![^\n]*[\'"]).*$/m', '', $js);
    $js = preg_replace('/\s*([\{\}\(\)\=\+\-\*\/,;:])\s*/', '$1', $js);
    return trim(preg_replace('/\s+/', ' ', $js));
}

/**
 * Minifies HTML outputs while strictly preserving <pre> and <code> tag formatting integrity.
 */
function minify_html($html) {
    preg_match_all('/<(pre|code)[^>]*>.*?<\/\\1>/ss', $html, $matches);
    $placeholders = [];
    
    foreach ($matches[0] as $i => $match) {
        $placeholder               = "___SUSTAIN_CODE_BLOCK_" . $i . "___";
        $placeholders[$placeholder] = $match;
        $html                      = str_replace($match, $placeholder, $html);
    }

    $search = [
        '//ms',       // Remove standard HTML comments
        '/\s+/u',     // Collapse multi-spaces and newlines
    ];

    $replace = ['', ' '];
    $html    = preg_replace($search, $replace, $html);
    $html    = preg_replace('/>\s+</', '><', $html);

    if (!empty($placeholders)) {
        $html = str_replace(array_keys($placeholders), array_values($placeholders), $html);
    }

    return trim($html);
}

/**
 * Returns categories as a map: slug => ['name' => ..., 'posts' => [...]]
 * Supports comma-separated multi-categorization.
 */
function get_categories() {
    $posts      = get_blog_posts();
    $categories = [];

    foreach ($posts as $post) {
        $names = array_filter(array_map('trim', explode(',', $post['category'])));

        foreach ($names as $name) {
            $slug = slugify($name);

            if (!isset($categories[$slug])) {
                $categories[$slug] = [
                    'name'  => $name,
                    'posts' => []
                ];
            }
            $categories[$slug]['posts'][] = $post;
        }
    }

    return $categories;
}

/**
 * URL Slugifier: converts spaces, accents, and special characters into clean hyphens.
 */
function slugify($text) {
    $text = strtolower($text);
    $text = str_replace(
        ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç'],
        ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c'],
        $text
    );
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Minifies CSS strings natively.
 */
function minify_css($css) {
    $css = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $css);
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    $css = preg_replace('/\s*([\{\}\:\;,])\s*/', '$1', $css);
    return trim(preg_replace('/\s+/', ' ', $css));
}

/**
 * Obfuscates email strings using HTML decimal entities to prevent automated spam harvesting.
 */
function susie_obfuscate($email) {
    if (empty($email)) return '';
    $encoded = '';
    for ($i = 0; $i < strlen($email); $i++) {
        $encoded .= '&#' . ord($email[$i]) . ';';
    }
    return $encoded;
}
