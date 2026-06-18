<?php
/**
 * Susie - A Suckless Static Site Generator
 * Core Engine & Global Helpers
 */

// 1. Load Configuration
$config = file_exists(__DIR__ . '/config.ini') ? parse_ini_file(__DIR__ . '/config.ini', true) : [];
$base_url = $config['site']['base_url'] ?? 'http://localhost:8000';
$site_author = $config['site']['author'] ?? 'Susie User';
$site_title = $config['site']['title'] ?? 'Minimalist Suckless Blog';

// 2. Determine the current page slug (Injected by build.sh, defaults to 'index')
$current_slug = $current_slug ?? ($filename_slug ?? 'index');

// Load Markdown Parser dependency
if (file_exists(__DIR__ . '/Parsedown.php')) {
    require_once __DIR__ . '/Parsedown.php';
}

/**
 * Filter: Transforms standard Markdown images into responsive HTML5 <picture> tags
 * for convertible formats (jpg/jpeg/png), or a simple <img> tag pointing to the
 * original file for formats that are copied as-is (gif, svg, webp...).
 */
function apply_responsive_images($markdown_content) {
    $pattern = '/\!\[(.*?)\]\(images\/([^)]+?)(?:\.([a-zA-Z0-9]+))?\)/i';

    return preg_replace_callback($pattern, function($matches) {
        $alt = $matches[1];
        $basename = $matches[2];
        $extension = isset($matches[3]) && $matches[3] !== '' ? strtolower($matches[3]) : '';

        $convertible = ['jpg', 'jpeg', 'png'];

        if ($extension === '' || in_array($extension, $convertible)) {
            // Pas d'extension fournie, ou format convertible → WebP responsive
            return '<picture>
        <source media="(max-width: 600px)" srcset="/images/' . $basename . '-mobile.webp" type="image/webp">
        <img src="/images/' . $basename . '.webp" alt="' . $alt . '" loading="lazy">
    </picture>';
        }

        // Formats copiés tels quels (gif, svg, webp fourni, etc.)
        return '<img src="/images/' . $basename . '.' . $extension . '" alt="' . $alt . '" loading="lazy">';

    }, $markdown_content);
}

/**
 * Parses Front Matter metadata blocks delimited by "---" from Markdown files.
 */
function parse_front_matter($file_path) {
    $content = file_get_contents($file_path);
    $parts = preg_split('/^---[\s]*$/m', $content);
    
    $meta = [];
    $markdown = $content;

    if (count($parts) >= 3) {
        $markdown = trim($parts[2]);
        $meta_lines = explode("\n", trim($parts[1]));
        foreach ($meta_lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $meta[trim($key)] = trim($value);
            }
        }
    }

    return [
        'meta' => $meta,
        'markdown' => $markdown
    ];
}

/**
 * Scans the posts directory and extracts compiled metadata collections.
 */
function get_blog_posts() {
    global $site_author;
    $posts = [];
    // FIX : Utilisation de __DIR__ au lieu de getcwd() pour sécuriser l'arborescence
    $dir = __DIR__ . '/posts';
    
    if (!is_dir($dir)) return $posts;

    $files = glob($dir . '/*.md');
    foreach ($files as $file) {
        $filename = basename($file, '.md');
        
        $parsed = parse_front_matter($file);
        $meta = $parsed['meta'];
        $clean_markdown = $parsed['markdown'];
        
        // Generate automatic plain-text fallback excerpt
        $lines = explode("\n", $clean_markdown);
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
            'slug' => $filename,
            'title' => $meta['title'] ?? ucwords(str_replace('-', ' ', $filename)),
            'date' => $date,
            'lang' => $meta['lang'] ?? 'en',
            'description' => $meta['description'] ?? 'A minimalist post.',
            'author' => $meta['author'] ?? $site_author, 
            'category' => $meta['category'] ?? 'General',
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
        $placeholder = "___SUSTAIN_CODE_BLOCK_" . $i . "___";
        $placeholders[$placeholder] = $match;
        $html = str_replace($match, $placeholder, $html);
    }

    $search = [
        '//ms', // Remove standard HTML comments
        '/\s+/u',          // Collapse multi-spaces and newlines
    ];

    $replace = ['', ' '];
    $html = preg_replace($search, $replace, $html);
    $html = str_replace(["> ", " <"], [">", "<"], $html);

    if (!empty($placeholders)) {
        $html = str_replace(array_keys($placeholders), array_values($placeholders), $html);
    }

    return trim($html);
}


/**
 * Returns categories as a map: slug => ['name' => ..., 'posts' => [...]]
 * A post can belong to multiple categories (comma-separated in frontmatter).
 */
function get_categories() {
    $posts = get_blog_posts();
    $categories = [];

    foreach ($posts as $post) {
        // Split sur virgule, trim, ignore les vides
        $names = array_filter(array_map('trim', explode(',', $post['category'])));

        foreach ($names as $name) {
            $slug = slugify($name);

            if (!isset($categories[$slug])) {
                $categories[$slug] = [
                    'name' => $name,
                    'posts' => []
                ];
            }
            $categories[$slug]['posts'][] = $post;
        }
    }

    return $categories;
}

/**
 * Simple slugify: lowercase, spaces/accents to hyphens, strip non-alphanumeric.
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
 * Minifies CSS strings without breaking advanced selectors or media queries.
 */
function minify_css($css) {
    $css = preg_replace('!/\*[^*]*\*+([^/*][^*]*\*+)*/!', '', $css);
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    $css = preg_replace('/\s*([\{\}\:\;,])\s*/', '$1', $css);
    return trim(preg_replace('/\s+/', ' ', $css));
}
