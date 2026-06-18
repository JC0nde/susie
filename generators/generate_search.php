<?php
require __DIR__ . '/../functions.php';

$posts = get_blog_posts(); // On récupère tous les posts
$search_index = [];

foreach ($posts as $post) {
    // On nettoie le Markdown pour ne garder que le texte brut lisible
    $clean_text = strip_tags((new Parsedown())->text($post['excerpt_markdown']));
    
    $search_index[] = [
        'title'       => $post['title'],
        'slug'        => '/blog/' . $post['slug'] . '.html',
        'category'    => $post['category'],
        'description' => $post['description'],
        'content'     => strtolower($clean_text) // En minuscule pour faciliter la recherche
    ];
}

// Sauvegarde de l'index dans le répertoire temporaire de build
file_put_contents('dist_tmp/search-index.json', json_encode($search_index, JSON_UNESCAPED_UNICODE));
echo "[SEARCH] Indexing framework completed: search-index.json generated." . PHP_EOL;
