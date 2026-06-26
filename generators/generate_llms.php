<?php
// On charge les fonctions globales si nécessaire
if (file_exists('functions.php')) {
    require_once 'functions.php';
}

// Lecture sécurisée du fichier de configuration
$config = parse_ini_file('config.ini', true);
$site_title = $config['site']['title'] ?? 'Susie Blog';
$site_desc = $config['site']['description'] ?? 'A minimalist static blog powered by Susie.';
$base_url = rtrim($config['site']['base_url'] ?? 'https://example.com', '/');
$linkedin = $config['site']['linkedin'] ?? '';
$mastodon = $config['site']['mastodon'] ?? '';
$ignore_files = explode(' ', $config['site']['ignore_files'] ?? '');

$pages_links = "";
$posts_links = "";

// 1. Scan des pages statiques
if (is_dir('pages')) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('pages'));
    foreach ($files as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'md'])) {
            $filename_raw = $file->getBasename();
            if (in_array($filename_raw, $ignore_files)) continue;

            // Construction du slug relatif
            $relative_path = str_replace('pages/', '', $file->getPathname());
            $slug = pathinfo($relative_path, PATHINFO_FILENAME);

            if ($slug !== 'index' && $slug !== '404') {
                $title = ucfirst(str_replace('-', ' ', $slug));
                $pages_links .= "- [{$title}]({$base_url}/{$slug})\n";
            }
        }
    }
}

// 2. Scan des articles du blog
if (is_dir('posts')) {
    $files = glob('posts/*.md');
    if ($files) {
        foreach ($files as $file) {
            $filename_raw = basename($file);
            if (in_array($filename_raw, $ignore_files)) continue;

            $slug = pathinfo($file, PATHINFO_FILENAME);
            $title = ucfirst(str_replace('-', ' ', $slug));
            $posts_links .= "- [{$title}]({$base_url}/blog/{$slug})\n";
        }
    }
}

// 3. Assemblage du rendu Markdown final
$output = "# {$site_title}\n\n";
$output .= "> {$site_desc}\n\n";
$output .= "## Information\n";
$output .= "- **Main Website:** [" . parse_url($base_url, PHP_URL_HOST) . "]({$base_url})\n";

if (!empty($linkedin)) $output .= "- **Professional Profile:** [LinkedIn]({$linkedin})\n";
if (!empty($mastodon)) $output .= "- **Social Network:** [Mastodon]({$mastodon})\n";

if (!empty($pages_links)) {
    $output .= "\n## Pages\n" . trim($pages_links) . "\n";
}

if (!empty($posts_links)) {
    $output .= "\n## Recent Articles\n" . trim($posts_links) . "\n";
}

// Écriture directe dans le répertoire temporaire de staging
file_put_contents('dist_tmp/llms.txt', $output);
echo "[LLM]   Automated Syndication: Dynamic llms.txt context index generated via PHP.\n";
