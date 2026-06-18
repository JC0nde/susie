<?php
require __DIR__ . '/../functions.php';

$filepath = $argv[1] ?? null;
$filename_slug = $argv[2] ?? null;

if (!$filepath || !$filename_slug || !file_exists($filepath)) {
    fwrite(STDERR, "Usage: php generate_page.php <filepath> <filename_slug>" . PHP_EOL);
    exit(1);
}

$current_slug = $filename_slug;
$extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

// Valeurs par défaut
$title = 'Jonathan Conde';
$description = 'Site minimaliste';
$lang = 'fr';

if ($extension === 'md') {
    // --- CAS 1 : C'EST DU MARKDOWN ---
    $file_content = file_get_contents($filepath);
    $content_raw = $file_content;

    // Extraction Front-Matter YAML
    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)/s', $file_content, $matches)) {
        $front_matter = $matches[1];
        $content_raw = $matches[2];

        foreach (explode("\n", $front_matter) as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key === 'title') $title = $value;
                if ($key === 'description') $description = $value;
                if ($key === 'lang') $lang = $value;
            }
        }
    }

    $parsedown = new Parsedown();
    $content = $parsedown->text(apply_responsive_images($content_raw));

} else {
    // --- CAS 2 : C'EST DU PHP ---
    ob_start();
    include $filepath;
    $content = ob_get_clean();
}

// Emballage final dans le layout
ob_start();
include __DIR__ . '/../layouts/main.php';
echo minify_html(ob_get_clean());
