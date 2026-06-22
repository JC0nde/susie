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

// UNIFICATION : Utilisation des variables globales chargées depuis config.ini par functions.php
global $site_lang, $site_title, $site_author;

$title = $site_title;
$description = 'Site et blog minimaliste.';
$lang = $site_lang; // Par défaut, on prend la langue globale (fr, en, etc.)
$author = $site_author;

if ($extension === 'md') {
    // --- CAS 1 : C'EST DU MARKDOWN ---
    // Utilisation de la fonction centralisée parse_front_matter() pour éviter les duplications de regex
    $parsed = parse_front_matter($filepath);
    $meta = $parsed['meta'];
    $content_raw = apply_responsive_images($parsed['markdown']);

    // Overrides locales via le Front Matter du fichier .md
    if (isset($meta['title'])) $title = $meta['title'];
    if (isset($meta['description'])) $description = $meta['description'];
    if (isset($meta['lang'])) $lang = $meta['lang'];
    if (isset($meta['author'])) $author = $meta['author'];

    $parsedown = new Parsedown();
    $content = $parsedown->text($content_raw);

} else {
    // --- CAS 2 : C'EST DU PHP ---
    // On exécute le fichier PHP. S'il contient des variables $title, $lang, etc., 
    // elles écraseront proprement les valeurs par défaut définies plus haut.
    ob_start();
    include $filepath;
    $content = ob_get_clean();
}

// Emballage final dans le layout main.php
if (file_exists(__DIR__ . '/../layouts/main.php')) {
    ob_start();
    include __DIR__ . '/../layouts/main.php';
    echo minify_html(ob_get_clean());
}
