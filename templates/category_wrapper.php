<?php
// $category_name, $category_posts, $current_slug sont injectés par build.sh
// On s'assure de récupérer la langue globale si besoin
global $site_lang;

$title = $category_name . ' — Articles';
$description = 'Articles dans la catégorie ' . $category_name;
$lang = $site_lang; // UNIFICATION : On transmet proprement la langue au layout

// On instancie Parsedown une seule fois ici pour économiser les ressources de Susie
$parsedown = new Parsedown();

ob_start();
?>
<section class="category-section">
    <h1>Catégorie : <?= htmlspecialchars($category_name) ?></h1>
    <div class="posts-list">
        <?php foreach ($category_posts as $post): ?>
            <article class="post-preview">
                <h2 class="post-title" style="font-size: 1.8rem; margin: 10px 0;">
                    <a href="/blog/<?= htmlspecialchars($post['slug']) ?>.html"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <div class="excerpt">
                    <!-- Utilisation de l'instance unique de Parsedown -->
                    <?= $parsedown->text($post['excerpt_markdown']) ?>
                </div>
                <a href="/blog/<?= htmlspecialchars($post['slug']) ?>.html" class="read-more">Lire l'article complet →</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
